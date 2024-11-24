<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Education;
use Illuminate\Http\Request;

class EducationController extends Controller
{
    //

    public function index()
    {
        return Education::all();
    }

    // Get a single education record by ID
    public function show($id)
    {
        $education = Education::find($id);
        if (!$education) {
            return response()->json(['error' => 'Education not found'], 404);
        }
        return response()->json($education);
    }

    // Create a new education record
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:hr_employee,id',
            'institution_name' => 'nullable|string|max:255',
            'degree' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $education = Education::create($validatedData);
        return response()->json($education, 201);
    }

    // Update an existing education record
    public function update(Request $request, $id)
    {
        $education = Education::find($id);
        if (!$education) {
            return response()->json(['error' => 'Education not found'], 404);
        }

        $validatedData = $request->validate([
            'institution_name' => 'sometimes|string|max:255',
            'degree' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $education->update($validatedData);
        return response()->json($education);
    }

    // Delete an education record
    public function destroy($id)
    {
        $education = Education::find($id);
        if (!$education) {
            return response()->json(['error' => 'Education not found'], 404);
        }

        $education->delete();
        return response()->json(['message' => 'Education deleted successfully']);
    }
}
