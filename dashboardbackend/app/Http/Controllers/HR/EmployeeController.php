<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\CvDocument;
use App\Models\HR\OrgChart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    // Get all employees
    public function index()
    {
        $employees = Employee::with(['cvDocuments', 'position', 'project', 'jobDescription'])->get(); // Include Job Description in the response
        return response()->json($employees);
    }

    // Get a single employee by ID
    public function show($id)
    {
        $employee = Employee::with('cvDocuments', 'jobDescription')->find($id); // Include Job Description in the response

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        return response()->json($employee);
    }

    // Create a new employee with CV upload
    public function store(Request $request)
    {
        // Validate input data
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:hr_employee,email',
            'phone' => 'required|string|max:15',
            'status' => 'required|string',
            'position_id' => 'nullable|exists:hr_positions,id',
            'project_id' => 'nullable|exists:hr_project,id',
            'expectation_start_date' => 'nullable|date',
            'actual_start_date' => 'nullable|date',
            'expectation_salary' => 'nullable|numeric',
            'current_salary' => 'nullable|numeric',
            'years_experience' => 'nullable|numeric',
            'proposed_position' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'languages' => 'nullable|string',
            'about_me' => 'nullable|string',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:20048',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20048',
            'reported_by' => 'nullable|exists:hr_employee,id',
            'hr_job_description_id' => 'nullable|exists:hr_job_description,id',
        ]);
    
        // Create the employee record
        $employee = Employee::create($validatedData);
    
        // Log the presence of the CV file
        Log::info('Checking if CV file is present: ', ['has_cv' => $request->hasFile('cv')]);
        Log::info('Checking if CV file is present: ', ['has_cv' => $request->hasFile('cv_documents')]);
    
        // Handle CV upload if present
        if ($request->hasFile('cv')) {
            $cvFile = $request->file('cv');
    
            // Log file details
            Log::info('CV file details:', [
                'original_name' => $cvFile->getClientOriginalName(),
                'mime_type' => $cvFile->getMimeType(),
                'size' => $cvFile->getSize(),
            ]);
    
            // Create directory if it doesn't exist
            $cvFolderPath = public_path('cv_documents');
            if (!File::exists($cvFolderPath)) {
                File::makeDirectory($cvFolderPath, 0777, true, true);
                Log::info("Created directory for CV at $cvFolderPath");
            }
    
            // Generate unique file name and move the file
            try {
                $cvFileName = time() . '_' . $cvFile->getClientOriginalName();
                $cvPath = 'cv_documents/' . $cvFileName;
                $cvFile->move($cvFolderPath, $cvFileName);
    
                // Log successful file move
                Log::info("CV file successfully uploaded to: $cvPath");
    
                // Create the CV document record
                CvDocument::create([
                    'employee_id' => $employee->id,
                    'mgi_cv_path' => $cvPath,
                    'original_cv_path' => $cvFile->getClientOriginalName(),
                    'uploaded_date' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('Error uploading CV:', ['error' => $e->getMessage()]);
            }
        } else {
            Log::warning('No CV file uploaded.');
        }
    
        // Handle image upload if present
        if ($request->hasFile('image')) {
            $imageFolderPath = public_path('images');
            if (!File::exists($imageFolderPath)) {
                File::makeDirectory($imageFolderPath, 0777, true, true);
            }
    
            $imageFileName = time() . '_' . $request->file('image')->getClientOriginalName();
            $imagePath = 'images/' . $imageFileName;
            $request->file('image')->move($imageFolderPath, $imageFileName);
    
            // Optionally, save the image path to the employee record
            $employee->image_path = $imagePath;
            $employee->save();
        }
    
        // Create OrgChart entry
        $hierarchyLevel = $this->calculateHierarchyLevel($employee->reported_by);
    
        OrgChart::create([
            'employee_id' => $employee->id,
            'project_id' => $employee->project_id,
            'position_id' => $employee->position_id,
            'manager_id' => $employee->reported_by,
            'hierarchy_level' => $hierarchyLevel,
        ]);
    
        return response()->json($employee->load('cvDocuments', 'jobDescription'), 201);
    }
    



    private function calculateHierarchyLevel($managerId)
    {
        if (is_null($managerId)) {
            return 1; // Root employee
        }

        $manager = Employee::find($managerId);
        if ($manager) {
            $managerOrgChart = OrgChart::where('employee_id', $manager->id)->first();
            return $managerOrgChart ? $managerOrgChart->hierarchy_level + 1 : 2; // If manager has a hierarchy, increment, otherwise level 2
        }

        return 2; // Default to level 2 if no manager found
    }

    // Update an existing employee and handle CV upload
    public function update(Request $request, $id)
    {
        Log::info($request->all());

        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $validatedData = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:hr_employee,email,' . $employee->id,
            'phone' => 'nullable|string|max:15',
            'status' => 'nullable|string',
            'position_id' => 'nullable|exists:hr_positions,id',
            'project_id' => 'nullable|exists:hr_project,id',
            'expectation_start_date' => 'nullable',
            'actual_start_date' => 'nullable',
            'expectation_salary' => 'nullable',
            'current_salary' => 'nullable',
            'years_experience' => 'nullable',
            'proposed_position' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'languages' => 'nullable|string',
            'about_me' => 'nullable|string',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validation for the image
            'reported_by' => 'nullable',
            'hr_job_description_id' => 'nullable|exists:hr_job_description,id', // Validation for job description
        ]);

        Log::info($validatedData);

        // Update employee details
        $reported_by = $request->input('reported_by');
        $employee->update($validatedData);

        if ($reported_by === 'NONE' || empty($reported_by)) {
            $reported_by = null;
        }

        // Handle CV upload if present
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('cv_documents', 'public');

            $oldCv = $employee->cvDocuments()->latest()->first();
            if ($oldCv && Storage::exists('public/' . $oldCv->mgi_cv_path)) {
                Storage::delete('public/' . $oldCv->mgi_cv_path);
                $oldCv->delete();
            }

            CvDocument::create([
                'employee_id' => $employee->id,
                'mgi_cv_path' => $cvPath,
                'original_cv_path' => $request->file('cv')->getClientOriginalName(),
                'uploaded_date' => now(),
            ]);
        }

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');

            // Remove old image if exists
            if ($employee->image_path && Storage::exists('public/' . $employee->image_path)) {
                Storage::delete('public/' . $employee->image_path);
            }

            // Update the image path
            $employee->image_path = $imagePath;
            $employee->save();
        }

        // Update OrgChart entry
        $hierarchyLevel = $this->calculateHierarchyLevel($employee->reported_by);

        $orgChart = OrgChart::where('employee_id', $employee->id)->first();
        if ($orgChart) {
            $orgChart->update([
                'project_id' => $employee->project_id,
                'position_id' => $employee->position_id,
                'manager_id' => $employee->reported_by,
                'hierarchy_level' => $hierarchyLevel,
            ]);
        } else {
           OrgChart::create([
                'employee_id' => $employee->id,
                'project_id' => $employee->project_id,
                'position_id' => $employee->position_id,
                'manager_id' => $employee->reported_by,
                'hierarchy_level' => $hierarchyLevel,
            ]);
        }

        return response()->json($employee->load('cvDocuments', 'jobDescription'));
    }

    // Delete an employee and their CVs and Images
    public function destroy($id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        // Delete associated CVs
        $employee->cvDocuments()->each(function ($cv) {
            if (Storage::exists('public/' . $cv->mgi_cv_path)) {
                Storage::delete('public/' . $cv->mgi_cv_path);
            }
            $cv->delete();
        });

        // Delete associated Image if exists
        if ($employee->image_path && Storage::exists('public/' . $employee->image_path)) {
            Storage::delete('public/' . $employee->image_path);
        }

        $employee->delete();

        return response()->json(['message' => 'Employee and CV documents deleted successfully']);
    }
}