<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\DealStage;
use Illuminate\Http\Request;

class DealStageController extends Controller
{
    /**
     * Display a listing of the resource.
     */public function index()
    {
        // Retrieve all deal stages
        $dealStages = DealStage::all();

        // Return the deal stages as a JSON response
        return response()->json($dealStages, 200);
    }

    /**
     * Store a newly created deal stage in storage.
     */
    public function store(Request $request)
    {
        // Step 1: Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        // Step 2: Create the new deal stage
        $dealStage = DealStage::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        // Step 3: Return a success response
        return response()->json([
            'message' => 'Deal stage created successfully',
            'dealStage' => $dealStage,
        ], 201);
    }

    /**
     * Display the specified deal stage.
     */
    public function show($id)
    {
        // Retrieve the deal stage by ID, or return 404 if not found
        $dealStage = DealStage::findOrFail($id);

        // Return the deal stage as a JSON response
        return response()->json($dealStage, 200);
    }

    /**
     * Update the specified deal stage in storage.
     */
    public function update(Request $request, $id)
    {
        // Step 1: Validate the incoming request
        $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
        ]);

        // Step 2: Find the deal stage and update its details
        $dealStage = DealStage::findOrFail($id);
        $dealStage->update($request->all());

        // Step 3: Return a success response
        return response()->json([
            'message' => 'Deal stage updated successfully',
            'dealStage' => $dealStage,
        ], 200);
    }

    /**
     * Remove the specified deal stage from storage.
     */
    public function destroy($id)
    {
        // Find the deal stage by ID and delete it
        $dealStage = DealStage::findOrFail($id);
        $dealStage->delete();

        // Return a success response
        return response()->json([
            'message' => 'Deal stage deleted successfully',
        ], 200);
    }
}
