<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HR\Experience;

class ExperienceController extends Controller
{
    /**
     * Display a listing of all experiences.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Fetch and return all experiences
        return Experience::all();
    }

  
    public function show($id)
    {
        // Find experience by ID
        $experience = Experience::find($id);

        // If experience is not found, return a 404 error response
        if (!$experience) {
            return response()->json(['error' => 'Experience not found'], 404);
        }

        // Return the experience in JSON format
        return response()->json($experience);
    }

    /**
     * Store a newly created experience in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:hr_employee,id',
            'company_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable',
        ]);

        // Create a new experience with the validated data
        $experience = Experience::create($validatedData);

        // Return the created experience with a 201 status code
        return response()->json($experience, 201);
    }

    /**
     * Update the specified experience in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Find the experience by ID
        $experience = Experience::find($id);

        // If experience is not found, return a 404 error response
        if (!$experience) {
            return response()->json(['error' => 'Experience not found'], 404);
        }

        // Validate incoming request data
        $validatedData = $request->validate([
            'company_name' => 'sometimes|string|max:255',
            'position' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable',
        ]);

        // Update the experience with the validated data
        $experience->update($validatedData);

        // Return the updated experience
        return response()->json($experience);
    }

    /**
     * Remove the specified experience from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Find the experience by ID
        $experience = Experience::find($id);

        // If experience is not found, return a 404 error response
        if (!$experience) {
            return response()->json(['error' => 'Experience not found'], 404);
        }

        // Delete the experience
        $experience->delete();

        // Return a success message
        return response()->json(['message' => 'Experience deleted successfully']);
    }
}
