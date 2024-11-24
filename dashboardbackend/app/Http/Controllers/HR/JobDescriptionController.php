<?php

namespace App\Http\Controllers\HR;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;
use App\Models\HR\JobDescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JobDescriptionController extends Controller
{
    // Get all job descriptions
    public function index()
    {
        $jobDescriptions = JobDescription::all();
        return response()->json($jobDescriptions);
    }

    // Show a single job description
    public function show($id)
    {
        $jobDescription = JobDescription::find($id);

        if (!$jobDescription) {
            return response()->json(['error' => 'Job description not found'], 404);
        }

        return response()->json($jobDescription);
    }

    // Store a new job description with file upload

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:2048', // Validate file (max 2MB)
        ]);

        // Handle the file upload
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName(); // Unique file name with timestamp
        $file->move(public_path('job_descriptions'), $fileName); // Move file to public/job_descriptions

        // Save the file path in the database
        $validatedData['document_path'] = 'job_descriptions/' . $fileName;

        $jobDescription = JobDescription::create($validatedData);

        return response()->json($jobDescription, 201);
    }

    public function update(Request $request, $id)
    {
        $jobDescription = JobDescription::find($id);

        if (!$jobDescription) {
            return response()->json(['error' => 'Job description not found'], 404);
        }

        $validatedData = $request->validate([
            'title' => 'sometimes|string|max:255',
            'file' => 'nullable|file|max:2048', // Validate file if uploaded
        ]);

        // Check if a file is uploaded, and handle the update
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName(); // Unique file name with timestamp

            // Delete the old file from public folder if it exists
            $oldFilePath = public_path($jobDescription->document_path);
            if (File::exists($oldFilePath)) {
                File::delete($oldFilePath);
            }

            // Move the new file to the public directory
            $file->move(public_path('job_descriptions'), $fileName);
            $validatedData['document_path'] = 'job_descriptions/' . $fileName;
        }

        $jobDescription->update($validatedData);

        return response()->json($jobDescription);
    }


    // Delete a job description and its associated file
    public function destroy($id)
    {
        $jobDescription = JobDescription::find($id);

        if (!$jobDescription) {
            return response()->json(['error' => 'Job description not found'], 404);
        }

        // Delete the file from storage
        if (Storage::exists('public/' . $jobDescription->document_path)) {
            Storage::delete('public/' . $jobDescription->document_path);
        }

        $jobDescription->delete();

        return response()->json(['message' => 'Job description deleted successfully']);
    }
}
