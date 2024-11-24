<?php

namespace App\Http\Controllers\ProcurementControllers;

use App\Http\Controllers\Controller;
use App\Models\ProcurementModels\ProcurementSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
class ProcurementSuppliersController extends Controller
{
    public function store(Request $request)
    {
        // Validate the form inputs
        $validator = Validator::make($request->all(), [
            'payment_sections.*.project_description' => 'required|string|max:255',
            'payment_sections.*.supplier' => 'required|string|max:255',
            'payment_sections.*.invoice_amount' => 'nullable|numeric',
            'payment_sections.*.remarks' => 'nullable|string|max:255',
            'payment_sections.*.priority' => 'nullable|string|max:255',
            'payment_sections.*.invoice_status' => 'nullable|string|max:255',
            'payment_sections.*.status' => 'required|string|max:255',
            'payment_sections.*.services' => 'required|string|max:255',
            
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
            ProcurementSupplier::create([
                'project_description' => $section['project_description'],
                'supplier_name' => $section['supplier'], // Make sure this matches
                'amount' => $section['invoice_amount'], // Make sure this matches
                'remarks' => $section['remarks'] ?? null, // Nullable fields
                'priority' => $section['priority'] ?? null,
                'invoice_status' => $section['invoice_status'] ?? null,
                'status' => $section['status'], 
                'services' => $section['services'], 
                
            ]);
        }
    
        return response()->json([
            'status' => 'success',
            'message' => 'Procurement suppliers successfully stored',
        ], 201);
    }
    public function getSuppliersWithServices()
    {
        // Fetch suppliers and their services
        $suppliers = ProcurementSupplier::select('supplier_name', 'services')
            ->orderBy('supplier_name')
            ->get();
    
        $formattedSuppliers = [];
    
        // Format the data to have a list of services for each supplier
        foreach ($suppliers as $supplier) {
            // Check if the supplier already exists in the array
            if (!isset($formattedSuppliers[$supplier->supplier_name])) {
                $formattedSuppliers[$supplier->supplier_name] = [
                    'supplier_name' => $supplier->supplier_name,
                    'services' => [],
                ];
            }
    
            // Add the service if it's not already in the list
            if (!in_array($supplier->services, $formattedSuppliers[$supplier->supplier_name]['services'])) {
                $formattedSuppliers[$supplier->supplier_name]['services'][] = $supplier->services;
            }
        }
    
        // Convert to a list of formatted supplier data
        $finalSuppliers = array_values($formattedSuppliers);
        return response()->json([
            'status' => 'success',
            'data' => $finalSuppliers,
        ], 200);
    }

    
    public function getSuppliersStatusCounts()
    {
        // Count the number of active and inactive suppliers
        $activeSuppliersCount = ProcurementSupplier::where('status', 'active')->count();
        $totalSuppliersCount  = ProcurementSupplier::count();
    
        return response()->json([
            'status' => 'success',
            'data' => [
                'active_suppliers' => $activeSuppliersCount,
                'total_suppliers' => $totalSuppliersCount,
            ],
        ], 200);
    }
}
