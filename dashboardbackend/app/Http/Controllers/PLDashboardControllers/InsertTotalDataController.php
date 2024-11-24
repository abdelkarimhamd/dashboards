<?php

namespace App\Http\Controllers\PLDashboardControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class InsertTotalDataController extends Controller
{
    public function updateFinancialTotals(Request $request)
    {
                    // Get the authenticated user and their branch
                    $user = auth()->user();
                    $userBranch = $user->branch;
            
                    // Check if the branch is available
                    if (!$userBranch) {
                        return response()->json(['error' => 'User branch not found'], 400);
                    }
        $selectedYear = $request->year;
        if (!$selectedYear) {
            return response()->json("Year not selected", 400);
        }

        try {
            $totalFcInvoice = DB::table('fcinvoice')->where('Year', $selectedYear)->where('branch', $userBranch)->sum('Total');
            $totalActualInvoice = DB::table('actualinvoice')->where('Year', $selectedYear)->where('branch', $userBranch)->sum('Total');
            $totalActualCashin = DB::table('cashin')->where('Year', $selectedYear)->where('branch', $userBranch)->sum('Total');
            $totalActualCashout = DB::table('cashout')->where('Year', $selectedYear)->where('branch', $userBranch)->sum('Total');

            DB::table('totalValues')->updateOrInsert(
                ['YearSelected' => $selectedYear],
                [
                    'TotalFCInvoice' => $totalFcInvoice,
                    'TotalActualInvoice' => $totalActualInvoice,
                    'TotalActualCashin' => $totalActualCashin,
                    'TotalActualCashout' => $totalActualCashout
                ]
            );

            return response()->json([
                'TotalFCInvoice' => $totalFcInvoice,
                'TotalActualInvoice' => $totalActualInvoice,
                'TotalActualCashin' => $totalActualCashin,
                'TotalActualCashout' => $totalActualCashout
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error executing query: ' . $e->getMessage()], 500);
        }
    }
}
