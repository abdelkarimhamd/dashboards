<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Header;
use Carbon\Carbon;
use App\Models\StaffRegistartion;
class StaffRegistartionValue extends Controller
{
    public function storeStaffMonthlyValue(Request $request, $projectId)
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
    
        // Get the current month and year
        $currentMonthFullName = Carbon::now()->format('F'); // 'January', 'February', etc.
        $currentMonthAbbreviation = $monthMapping[$currentMonthFullName];
        $currentYear = Carbon::now()->year;
    
        // Retrieve or create the ActualValueMonthlyBase record for the current year
        $monthlyValueRecord = StaffRegistartion::firstOrCreate(
            ['projectId' => $projectId, 'year' => $currentYear],
            array_fill_keys(['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'], 0)
        );
    
        // Update the value for the current month
        $monthlyValueRecord->$currentMonthAbbreviation = $value;
        $monthlyValueRecord->save();
    
        return response()->json(['message' => 'Monthly value saved successfully']);
    }
    
    public function calculateCumulativeStaffValue($projectId) {
        $currentMonth = now()->format('m');
        $currentYear = now()->year;
        
        $header = Header::where('id', $projectId)->first();
    
        if (!$header) {
            return response()->json(['message' => 'Project not found'], 404);
        }
    
        //$totalProjectValue = $header->projectValue;
        // Retrieve all monthly reports for the given project ID up to the current month and year
        $monthlyReports = StaffRegistartion::where('projectId', $projectId)
                            ->where('year', '<=', $currentYear)
                            ->get();
    
        $cumulativeStaff = 0;
    
        foreach ($monthlyReports as $report) {
            foreach (['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'] as $index => $month) {
                // If the report is from a past year or the current month of the current year, add to cumulative
                if ($report->year < $currentYear || ($report->year == $currentYear && $index < $currentMonth)) {
                    $cumulativeStaff += $report->{$month};
                }
            }
        }
        return response()->json([
            'cumulativeStaff' => $cumulativeStaff

    
        ]);
    }
public function getCurrentMonthStaffValue(Request $request, $projectId)
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

    // Get the current month and year
    $currentMonthFullName = Carbon::now()->format('F'); // 'January', 'February', etc.
    $currentMonthAbbreviation = $monthMapping[$currentMonthFullName];
    $currentYear = Carbon::now()->year;

    // Attempt to retrieve the OperationValue record for the current month and year
    $monthlyValueRecord = StaffRegistartion::where('ProjectId', $projectId)
                                         ->where('year', $currentYear)
                                         ->first();

    if (!$monthlyValueRecord) {
        return response()->json(['message' => 'No staff value record found for the current month'], 404);
    }

    // Return the value for the current month
    return response()->json([
        'month' => $currentMonthFullName,
        'year' => $currentYear,
        'value' => $monthlyValueRecord->{$currentMonthAbbreviation},
    ]);
}
}
