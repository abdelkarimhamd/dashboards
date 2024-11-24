<?php

namespace App\Http\Controllers\ProcurementControllers;

use App\Http\Controllers\Controller;
use App\Models\ProcurementModels\ProcurementDailyTransfers;
use App\Models\ProcurementModels\ProcurementPayments;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
class ProcurementPaymentsController extends Controller
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
            'payment_sections.*.invoice_status' => 'required|string|max:255',
            'payment_sections.*.category_of_charging' => 'required|string|max:255',
            'payment_sections.*.invoice_amount' => 'required|numeric',
        ]);

        // If validation fails, return error messages
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Calculate the total invoice amount by summing all the invoice_amount values
        $totalAmount = array_reduce($request->payment_sections, function ($carry, $section) {
            return $carry + $section['invoice_amount'];
        }, 0);
        // Loop through the payment sections and store each section with common data
        foreach ($request->payment_sections as $section) {
            ProcurementPayments::create([
                'project_name' => $request->project_name,
                'project_type' => $request->project_type,
                'supplier_name' => $request->supplier_name,
                'scope_of_services' => $request->scope_of_services,
                'payment_contract' => $section['payment_contract'],
                'due_date' => $section['due_date_payment'],
                'invoice_status' => $section['invoice_status'],
                'category_of_charging' => $section['category_of_charging'],
                'invoice_amount' => $section['invoice_amount'],
                'total_amount' => $totalAmount,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Procurement payments successfully stored',
        ], 201);
    }


    public function getPaymentsData()
{
    $transfers = ProcurementPayments::orderBy('project_name')
        ->get();
    return response()->json([
        'status' => 'success',
        'data' => $transfers,
    ], 200);
}

function calculateDelayDays($dueDate, $invoiceAmount)
{
    // If the invoice amount is zero, no delay days are calculated
    if ($invoiceAmount == 0) {
        return 0;
    }

    // Parse the due date
    $dueDate = new DateTime($dueDate);
    $currentDate = new DateTime(); // Current date

    // Calculate the difference in days
    $interval = $currentDate->diff($dueDate);
    $delayDays = $interval->format('%R%a'); // Get the difference in days

    // Return delay days only if the current date is after the due date
    return ($delayDays < 0) ? abs($delayDays) : 0;
}

public function getInvoiceProjectData($project_name)
{
    // Fetch transfers based on the project name
    $transfers = ProcurementPayments::where('project_name', $project_name)->get();

    // Loop through each transfer and calculate delay days
    $result = $transfers->map(function ($transfer) {
        $dueDate = $transfer->due_date;
        $invoiceAmount = $transfer->invoice_amount;
        
        // Calculate delay days using the helper function
        $delayDays = $this->calculateDelayDays($dueDate, $invoiceAmount);

        // Add delay_days to the transfer data
        return array_merge($transfer->toArray(), [
            'delay_days' => $delayDays,
        ]);
    });



    return response()->json([
        'status' => 'success',
        'data' => $result,
    ], 200);
}


public function calculateAverageInvoicing()
{
    // Fetch the oldest and newest due dates from invoices where invoice_amount is greater than zero
    $oldestDueDate = ProcurementPayments::where('invoice_amount', '>', 0)
        ->orderBy('due_date', 'asc')
        ->value('due_date');
    
    $newestDueDate = ProcurementPayments::where('invoice_amount', '>', 0)
        ->orderBy('due_date', 'desc')
        ->value('due_date');
        Log::info("Oldest Due Date: $oldestDueDate");
        Log::info("Newest Due Date: $newestDueDate");
    // If there are no invoices with amount greater than zero, return 0 to avoid division by zero
    if (!$oldestDueDate || !$newestDueDate) {
        return response()->json([
            'status' => 'success',
            'average_invoicing' => 0,
            'message' => 'No invoices with status pending.',
        ], 200);
    }

    // Convert the dates to DateTime objects
    $startDate = new DateTime($oldestDueDate);
    $endDate = new DateTime($newestDueDate);

    // Calculate the difference in months between the two dates
    $interval = $startDate->diff($endDate);
    $totalMonths = ($interval->y * 12) + $interval->m + 1; // Adding 1 to include the starting month
    
    Log::info("Total Months Between Oldest and Newest Due Dates: $totalMonths");

    // Get the count of invoices where invoice_amount is greater than zero
    $invoiceCount = ProcurementPayments::where('invoice_amount', '>', 0)->count();

    // Calculate the average invoicing
    $averageInvoicing = $totalMonths > 0 && $invoiceCount > 0 
        ? $totalMonths / $invoiceCount 
        : 0;

    // Return the average invoicing
    return response()->json([
        'status' => 'success',
        'average_invoicing' => $averageInvoicing,
        'message' => 'Average invoicing calculated successfully.',
    ], 200);
}
public function getTotalInvoiceAmount()
{
    // Calculate the total value of all invoices where invoice_amount is greater than zero
    $totalInvoiceAmount = ProcurementPayments::where('invoice_amount', '>', 0)->sum('invoice_amount');

    // Log the total invoice amount
    Log::info("Total Invoice Amount: $totalInvoiceAmount");

    // Format the total amount for both thousands (K) and millions (M)
   
    $formattedInMillions = $this->formatToMillions($totalInvoiceAmount);

    // Return the total invoice amount with formatted values
    return response()->json([
        'status' => 'success',
        'formatted_in_millions' => $formattedInMillions,
        'total_invoices' => $totalInvoiceAmount,
        'message' => 'Total invoice amount calculated successfully.',
    ], 200);
}

private function formatToMillions($amount)
{

        // Return the amount in millions (M) with comma formatting
        return number_format($amount / 1000000, 2);
 
}
public function getTotalForecastedInvoiceAmount()
{
    // Get the start date of the next month
    $nextMonthStart = now()->startOfMonth()->addMonth();

    // Calculate the total forecasted invoice amount where the due date is next month or later
    $totalForecastedAmount = ProcurementPayments::where('due_date', '>=', $nextMonthStart)
        ->sum('invoice_amount');

    // Log the total forecasted invoice amount
    Log::info("Total Forecasted Invoice Amount: $totalForecastedAmount");

    // Format the total amount in millions (M)
    $formattedInMillions = $this->formatToMillions($totalForecastedAmount);

    // Return the total forecasted invoice amount as a formatted response
    return response()->json([
        'status' => 'success',
        'total_forecasted_invoice_amount' => $formattedInMillions,
        'message' => 'Total forecasted invoice amount calculated successfully.',
    ], 200);
}

public function getProjectFinancials()
{
    // Get the current date to filter transfers and invoices
    $currentDate = now();

    // Step 1: Get total paid amounts for each project
    $paidAmounts = ProcurementDailyTransfers::select('project_name', DB::raw('SUM(transfer_amount) as total_paid'))
        ->where('transfer_date', '<=', $currentDate)
        ->groupBy('project_name')
        ->get()
        ->keyBy('project_name'); // Key by project name for easy access

    // Step 2: Get total invoice amounts (total) for each project where due date is until the current date
    $totalAmounts = ProcurementPayments::select('project_name', DB::raw('SUM(invoice_amount) as total_invoices'))
        ->where('due_date', '<=', $currentDate)
        ->groupBy('project_name')
        ->get()
        ->keyBy('project_name'); // Key by project name for easy access

    // Step 3: Get the count of active suppliers for each project
    $activeSuppliersCounts = ProcurementPayments::select('project_name', DB::raw('COUNT(DISTINCT supplier_name) as active_suppliers_count'))
        ->groupBy('project_name')
        ->get()
        ->keyBy('project_name'); // Key by project name for easy access

    // Step 4: Combine the data to calculate paid, total, remaining, and active suppliers for each project
    $projectData = [];

    // Combine data from paid and total amounts
    foreach ($totalAmounts as $projectName => $total) {
        $totalAmount = $total->total_invoices;
        $paidAmount = $paidAmounts[$projectName]->total_paid ?? 0;
        $remainingAmount = $totalAmount - $paidAmount;
        $activeSuppliers = $activeSuppliersCounts[$projectName]->active_suppliers_count ?? 0;

        // Convert to millions
        $formattedPaid = $this->formatToMillions($paidAmount);
        $formattedTotal = $this->formatToMillions($totalAmount);
        $formattedRemaining = $this->formatToMillions($remainingAmount);

        $projectData[] = [
            'project_name' => $projectName,
            'paid' => $formattedPaid,
            'total' => $formattedTotal,
            'remaining' => $formattedRemaining,
            'active_suppliers' => $activeSuppliers, // Add active suppliers count
        ];
    }

    // Include projects with paid amounts but no corresponding total amounts in the invoices table
    foreach ($paidAmounts as $projectName => $paid) {
        if (!isset($totalAmounts[$projectName])) {
            $paidAmount = $paid->total_paid;
            $totalAmount = 0;
            $remainingAmount = $totalAmount - $paidAmount;
            $activeSuppliers = $activeSuppliersCounts[$projectName]->active_suppliers_count ?? 0;

            // Convert to millions
            $formattedPaid = $this->formatToMillions($paidAmount);
            $formattedTotal = $this->formatToMillions($totalAmount);
            $formattedRemaining = $this->formatToMillions($remainingAmount);

            $projectData[] = [
                'project_name' => $projectName,
                'paid' => $formattedPaid,
                'total' => $formattedTotal,
                'remaining' => $formattedRemaining,
                'active_suppliers' => $activeSuppliers, // Add active suppliers count
            ];
        }
    }

    // Return the aggregated data
    return response()->json([
        'status' => 'success',
        'data' => $projectData,
        'message' => 'Project financial data fetched successfully.',
    ], 200);
}

}
