<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HR\Employee;
use App\Models\HR\CvDocument;
use App\Models\HR\Experience;
use App\Models\HR\Education;
use App\Models\HR\Skills;
use App\Models\HR\Certification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CVEmployeesController extends Controller
{
    //
    public function generatePDF($id)
    {
        $employee = Employee::with(['experiences', 'education', 'skills', 'certifications'])->find($id);

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        // Generate PDF
        $pdf = Pdf::loadView('cv_template', compact('employee'));

        // Download the PDF file
        return $pdf->download( $employee->first_name.' '.$employee->last_name.'.pdf');
    }
    public function index()
    {
        $employees = Employee::with(['experiences', 'education', 'skills', 'certifications', 'cvDocuments'])->get();
        return response()->json($employees);
    }

    // Get a single employee by ID with their related data
    public function show($id)
    {
        $employee = Employee::with(['experiences', 'education', 'skills', 'certifications', 'cvDocuments'])->find($id);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }
        Log::info($employee);
        return response()->json($employee);
    }

    // Create a new employee along with their CV
    public function store(Request $request)
    {
        // Validate request data
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:hr_employee,email',
            'phone' => 'required|string|max:15',
            'status' => 'required|string',
            'position_id' => 'required|exists:hr_positions,id',
            'project_id' => 'required|exists:hr_project,id',
            'expectation_start_date' => 'nullable|date',
            'actual_start_date' => 'nullable|date',
            'expectation_salary' => 'nullable|numeric',
            'current_salary' => 'nullable|numeric',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:2048' // CV upload validation
        ]);

        // Create employee record
        $employee = Employee::create($validatedData);

        // Handle CV upload if present
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('cv_documents', 'public');
            CvDocument::create([
                'employee_id' => $employee->id,
                'cv_generated_path' => $cvPath,
                'status' => 'created',
            ]);
        }

        return response()->json($employee->load('cvDocuments'), 201);
    }

    // Update an existing employee and handle CV upload
    public function update(Request $request, $id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        // Validate the updated data
        $validatedData = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:hr_employee,email,' . $employee->id,
            'phone' => 'sometimes|string|max:15',
            'status' => 'sometimes|string',
            'position_id' => 'sometimes|exists:hr_positions,id',
            'project_id' => 'sometimes|exists:hr_projects,id',
            'expectation_start_date' => 'nullable|date',
            'actual_start_date' => 'nullable|date',
            'expectation_salary' => 'nullable|numeric',
            'current_salary' => 'nullable|numeric',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:2048' // CV upload validation
        ]);

        // Update employee details
        $employee->update($validatedData);

        // Handle CV upload if present
        if ($request->hasFile('cv')) {
            // Remove previous CV
            $oldCv = $employee->cvDocuments()->latest()->first();
            if ($oldCv && Storage::exists('public/' . $oldCv->cv_generated_path)) {
                Storage::delete('public/' . $oldCv->cv_generated_path);
            }

            // Store the new CV
            $cvPath = $request->file('cv')->store('cv_documents', 'public');
            CvDocument::create([
                'employee_id' => $employee->id,
                'cv_generated_path' => $cvPath,
                'status' => 'updated',
            ]);
        }

        return response()->json($employee->load('cvDocuments'));
    }

    // Delete an employee and associated CVs
    public function destroy($id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        // Delete associated CVs
        $employee->cvDocuments()->each(function ($cv) {
            if (Storage::exists('public/' . $cv->cv_generated_path)) {
                Storage::delete('public/' . $cv->cv_generated_path); // Delete CV file
            }
            $cv->delete(); // Delete CV record
        });

        $employee->delete();

        return response()->json(['message' => 'Employee and CV documents deleted successfully']);
    }

    // Add or update employee's experience
    public function addExperience(Request $request, $employeeId)
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $experience = $employee->experiences()->create($validatedData);

        return response()->json($experience);
    }

    // Add or update employee's education
    public function addEducation(Request $request, $employeeId)
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $validatedData = $request->validate([
            'institution_name' => 'required|string|max:255',
            'degree' => 'required|string|max:255',
            'field_of_study' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $education = $employee->education()->create($validatedData);

        return response()->json($education);
    }

    // Add or update employee's skills
    public function addSkills(Request $request, $employeeId)
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $validatedData = $request->validate([
            'skill_name' => 'required|string|max:255',
            'proficiency_level' => 'required|string|max:255',
        ]);

        $skill = $employee->skills()->create($validatedData);

        return response()->json($skill);
    }

    // Add or update employee's certifications
    public function addCertification(Request $request, $employeeId)
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $validatedData = $request->validate([
            'certification_name' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);

        $certification = $employee->certifications()->create($validatedData);

        return response()->json($certification);
    }
}
