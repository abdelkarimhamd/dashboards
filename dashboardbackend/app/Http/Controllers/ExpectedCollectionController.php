<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExpectedCollectionValue;
use Illuminate\Http\Request;
use App\Models\Header;
use Carbon\Carbon;
class ExpectedCollectionController extends Controller
{
    public function storeExpectedCollectionValue(Request $request, $projectId)
    {
        $request->validate([
            'value' => 'required|numeric',
        ]);

        $value = $request->value;

        // Fetch the project
        $header = Header::where('id', $projectId)->first();
        if (!$header) {
            return response()->json(['message' => 'Header not found'], 404);
        }

        $monthMapping = [
            'January' => 'jan', 'February' => 'feb', 'March' => 'mar',
            'April' => 'apr', 'May' => 'may', 'June' => 'jun',
            'July' => 'jul', 'August' => 'aug', 'September' => 'sep',
            'October' => 'oct', 'November' => 'nov', 'December' => 'december',
        ];

        // Get the current month and year
        $currentMonthFullName = Carbon::now()->format('F'); // e.g., 'January'
        $currentMonthAbbreviation = $monthMapping[$currentMonthFullName];
        $currentYear = Carbon::now()->year;

        // Create or get the existing monthly value record
        $monthlyValueRecord = ExpectedCollectionValue::firstOrCreate(
            ['projectId' => $projectId, 'year' => $currentYear],
            array_fill_keys(array_values($monthMapping), 0)
        );

        // Store the value for the current month
        $monthlyValueRecord->$currentMonthAbbreviation = $value;
        $monthlyValueRecord->save();

        return response()->json(['message' => 'Monthly value saved successfully']);
    }

    /**
     * Calculate cumulative actual values up to the current month
     */
    public function calculateCumulativeExpectedCollectioValue($projectId)
    {
        $currentMonth = Carbon::now()->format('m'); // Get the current month number
        $currentYear = Carbon::now()->year;

        // Fetch the project
        $header = Header::where('id', $projectId)->first();
        if (!$header) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        // Get all monthly reports for the project
        $monthlyReports = ExpectedCollectionValue::where('projectId', $projectId)
                                ->where('year', '<=', $currentYear)
                                ->get();

        $cumulativeActual = 0;
        $monthMapping = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'];

        foreach ($monthlyReports as $report) {
            foreach ($monthMapping as $index => $month) {
                // Sum values up to and including the current month
                if ($report->year < $currentYear || ($report->year == $currentYear && $index + 1 <= $currentMonth)) {
                    $cumulativeActual += $report->{$month};
                }
            }
        }

        return response()->json([
            'cumulativeBudget' => $cumulativeActual
        ]);
    }

    /**
     * Retrieve the invoice value for the current month
     */
    public function getExpectedCollectionValue(Request $request, $projectId)
    {
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
        $currentMonthFullName = Carbon::now()->format('F'); // e.g., 'January'
        $currentMonthAbbreviation = $monthMapping[$currentMonthFullName];
        $currentYear = Carbon::now()->year;

        // Attempt to retrieve the invoice record for the current month and year
        $monthlyValueRecord = ExpectedCollectionValue::where('projectId', $projectId)
                                             ->where('year', $currentYear)
                                             ->first();

        if (!$monthlyValueRecord) {
            return response()->json(['message' => 'No invoice record found for the current month'], 404);
        }

        // Return the value for the current month
        return response()->json([
            'month' => $currentMonthFullName,
            'year' => $currentYear,
            'value' => $monthlyValueRecord->{$currentMonthAbbreviation},
        ]);
    }
}
