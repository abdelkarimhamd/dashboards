<?php

namespace App\Http\Controllers\FitOutControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\FitOutProject;
use App\Models\FoActualPercentage;

class FitOutActualPercentageController extends Controller
{
    public function storeFoActualPercentageMonthlyValue(Request $request, $projectId)
    {
        $request->validate([
            'value' => 'required|numeric',
        ]);
    
        $value = $request->value;
    
        // Check if the project header exists
        $header = FitOutProject::where('id', $projectId)->first();
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
        $monthlyValueRecord = FoActualPercentage::firstOrCreate(
            ['projectId' => $projectId, 'year' => $currentYear],
            array_fill_keys(['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'], 0)
        );
    
        // Update the value for the current month
        $monthlyValueRecord->$currentMonthAbbreviation = $value;
        $monthlyValueRecord->save();
    
        return response()->json(['message' => 'Monthly value saved successfully']);
    }
    
public function getCurrentFoActualPercentageMonthValue(Request $request, $projectId)
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
    $monthlyValueRecord = FoActualPercentage::where('ProjectId', $projectId)
                                         ->where('year', $currentYear)
                                         ->first();

    if (!$monthlyValueRecord) {
        return response()->json(['message' => 'No operation value record found for the current month'], 404);
    }

    // Return the value for the current month
    return response()->json([
        'month' => $currentMonthFullName,
        'year' => $currentYear,
        'value' => $monthlyValueRecord->{$currentMonthAbbreviation},
    ]);
}
}
