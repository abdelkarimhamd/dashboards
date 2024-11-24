<?php

namespace App\Http\Controllers\PettyCashControllers;

use App\Http\Controllers\Controller;
use App\Models\PettyCashModels\PettyCashGeneralExpense;
use App\Models\PettyCashModels\PettyCashRequest;
use App\Models\PettyCashModels\PettyCashRequestExpense;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
class PettyCashInsertData extends Controller
{


    public function storeExpenses(Request $request)
    {
        // Validate form input
        $validator = Validator::make($request->all(), [
            'requester_name' => 'required|string|max:255',
            'requester_title' => 'nullable|string|max:255',
            'requester_department' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'comments' => 'nullable|string',
            'expense_details' => 'required|array',
            'expense_details.*.expenses_description' => 'nullable|string',
            'expense_details.*.project_name' => 'nullable|string',
            'expense_details.*.invoice_number' => 'nullable|string|max:50',
            'expense_details.*.expenses' => 'nullable|numeric',
            'expense_details.*.transport' => 'nullable|numeric',
            'expense_details.*.consumables' => 'nullable|numeric',
            'expense_details.*.chargable' => 'nullable|numeric',
            'expense_details.*.invoice_image' => 'nullable|file|mimes:jpeg,png,pdf',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Ensure the 'public/invoices' directory exists
        $invoicesPath = public_path('invoices');
        if (!file_exists($invoicesPath)) {
            mkdir($invoicesPath, 0755, true);
        }
    
        // Create a unique main request record
        $mainRequest = new PettyCashRequestExpense();
        $mainRequest->requester_name = $request->input('requester_name');
        $mainRequest->requester_title = $request->input('requester_title');
        $mainRequest->requester_department = $request->input('requester_department');
        $mainRequest->location = $request->input('location');
        $mainRequest->comments = $request->input('comments');
        $mainRequest->save();
    
        // Process each expense row and link to main request ID
        foreach ($request->input('expense_details') as $index => $expenseDetail) {
            $expense = new PettyCashRequest();
            $expense->petty_cash_request_id = $mainRequest->id; // Associate with main request ID
            $expense->expenses_description = $expenseDetail['expenses_description'];
            $expense->project_name = $expenseDetail['project_name'];
            $expense->invoice_number = $expenseDetail['invoice_number'];
            $expense->expenses = $expenseDetail['expenses'] ?? 0.00;
            $expense->transport = $expenseDetail['transport'] ?? 0.00;
            $expense->consumables = $expenseDetail['consumables'] ?? 0.00;
            $expense->chargable = $expenseDetail['chargable'] ?? 0.00;
    
            // Handle file upload for invoice image
            if ($request->hasFile("expense_details.$index.invoice_image")) {
                $file = $request->file("expense_details.$index.invoice_image");
                $filename = time() . '_' . $file->getClientOriginalName();
                $filePath = "invoices/{$filename}";
    
                // Move the file to public/invoices
                $file->move(public_path('invoices'), $filename);
    
                // Store the relative path in the database
                $expense->invoice_image = $filePath;
            }
    
            $expense->save();
        }
    
        return response()->json(['message' => 'Expenses saved successfully'], 201);
    }

    public function getExpensesByProjectAndDate($projectName, $year, $month)
{
    $expenses = PettyCashRequest::where('project_name', $projectName)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->get();

    return response()->json($expenses);
}

public function getProjectAndGeneralTotals($year, $month)
{
    // Get totals for each project from petty_cash_requests filtered by year and month
    $projectTotals = PettyCashRequest::select('project_name', 
                            DB::raw('SUM(expenses + transport + consumables + chargable) as total_amount'))
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->groupBy('project_name')
                        ->orderBy('project_name', 'asc')
                        ->get();

    // Get the total for general expenses from petty_cash_general_expenses filtered by year and month
    $generalExpensesTotal = PettyCashGeneralExpense::whereYear('created_at', $year)
                            ->whereMonth('created_at', $month)
                            ->sum('amount');

    return response()->json([
        'status' => 'success',
        'project_totals' => $projectTotals,
        'general_expenses_total' => $generalExpensesTotal,
    ], 200);
}



public function getAvailableInvoiceMonths()
{
    $months = PettyCashRequest::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month')
                ->distinct()
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();

    return response()->json($months);
}
    
    public function storeGeneralExpenses(Request $request)
    {
        // Validate the incoming data
        $validator = Validator::make($request->all(), [
            'expense_details' => 'required|array',
            'expense_details.*.material' => 'required|string|max:255',
            'expense_details.*.amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Process each row in expense_details and save to the database
        foreach ($request->input('expense_details') as $expenseDetail) {
            PettyCashGeneralExpense::create([
                'material' => $expenseDetail['material'],
                'amount' => $expenseDetail['amount'],
            ]);
        }

        return response()->json(['message' => 'Expenses saved successfully'], 201);
    }



    // Get unique year and month combinations
public function getAvailableMonths()
{
    $months = PettyCashGeneralExpense::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month')
                ->distinct()
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();

    return response()->json($months);
}

// Get expenses for a specific year and month
public function getMonthlyExpenses($year, $month)
{
    $expenses = PettyCashGeneralExpense::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->get();

    return response()->json($expenses);
}



    public function countUniqueRequests()
    {
        // Use the Eloquent model or DB facade to execute a more specific query
        $count = PettyCashRequestExpense::select('requester_name', 'created_at')
                                 ->where('status', 'pending')  // Add this line to filter by 'pending' status
                                 ->get()
                                 ->count();
    
        return response()->json(['unique_request_count' => $count], 200);
    }
    public function getPendingRequests()
    {
        // Fetch all pending requests with necessary fields
        $requests = PettyCashRequestExpense::select('id', 'requester_name', 'location', 'status', 'created_at')
                                           ->where('status', 'pending') // Filter by status 'pending'
                                           ->get();
    
        // Group and format results for unique requester and timestamp
        $pendingRequests = $requests->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d H:i:s') . '|' . $item->requester_name;
        })->map(function ($items) {
            $firstItem = $items->first();
            return [
                'id' => $firstItem->id,
                'requester_name' => $firstItem->requester_name,
                'location' => $firstItem->location,
                'status' => $firstItem->status,
                'created_at' => $firstItem->created_at->format('Y-m-d')
            ];
        })->values(); // Convert the collection to an indexed array
    
        return response()->json($pendingRequests, 200);
    }
    
    public function getRequestDetailsById(Request $request)
    {
        // Validate the incoming request parameters
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Extract validated data
        $id = $request->input('id');
    
        // Fetch the main request
        $mainRequest = PettyCashRequestExpense::where('id', $id)->first();
    
        if (!$mainRequest) {
            return response()->json(['message' => 'No request found for the provided ID'], 404);
        }
    
        // Fetch related expenses based on petty_cash_request_id
        $expenses = PettyCashRequest::where('petty_cash_request_id', $id)->get();
    
        // Combine the main request data with the expenses
        $response = [
            'request' => $mainRequest,
            'expenses' => $expenses,
        ];
    Log::info($response);
        return response()->json($response);
    }
    
    
    public function updateRequest(Request $request, $id)
    {
        Log::info('Received updateRequest data', $request->all());
    
        // Validate form input
        $validator = Validator::make($request->all(), [
            'requester_name' => 'required|string|max:255',
            'requester_title' => 'nullable|string|max:255',
            'requester_department' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'comments' => 'nullable|string',
    
            // Approval-related fields
            'verified_by' => 'nullable|string|max:255',
            'verified_name' => 'nullable|string|max:255',
            'verified_signature' => 'nullable|string|max:255',
            'approved_by_project_manager' => 'nullable|string|max:255',
            'approved_by_ceo' => 'nullable|string|max:255',
            'project_manager_name' => 'nullable|string|max:255',
            'ceo_approver_name' => 'nullable|string|max:255',
            'project_manager_signature' => 'nullable|string|max:255',
            'ceo_approver_signature' => 'nullable|string|max:255',
            'requester_signature' => 'nullable|string|max:255',
            'checked_by_name' => 'nullable|string|max:255',
            'checked_by_signature' => 'nullable|string|max:255',
            'final_approver_name' => 'nullable|string|max:255',
            'final_approver_signature' => 'nullable|string|max:255',
    
            // Expense details validation
            'expense_details' => 'required|array',
            'expense_details.*.id' => 'nullable|integer',
            'expense_details.*.expenses_description' => 'nullable|string',
            'expense_details.*.project_name' => 'nullable|string',
            'expense_details.*.invoice_number' => 'nullable|string|max:50',
            'expense_details.*.expenses' => 'nullable|numeric',
            'expense_details.*.transport' => 'nullable|numeric',
            'expense_details.*.consumables' => 'nullable|numeric',
            'expense_details.*.chargable' => 'nullable|numeric',
            'expense_details.*.invoice_image' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
        ]);
    
        if ($validator->fails()) {
            Log::error('Validation failed', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Ensure the 'public/invoices' directory exists
        $invoicesPath = public_path('invoices');
        if (!file_exists($invoicesPath)) {
            mkdir($invoicesPath, 0755, true);
        }
    
        // Start transaction
        DB::beginTransaction();
        try {
            $mainRequest = PettyCashRequestExpense::find($id);
    
            if (!$mainRequest) {
                Log::error("Request with ID {$id} not found");
                return response()->json(['message' => 'Request not found'], 404);
            }
    
            // Update main request fields
            $mainRequest->requester_name = $request->input('requester_name');
            $mainRequest->requester_title = $request->input('requester_title');
            $mainRequest->requester_department = $request->input('requester_department');
            $mainRequest->location = $request->input('location');
            $mainRequest->comments = $request->input('comments');
    
            // Approval-related fields
            $mainRequest->verified_by = $request->input('verified_by');
            $mainRequest->verified_name = $request->input('verified_name');
            $mainRequest->verified_signature = $request->input('verified_signature');
            $mainRequest->approved_by_project_manager = $request->input('approved_by_project_manager');
            $mainRequest->approved_by_ceo = $request->input('approved_by_ceo');
            $mainRequest->project_manager_name = $request->input('project_manager_name');
            $mainRequest->ceo_approver_name = $request->input('ceo_approver_name');
            $mainRequest->project_manager_signature = $request->input('project_manager_signature');
            $mainRequest->ceo_approver_signature = $request->input('ceo_approver_signature');
            $mainRequest->requester_signature = $request->input('requester_signature');
            $mainRequest->checked_by_name = $request->input('checked_by_name');
            $mainRequest->checked_by_signature = $request->input('checked_by_signature');
            $mainRequest->final_approver_name = $request->input('final_approver_name');
            $mainRequest->final_approver_signature = $request->input('final_approver_signature');
    
            $mainRequest->save();
    
            // Update or create each expense item
            foreach ($request->input('expense_details') as $index => $expenseDetail) {
                if (isset($expenseDetail['id']) && !empty($expenseDetail['id'])) {
                    // Find and update existing expense item
                    $expense = PettyCashRequest::find($expenseDetail['id']);
                } else {
                    // Create new expense item if no ID is provided
                    $expense = new PettyCashRequest();
                    $expense->petty_cash_request_id = $mainRequest->id; // Assuming request_id is the foreign key
                }
    
                // Set or update expense fields
                $expense->expenses_description = $expenseDetail['expenses_description'];
                $expense->project_name = $expenseDetail['project_name'];
                $expense->invoice_number = $expenseDetail['invoice_number'];
                $expense->expenses = $expenseDetail['expenses'] ?? 0.00;
                $expense->transport = $expenseDetail['transport'] ?? 0.00;
                $expense->consumables = $expenseDetail['consumables'] ?? 0.00;
                $expense->chargable = $expenseDetail['chargable'] ?? 0.00;
    
                // Check if a new file is uploaded; if so, replace the existing one
                if ($request->hasFile("expense_details.$index.invoice_image")) {
                    $file = $request->file("expense_details.$index.invoice_image");
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $filePath = "invoices/{$filename}";
    
                    // Move the file to public/invoices
                    $file->move(public_path('invoices'), $filename);
    
                    // Store the new path in the database
                    $expense->invoice_image = $filePath;
                } elseif (isset($expenseDetail['existing_invoice_image'])) {
                    // Retain the existing image path if no new file is uploaded
                    $expense->invoice_image = $expenseDetail['existing_invoice_image'];
                }
    
                $expense->save();
            }
    
            // Commit transaction
            DB::commit();
    
            Log::info('Request updated successfully');
            return response()->json(['message' => 'Request updated successfully'], 200);
    
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            Log::error('Failed to update request', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update request'], 500);
        }
    }
    

    public function getPettyCashData()
    {
        $transfers = PettyCashRequest::orderBy('project_name')
            ->get();
        return response()->json([
            'status' => 'success',
            'data' => $transfers,
        ], 200);
    }
    
    public function getStatusCountsAndProjectCount($year, $month)
    {
        // Get counts of each status for the specified year and month
        $statusCounts = PettyCashRequestExpense::select('status', DB::raw('COUNT(*) as count'))
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->groupBy('status')
                        ->get();
    
        // Get count of unique projects for the specified year and month
        $projectCount = PettyCashRequest::whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->distinct('project_name')
                        ->count('project_name');
    
        // Format the response data
        $data = [
            'status_counts' => $statusCounts,
            'project_count' => $projectCount,
        ];
    
        return response()->json($data, 200);
    }
    
}
