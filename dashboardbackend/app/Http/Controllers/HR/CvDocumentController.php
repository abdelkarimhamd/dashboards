<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\CvDocument;
use Illuminate\Http\Request;

class CvDocumentController extends Controller
{
    //

    public function index()
    {
        $cvDocuments = CvDocument::all();
        return response()->json($cvDocuments);
    }

    public function show($id)
    {
        $cvDocument = CvDocument::find($id);

        if (!$cvDocument) {
            return response()->json(['error' => 'CV document not found'], 404);
        }

        return response()->json($cvDocument);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:hr_employee,id',
            'mgi_cv_path' => 'nullable|string',
            'original_cv_path' => 'required|string',
            'uploaded_date' => 'required|date',
        ]);

        $cvDocument = CvDocument::create($validatedData);

        return response()->json($cvDocument, 201);
    }

    public function update(Request $request, $id)
    {
        $cvDocument = CvDocument::find($id);

        if (!$cvDocument) {
            return response()->json(['error' => 'CV document not found'], 404);
        }

        $validatedData = $request->validate([
            'employee_id' => 'exists:hr_employee,id',
            'mgi_cv_path' => 'nullable|string',
            'original_cv_path' => 'string',
            'uploaded_date' => 'date',
        ]);

        $cvDocument->update($validatedData);

        return response()->json($cvDocument);
    }

    public function destroy($id)
    {
        $cvDocument = CvDocument::find($id);

        if (!$cvDocument) {
            return response()->json(['error' => 'CV document not found'], 404);
        }

        $cvDocument->delete();

        return response()->json(['message' => 'CV document deleted successfully']);
    }
}
