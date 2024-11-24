<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Header;
use Carbon\Carbon;
use App\Models\CashInMonthlyBase;
class CashinValueController extends Controller
{
    public function storeCashinMonthlyValue(Request $request, $projectId)
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
        $previousMonthFullName = Carbon::now()->subMonth()->format('F'); // Get the previous month
        $previousMonthAbbreviation = $monthMapping[$previousMonthFullName];
        $previousYear = Carbon::now()->subMonth()->year;
    
        // Retrieve or create the CashInMonthlyBase record for the previous year
        $monthlyValueRecord = CashInMonthlyBase::firstOrCreate(
            ['projectId' => $projectId, 'year' => $previousYear],
            array_fill_keys(['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'], 0)
        );
    
        // Update the value for the previous month
        $monthlyValueRecord->$previousMonthAbbreviation = $value;
        $monthlyValueRecord->save();
    
        return response()->json(['message' => 'Monthly value saved for the previous month successfully']);
    
    }
    
    public function calculateCumulativeCashinValue($projectId) {
        $currentMonth = now()->subMonth()->format('m'); // Get the previous month
        $currentYear = now()->subMonth()->year; // Adjust the year if it's January
        
        $header = Header::where('id', $projectId)->first();
        
        if (!$header) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        
        // Retrieve all monthly reports for the given project ID up to the previous month and year
        $monthlyReports = CashInMonthlyBase::where('projectId', $projectId)
            ->where('year', '<=', $currentYear)
            ->get();
        
        $cumulativeCashin = 0;
        
        foreach ($monthlyReports as $report) {
            foreach (['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'] as $index => $month) {
                // If the report is from a past year, or if it's from the previous year and month, add to cumulative
                if ($report->year < $currentYear || ($report->year == $currentYear && $index + 1 <= $currentMonth)) {
                    $cumulativeCashin += $report->{$month};
                }
            }
        }
        
        return response()->json([
            'cumulativeCashin' => $cumulativeCashin
        ]);
    }
public function getCurrentMonthCashinValue(Request $request, $projectId)
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
$previousMonthFullName = Carbon::now()->subMonth()->format('F'); // 'January', 'February', etc.
$previousMonthAbbreviation = $monthMapping[$previousMonthFullName];
$previousYear = Carbon::now()->subMonth()->year;

// Attempt to retrieve the CashInMonthlyBase record for the previous month and year
$monthlyValueRecord = CashInMonthlyBase::where('projectId', $projectId)
                                     ->where('year', $previousYear)
                                     ->first();

if (!$monthlyValueRecord) {
    return response()->json(['message' => 'No cashin value record found for the previous month'], 404);
}

// Return the value for the previous month
return response()->json([
    'month' => $previousMonthFullName,
    'year' => $previousYear,
    'value' => $monthlyValueRecord->{$previousMonthAbbreviation},
]);
}
}
