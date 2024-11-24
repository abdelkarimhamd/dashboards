<?php

namespace App\Http\Controllers\ProcurementControllers;

use App\Http\Controllers\Controller;
use App\Models\ProcurementModels\ProcurementProjects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class ProcurementProjectsController extends Controller
{
    public function store(Request $request)
    {
        // Validate the form inputs
        $validator = Validator::make($request->all(), [
            'payment_sections.*.project_name' => 'required|string|max:255',
            'payment_sections.*.project_type' => 'required|string|max:255',
            
           
        ]);
    
        // If validation fails, return error messages
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }
    
        // Loop through the payment sections and store each section in the database
        foreach ($request->payment_sections as $section) {
            ProcurementProjects::create([
                'project_name' => $section['project_name'],
                'project_type' => $section['project_type'],
                
            ]);
        }
    
        return response()->json([
            'status' => 'success',
            'message' => 'Procurement suppliers successfully stored',
        ], 201);
    }

    public function getProjects()
    {
        // Fetch all projects with their names and types
        $projects = ProcurementProjects::select('project_name', 'project_type')->get();
    
        // Return the projects in a JSON response
        return response()->json([
            'status' => 'success',
            'projects' => $projects,
        ], 200);
    }
}
