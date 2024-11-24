<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    /**
     * Display a listing of the leads.
     */
    public function index()
    {
        $leads = Lead::all();

        return response()->json($leads, 200);
    }

    /**
     * Store a newly created lead in storage.
     */
    public function store(Request $request)
    {
        // Step 1: Validate the incoming request
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:CRM_leads,email',
            'phone' => 'nullable|string|max:50',
            'stage' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'source' => 'nullable|string|max:100',
            'company_id' => 'nullable|exists:CRM_companies,id', // Ensure company exists
        ]);

        // Step 2: Create the lead
        $lead = Lead::create($request->all());

        // Step 3: Return a success response
        return response()->json([
            'message' => 'Lead created successfully',
            'lead' => $lead,
        ], 201);
    }

    /**
     * Display the specified lead.
     */
    public function show($id)
    {
        $lead = Lead::findOrFail($id); // Find lead by ID or return 404

        return response()->json($lead, 200);
    }

    /**
     * Update the specified lead in storage.
     */
    public function update(Request $request, $id)
    {
        // Step 1: Validate the incoming request
        $request->validate([
            'first_name' => 'sometimes|required|string|max:100',
            'last_name' => 'sometimes|required|string|max:100',
            'email' => 'nullable|email|max:255|unique:CRM_leads,email,' . $id,
            'phone' => 'nullable|string|max:50',
            'stage' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'source' => 'nullable|string|max:100',
            'company_id' => 'nullable|exists:CRM_companies,id',
        ]);

        // Step 2: Find the lead and update its details
        $lead = Lead::findOrFail($id);

        $lead->update($request->all());

        // Step 3: Return a success response
        return response()->json([
            'message' => 'Lead updated successfully',
            'lead' => $lead,
        ], 200);
    }

    /**
     * Remove the specified lead from storage (soft delete).
     */
    public function destroy($id)
    {
        $lead = Lead::findOrFail($id);

        // Step 1: Soft delete the lead
        $lead->delete();

        // Step 2: Return a success response
        return response()->json([
            'message' => 'Lead deleted successfully',
        ], 200);
    }
}
