<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\DealLead;
use Illuminate\Http\Request;

class DealLeadController extends Controller
{
    /**
     * Display a listing of the deal-lead associations.
     */
    public function index()
    {
        // Retrieve all deal-lead associations
        $dealLeads = DealLead::all();

        return response()->json($dealLeads, 200);
    }

    /**
     * Store a newly created deal-lead association in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'deal_id' => 'required|exists:CRM_deals,id',
            'lead_id' => 'required|exists:CRM_leads,id',
        ]);

        // Create the deal-lead association
        $dealLead = DealLead::create([
            'deal_id' => $request->deal_id,
            'lead_id' => $request->lead_id,
        ]);

        // Return a success response
        return response()->json([
            'message' => 'Deal-lead association created successfully',
            'dealLead' => $dealLead,
        ], 201);
    }

    /**
     * Display the specified deal-lead association.
     */
    public function show($id)
    {
        // Retrieve the deal-lead association by ID or return 404
        $dealLead = DealLead::findOrFail($id);

        return response()->json($dealLead, 200);
    }

    /**
     * Update the specified deal-lead association in storage.
     */
    public function update(Request $request, $id)
    {
        // Validate the request for updating deal-lead association
        $request->validate([
            'deal_id' => 'sometimes|required|exists:CRM_deals,id',
            'lead_id' => 'sometimes|required|exists:CRM_leads,id',
        ]);

        // Find the existing deal-lead association
        $dealLead = DealLead::findOrFail($id);

        // Update the deal-lead association
        $dealLead->update($request->all());

        return response()->json([
            'message' => 'Deal-lead association updated successfully',
            'dealLead' => $dealLead,
        ], 200);
    }

    /**
     * Remove the specified deal-lead association from storage.
     */
    public function destroy($id)
    {
        // Find the deal-lead association
        $dealLead = DealLead::findOrFail($id);

        // Delete the deal-lead association
        $dealLead->delete();

        return response()->json([
            'message' => 'Deal-lead association deleted successfully',
        ], 200);
    }
}
