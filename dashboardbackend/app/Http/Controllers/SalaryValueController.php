<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Header;
use Carbon\Carbon;
use App\Models\SalaryMonthlyBase;
class SalaryValueController extends Controller
{
    public function storeSalaryMonthlyValue(Request $request, $projectId)
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

        // Retrieve or create the record for the previous year
        $monthlyValueRecord = SalaryMonthlyBase::firstOrCreate(
            ['projectId' => $projectId, 'year' => $previousYear],
            array_fill_keys(['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'], 0)
        );

        // Update the value for the previous month
        $monthlyValueRecord->$previousMonthAbbreviation = $value;
        $monthlyValueRecord->save();

        return response()->json(['message' => 'Monthly salary value for the previous month saved successfully']);
    }
    
    public function calculateCumulativeSalaryValue($projectId) {
        $previousMonth = Carbon::now()->subMonth()->format('m');
        $currentYear = Carbon::now()->year;

        $header = Header::where('id', $projectId)->first();

        if (!$header) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        // Retrieve all monthly reports for the given project ID up to the previous month and year
        $monthlyReports = SalaryMonthlyBase::where('projectId', $projectId)
                            ->where('year', '<=', $currentYear)
                            ->get();

        $cumulativeSalary = 0;

        foreach ($monthlyReports as $report) {
            foreach (['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'] as $index => $month) {
                // Add to cumulative for past years or months before the previous month
                if ($report->year < $currentYear || ($report->year == $currentYear && $index < $previousMonth)) {
                    $cumulativeSalary += $report->{$month};
                }
            }
        }
        return response()->json([
            'cumulativeSalary' => $cumulativeSalary
        ]);
    }
public function getCurrentMonthSalaryValue(Request $request, $projectId)
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

    // Attempt to retrieve the record for the previous month and year
    $monthlyValueRecord = SalaryMonthlyBase::where('ProjectId', $projectId)
                                     ->where('year', $previousYear)
                                     ->first();

    if (!$monthlyValueRecord) {
        return response()->json(['message' => 'No salary value record found for the previous month'], 404);
    }

    // Return the value for the previous month
    return response()->json([
        'month' => $previousMonthFullName,
        'year' => $previousYear,
        'value' => $monthlyValueRecord->{$previousMonthAbbreviation},
    ]);
}
}
