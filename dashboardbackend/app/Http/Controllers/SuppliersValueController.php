<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Header;
use Carbon\Carbon;
use App\Models\SuppliersMonthlyBase;
class SuppliersValueController extends Controller
{
    
    public function storeSuppliersMonthlyValue(Request $request, $projectId)
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
        $monthlyValueRecord = SuppliersMonthlyBase::firstOrCreate(
            ['projectId' => $projectId, 'year' => $previousYear],
            array_fill_keys(['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'], 0)
        );

        // Update the value for the previous month
        $monthlyValueRecord->$previousMonthAbbreviation = $value;
        $monthlyValueRecord->save();

        return response()->json(['message' => 'Monthly value for the previous month saved successfully']);
    }
    
    public function calculateCumulativeSuppliersValue($projectId) {
        $previousMonth = Carbon::now()->subMonth()->format('m');
        $currentYear = Carbon::now()->year;

        $header = Header::where('id', $projectId)->first();

        if (!$header) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        // Retrieve all monthly reports for the given project ID up to the previous month and year
        $monthlyReports = SuppliersMonthlyBase::where('projectId', $projectId)
                            ->where('year', '<=', $currentYear)
                            ->get();

        $cumulativeSuppliers = 0;

        foreach ($monthlyReports as $report) {
            foreach (['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'] as $index => $month) {
                // Add to cumulative for past years or months before the previous month
                if ($report->year < $currentYear || ($report->year == $currentYear && $index < $previousMonth)) {
                    $cumulativeSuppliers += $report->{$month};
                }
            }
        }
        return response()->json([
            'cumulativeSuppliers' => $cumulativeSuppliers
        ]);
    }
public function getCurrentMonthSuppliersValue(Request $request, $projectId)
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
    $monthlyValueRecord = SuppliersMonthlyBase::where('ProjectId', $projectId)
                                     ->where('year', $previousYear)
                                     ->first();

    if (!$monthlyValueRecord) {
        return response()->json(['message' => 'No supplier value record found for the previous month'], 404);
    }

    // Return the value for the previous month
    return response()->json([
        'month' => $previousMonthFullName,
        'year' => $previousYear,
        'value' => $monthlyValueRecord->{$previousMonthAbbreviation},
    ]);

}
}
