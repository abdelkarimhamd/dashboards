<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CompanyController extends Controller
{
    /**
     * Display a listing of the companies.
     */
    public function index()
    {
        $companies = Company::all();

        return response()->json($companies, 200);
    }

    /**
     * Store a newly created company in storage.
     */
    public function store(Request $request)
    {
        // Step 1: Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:CRM_companies,email',
            'industry' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'parent_company_id' => 'nullable|exists:CRM_companies,id', // Check if parent exists
        ]);

        // Step 2: Create the company
        $company = Company::create($request->all());

        // Step 3: Return a success response
        return response()->json([
            'message' => 'Company created successfully',
            'company' => $company,
        ], 201);
    }

    /**
     * Display the specified company.
     */
/**
 * Display the specified company.
 */
public function show($id)
{
    // Eager load 'parent', 'children.contacts', and other relationships
    $company = Company::with([
        'parent',
        'children.contacts', // Eager load contacts for each child
        'createdBy',
        'leads',
        'contacts',
        'deals',
        'tasks'
    ])->findOrFail($id);

    return response()->json($company, Response::HTTP_OK);
}



    /**
     * Update the specified company in storage.
     */
    public function update(Request $request, $id)
    {
        // Step 1: Validate the incoming request
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255|unique:CRM_companies,email,' . $id,
            'industry' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'parent_company_id' => 'nullable|exists:CRM_companies,id',
        ]);

        // Step 2: Find the company and update it
        $company = Company::findOrFail($id);

        $company->update($request->all());

        // Step 3: Return a success response
        return response()->json([
            'message' => 'Company updated successfully',
            'company' => $company,
        ], 200);
    }

    /**
     * Remove the specified company from storage (soft delete).
     */
    public function destroy($id)
    {
        $company = Company::findOrFail($id);

        // Step 1: Soft delete the company
        $company->delete();

        // Step 2: Return a success response
        return response()->json([
            'message' => 'Company deleted successfully',
        ], 200);
    }
}
