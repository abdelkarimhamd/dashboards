<?php

namespace App\Http\Controllers\ProcurementControllers;

use App\Http\Controllers\Controller;
use App\Models\ProcurementModels\ProcurementDailyTransfers;
use App\Models\ProcurementModels\ProcurementPayments;
use App\Models\ProcurementModels\ProcurementSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
class ProcurementDailyTransfersController extends Controller
{
    public function store(Request $request)
    {
        // Validate the form inputs
        $validator = Validator::make($request->all(), [
            'project_name' => 'required|string|max:255',
            'project_type' => 'required|string|max:255',
            'supplier_name' => 'required|string|max:255',
            'scope_of_services' => 'required|string|max:255',
            'payment_sections.*.payment_contract' => 'required|string|max:255',
            'payment_sections.*.due_date_payment' => 'required|date',
            'payment_sections.*.invoice_status' => 'nullable|string|max:255',
            'payment_sections.*.invoice_amount' => 'required|numeric',
            'payment_sections.*.status' => 'required|string|max:255',
            'payment_sections.*.transfer_type' => 'required|string|max:255',
            'payment_sections.*.category_of_charging' => 'required|string|max:255',
        ]);
    
        // If validation fails, return error messages
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }
    
        // Loop through the payment sections and store each section with common data
        foreach ($request->payment_sections as $section) {
            ProcurementDailyTransfers::create([
                'project_name' => $request->project_name,
                'project_type' => $request->project_type,
                'supplier_contractor' => $request->supplier_name, // Ensure consistency with model field
                'services' => $request->scope_of_services, // Ensure consistency with model field
                'contract' => $section['payment_contract'],
                'transfer_amount' => $section['invoice_amount'],
                'transfer_type' => $section['transfer_type'],
                'status' => $section['status'],
                'transfer_date' => $section['due_date_payment'],
                'category_of_charging' => $section['category_of_charging'],
                'invoice' => $section['invoice_status'],
            ]);
        }
    
        return response()->json([
            'status' => 'success',
            'message' => 'Procurement daily transfers successfully stored',
        ], 201);
    }

    public function getSuppliersByProject(Request $request)
{
    // Validate that the project_name is provided
    $validator = Validator::make($request->all(), [
        'project_name' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validator->errors(),
        ], 422);
    }

    // Fetch suppliers associated with the given project name, including services
    $suppliers = ProcurementPayments::where('project_name', $request->project_name)
        ->select('supplier_name')
        ->distinct()
        ->get();

    // Fetch services for each supplier
    $supplierWithServices = [];
    foreach ($suppliers as $supplier) {
        $services = ProcurementSupplier::where('supplier_name', $supplier->supplier_name)
            ->pluck('services'); // Fetch all services for this supplier

        $supplierWithServices[] = [
            'supplier_name' => $supplier->supplier_name,
            'services' => $services,
        ];
    }

    

    return response()->json([
        'status' => 'success',
        'data' => $supplierWithServices,
    ], 200);
}

public function getTransfersData()
{
    $transfers = ProcurementDailyTransfers::orderBy('project_name')
        ->orderBy('transfer_date')
        ->get();

    return response()->json([
        'status' => 'success',
        'data' => $transfers,
    ], 200);
}

public function applyTransferAmount(Request $request)
{
    // Validate the request
    $validatedData = $request->validate([
        
        'transfers.*.supplier_contractor' => 'required|string',
        'transfers.*.services' => 'required|string',
        'transfers.*.project_name' => 'required|string',
        'transfers.*.transfer_amount' => 'required|numeric|min:0'
    ]);

    $transferDetails = $validatedData['transfers'];

    foreach ($transferDetails as $transfer) {
        $supplierName = $transfer['supplier_contractor'];
        $scopeOfServices = $transfer['services'];
        $projectName = $transfer['project_name'];
        $transferAmount = $transfer['transfer_amount'];

        // Check if there is a matching daily transfer record
        $matchingTransfer = ProcurementPayments::where('supplier_name', $supplierName)
            ->where('scope_of_services', $scopeOfServices)
            ->where('project_name', $projectName)
            ->exists();

        if (!$matchingTransfer) {
            // Log if no matching record is found
            Log::warning("No matching transfer record found for: $projectName, $scopeOfServices, $supplierName");
            continue; // Skip to the next transfer if no match is found
        }

        // Fetch the invoices for the specified supplier, scope of services, and project name ordered by the oldest due date first
        $invoices = ProcurementPayments::where('supplier_name', $supplierName)
            ->where('scope_of_services', $scopeOfServices)
            ->where('project_name', $projectName)
            ->orderBy('due_date', 'asc')
            ->get();

        $remainingTransferAmount = $transferAmount;

        // Start subtracting the transfer amount from each invoice
        foreach ($invoices as $invoice) {
            if ($remainingTransferAmount <= 0) {
                break; // Stop if the transfer amount is completely applied
            }

            // Get the current invoice amount
            $currentInvoiceAmount = $invoice->invoice_amount;

            if ($remainingTransferAmount >= $currentInvoiceAmount) {
                // If the transfer amount is greater than or equal to the current invoice amount, make it zero
                $remainingTransferAmount -= $currentInvoiceAmount;
                $invoice->invoice_amount = 0;
            } else {
                // If the transfer amount is less than the current invoice amount, subtract and update the amount
                $invoice->invoice_amount -= $remainingTransferAmount;
                $remainingTransferAmount = 0; // Transfer amount fully applied
            }

            // Save the updated invoice
            $invoice->save();
        }
        $this->updateTotalAmount($supplierName, $scopeOfServices, $projectName);

        Log::info('Transfer amount application completed for one entry.', [
            'project_name' => $projectName,
            'supplier' => $supplierName,
            'scope_of_services' => $scopeOfServices,
            'total_applied' => $transferAmount - $remainingTransferAmount,
            'remaining_transfer_amount' => $remainingTransferAmount
        ]);
    }

    // Log the final invoices status after updates
    return response()->json([
        'status' => 'success',
        'message' => 'Transfer amounts applied successfully to invoices.',
    ], 200);
}


private function updateTotalAmount($supplierName, $scopeOfServices, $projectName)
{
    // Fetch the invoices again to calculate the total amount
    $invoices = ProcurementPayments::where('supplier_name', $supplierName)
        ->where('scope_of_services', $scopeOfServices)
        ->where('project_name', $projectName)
        ->get();

    // Calculate the new total amount
    $totalAmount = $invoices->sum('invoice_amount');

    // Update the total_amount field for all matching invoices
    ProcurementPayments::where('supplier_name', $supplierName)
        ->where('scope_of_services', $scopeOfServices)
        ->where('project_name', $projectName)
        ->update(['total_amount' => $totalAmount]);
}

public function getCurrentMonthTotalTransferAmount()
{
    // Get the start and end dates for the current month
    $currentMonthStart = now()->startOfMonth();
    $currentMonthEnd = now()->endOfMonth();

    // Calculate the total transfer amount for the current month
    $totalTransferAmount = ProcurementDailyTransfers::whereBetween('transfer_date', [$currentMonthStart, $currentMonthEnd])
        ->sum('transfer_amount');

    // Log the total transfer amount for the current month
    Log::info("Total Transfer Amount for Current Month: $totalTransferAmount");

    // Format the amount in millions if it exceeds one million
    $formattedTotal = $this->formatToMillions($totalTransferAmount);

    // Return the total transfer amount as a formatted response
    return response()->json([
        'status' => 'success',
        'total_transfer_amount' => $formattedTotal,
        'total_transfer' => $totalTransferAmount,
        'message' => 'Total transfer amount for the current month calculated successfully.',
    ], 200);
}

private function formatToMillions($amount)
{
  
        return number_format($amount / 1000000, 2);
}


}