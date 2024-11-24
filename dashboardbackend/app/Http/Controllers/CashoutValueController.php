<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Header;
use Carbon\Carbon;
use App\Models\CashoutMonthlyBaseValues;
class CashoutValueController extends Controller
{
    public function storeCashoutMonthlyValue(Request $request, $projectId)
    {
        $request->validate([
            'value' => 'required|numeric',
        ]);
    
        $value = $request->value;
    
        // Check if the project header exists
        $header = Header::where('id', $projectId)->first();
        if (!$header) {
            return response()->json(['message' => 'Header not found'], 404);
        }
    
        // Define month abbreviation mapping
        $monthMapping = [
            'January' => 'jan', 'February' => 'feb', 'March' => 'mar',
            'April' => 'apr', 'May' => 'may', 'June' => 'jun',
            'July' => 'jul', 'August' => 'aug', 'September' => 'sep',
            'October' => 'oct', 'November' => 'nov', 'December' => 'december',
        ];
    
        // Get the previous month and year
        $previousMonth = Carbon::now()->subMonth();
        $previousMonthFullName = $previousMonth->format('F'); // 'January', 'February', etc.
        $previousMonthAbbreviation = $monthMapping[$previousMonthFullName];
        $previousYear = $previousMonth->year;
    
        // Retrieve or create the CashoutMonthlyBaseValues record for the previous year
        $monthlyValueRecord = CashoutMonthlyBaseValues::firstOrCreate(
            ['projectId' => $projectId, 'year' => $previousYear],
            array_fill_keys(['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'], 0)
        );
    
        // Update the value for the previous month
        $monthlyValueRecord->$previousMonthAbbreviation = $value;
        $monthlyValueRecord->save();
    
        return response()->json(['message' => 'Monthly value saved successfully']);
    }
    
    public function calculateCumulativeCashoutValue($projectId) {
        $previousMonth = Carbon::now()->subMonth();
    $currentMonth = $previousMonth->format('m'); // Get the previous month
    $currentYear = $previousMonth->year;

    $header = Header::where('id', $projectId)->first();

    if (!$header) {
        return response()->json(['message' => 'Project not found'], 404);
    }

    // Retrieve all monthly reports for the given project ID up to the previous month and year
    $monthlyReports = CashoutMonthlyBaseValues::where('projectId', $projectId)
                            ->where('year', '<=', $currentYear)
                            ->get();

    $cumulativeCashout = 0;

    foreach ($monthlyReports as $report) {
        foreach (['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'] as $index => $month) {
            // If the report is from a past year or if it is for the previous month of the current year, add to cumulative
            if ($report->year < $currentYear || ($report->year == $currentYear && $index + 1 <= $currentMonth)) {
                $cumulativeCashout += $report->{$month};
            }
        }
    }
    return response()->json([
        'cumulativeCashout' => $cumulativeCashout
    ]);
    }
public function getCurrentMonthCashoutValue(Request $request, $projectId)
{
    // Define month abbreviation mapping
    $monthMapping = [
        'January' => 'jan',
        'February' => 'feb',
        'March' => 'mar',
        'April' => 'apr',
        'May' => 'may',
        'June' => 'jun',
        'July' => 'jul',
        'August' => 'aug',
        'September' => 'sep',
        'October' => 'oct',
        'November' => 'nov',
        'December' => 'december',
    ];

    // Get the previous month and year
    $previousMonth = Carbon::now()->subMonth();
    $previousMonthFullName = $previousMonth->format('F'); // 'January', 'February', etc.
    $previousMonthAbbreviation = $monthMapping[$previousMonthFullName];
    $previousYear = $previousMonth->year;

    // Attempt to retrieve the OperationValue record for the previous month and year
    $monthlyValueRecord = CashoutMonthlyBaseValues::where('ProjectId', $projectId)
                                         ->where('year', $previousYear)
                                         ->first();

    if (!$monthlyValueRecord) {
        return response()->json(['message' => 'No Cashout value record found for the previous month'], 404);
    }

    // Return the value for the previous month
    return response()->json([
        'month' => $previousMonthFullName,
        'year' => $previousYear,
        'value' => $monthlyValueRecord->{$previousMonthAbbreviation},
    ]);
}
}
