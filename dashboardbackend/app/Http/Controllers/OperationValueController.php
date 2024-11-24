<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OperationValue;
use App\Models\Header;
use Carbon\Carbon;
class OperationValueController extends Controller
{
    public function storeOperationValuesMonthlyValue(Request $request, $projectId)
{
    $request->validate([
        'value' => 'required|numeric',
    ]);

    $value = $request->value;

    $header = Header::where('id', $projectId)->first();
    if (!$header) {
        return response()->json(['message' => 'Project header not found'], 404);
    }

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

    // Check if a OperationValue record already exists for the previous month and year
    $monthlyValueRecord = OperationValue::where('ProjectId', $projectId)
                                            ->where('year', $previousYear)
                                            ->first();

    if (!$monthlyValueRecord) {
        // If not, create a new OperationValue record for the previous year
        $monthlyValueRecord = new OperationValue();
        $monthlyValueRecord->ProjectId = $projectId;
        $monthlyValueRecord->year = $previousYear;

        // Initialize all months to 0
        foreach ($monthMapping as $monthFullName => $monthAbbreviation) {
            $monthlyValueRecord->$monthAbbreviation = 0;
        }
    }

    // Update the value for the previous month
    $monthlyValueRecord->$previousMonthAbbreviation = $value;
    $monthlyValueRecord->save();

    return response()->json(['message' => 'Operation value for the previous month saved successfully']);
}

public function getOperationValuesMonthlyValue(Request $request, $projectId)
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
$monthlyValueRecord = OperationValue::where('ProjectId', $projectId)
                                     ->where('year', $previousYear)
                                     ->first();

if (!$monthlyValueRecord) {
    return response()->json(['message' => 'No operation value record found for the previous month'], 404);
}

// Return the value for the previous month
return response()->json([
    'month' => $previousMonthFullName,
    'year' => $previousYear,
    'value' => $monthlyValueRecord->{$previousMonthAbbreviation},
]);
}
}
