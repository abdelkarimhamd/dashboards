<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Certification;
use Illuminate\Http\Request;

class CertificationController extends Controller
{
    //
    public function index()
    {
        return Certification::all();
    }

    // Get a single certification by ID
    public function show($id)
    {
        $certification = Certification::find($id);
        if (!$certification) {
            return response()->json(['error' => 'Certification not found'], 404);
        }
        return response()->json($certification);
    }

    // Create a new certification
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:hr_employee,id',
            'certification_name' => 'required|string|max:255',
            'institution' => 'nullable|string|max:255',
            'issued_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:issued_date',
        ]);

        $certification = Certification::create($validatedData);
        return response()->json($certification, 201);
    }

    // Update an existing certification
    public function update(Request $request, $id)
    {
        $certification = Certification::find($id);
        if (!$certification) {
            return response()->json(['error' => 'Certification not found'], 404);
        }

        $validatedData = $request->validate([
            'certification_name' => 'sometimes|string|max:255',
            'issued_by' => 'sometimes|string|max:255',
            'issued_date' => 'sometimes|date',
            'expiry_date' => 'nullable|date|after_or_equal:issued_date',
        ]);

        $certification->update($validatedData);
        return response()->json($certification);
    }

    // Delete a certification
    public function destroy($id)
    {
        $certification = Certification::find($id);
        if (!$certification) {
            return response()->json(['error' => 'Certification not found'], 404);
        }

        $certification->delete();
        return response()->json(['message' => 'Certification deleted successfully']);
    }
}
