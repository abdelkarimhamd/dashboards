<?php

namespace App\Http\Controllers\TenderingControllers;

use App\Http\Controllers\Controller;
use App\Models\FactSheet;
use App\Models\Image;
use App\Models\Tender;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FactSheetController extends Controller
{
    public function store(Request $request, $tenderId)
    {
        // Validate the request
        $validatedData = $request->validate([
            'client_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:2555',
            'scope_of_work' => 'nullable',
            'commencement_date' => 'nullable|date',
            'duration_of_project' => 'nullable|string|max:2555',
            'tender_bond' => 'nullable|string',
            'technical_requirements' => 'nullable|string',
            'delay_damages' => 'nullable|string|max:2555',
            'type_of_contract' => 'nullable|string|max:255',
            'procurement_route' => 'nullable|string|max:255',
            'advance_payment' => 'nullable|string|max:2885',
            'performance_bond' => 'nullable|string|max:2555',
            'retention' => 'nullable|string|max:2555',
            'bid_bond' => 'nullable|string|max:2555',
            'area' => 'nullable|string|max:2555',
            'tender_submission_date' => 'nullable|date',
            'preliminary_estimate' => 'nullable|numeric',
            'jv_partner' => 'nullable|string|max:2555',
            'dry_cost' => 'nullable|numeric',
            'admin_and_salaries' => 'nullable|numeric',
            'profit' => 'nullable|numeric',
            'provisional_sum' => 'nullable|numeric',
            'total_offer_price' => 'nullable|numeric',
            'recommendation' => 'nullable|string', // Add recommendation field
            'images' => 'nullable|array', // Ensures 'images' is an array
            'images.*' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048', // Validates each file in the 'images' array
        ]);

        // Retrieve the tender or fail if not found
        $tender = Tender::findOrFail($tenderId);

        // Check if a Fact Sheet already exists for this tender
        $factSheet = FactSheet::where('tender_id', $tenderId)->first();

        // Automatically populate fields from the tender
        $factSheetData = [
            'tender_id' => $tender->id,
            'project_name' => $tender->tenderTitle,
            'location' => $tender->location,
        ];

        // Merge with validated data
        $factSheetData = array_merge($factSheetData, $validatedData);

        if ($factSheet) {
            // If the Fact Sheet already exists, update it
            $factSheet->update($factSheetData);
            $message = 'Fact sheet updated successfully';
        } else {
            // If the Fact Sheet does not exist, create a new one
            $factSheet = FactSheet::create($factSheetData);
            $message = 'Fact sheet created successfully';
        }

        // Handle image uploads only if new files are uploaded
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images/fact_sheets'), $imageName);

                Image::create([
                    'fact_sheet_id' => $factSheet->id,
                    'file_name' => $image->getClientOriginalName(),
                    'file_path' => 'images/fact_sheets/' . $imageName,
                ]);
            }
        }

        return response()->json(['message' => $message, 'fact_sheet' => $factSheet], 200);
    }

  
    
    public function show($id)
    {
        try {
            // Find the FactSheet by tender_id
            $factSheet = FactSheet::with('tender', 'images')
                ->where('tender_id', $id)
                ->firstOrFail();
    
            // Log for debugging
            Log::info("Fetched FactSheet");
            Log::info($factSheet);
    
            // Return the FactSheet as JSON, including the recommendation field
            return response()->json(['fact_sheet' => $factSheet], 200);
        } catch (ModelNotFoundException $e) {
            // Log the error for debugging
            Log::error("FactSheet not found for tender_id: " . $id);
    
            // Return a custom error response
            return response()->json(['message' => 'FactSheet not created yet'], 404);
        }
    }
    

    public function update(Request $request, $id)
    {
        // Validate the request
        Log::info($request->all());
        $validatedData = $request->validate([
            'tender_id' => "required",
            'project_name' => "required",
            'client_name' => "nullable",
            'location' => "required",
            'scope_of_work' => "nullable",
            'commencement_date' => 'nullable|date',
            'duration_of_project' => 'nullable|string|max:255',
            'tender_bond' => 'nullable|numeric',
            'technical_requirements' => 'nullable|string',
            'delay_damages' => 'nullable|string|max:255',
            'type_of_contract' => 'nullable|string|max:255',
            'procurement_route' => 'nullable|string|max:255',
            'advance_payment' => 'nullable|numeric',
            'performance_bond' => 'nullable|string|max:255',
            'retention' => 'nullable|string|max:255',
            'bid_bond' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'tender_submission_date' => 'nullable|date',
            'preliminary_estimate' => 'nullable|numeric',
            'jv_partner' => 'nullable|string|max:255',
            'dry_cost' => 'nullable|numeric',
            'admin_and_salaries' => 'nullable|numeric',
            'profit' => 'nullable|numeric',
            'provisional_sum' => 'nullable|numeric',
            'total_offer_price' => 'nullable|numeric',
            'recommendation' => 'nullable|string', // Add recommendation field
            'images.*' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);

        // Find the fact sheet
        $factSheet = FactSheet::findOrFail($id);

        // Update the fact sheet
        $factSheet->update($validatedData);

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('fact_sheets', 'public');

                Image::create([
                    'fact_sheet_id' => $factSheet->id,
                    'file_name' => $image->getClientOriginalName(),
                    'file_path' => $path,
                ]);
            }
        }

        return response()->json(['message' => 'Fact sheet updated successfully', 'fact_sheet' => $factSheet], 200);
    }
}
