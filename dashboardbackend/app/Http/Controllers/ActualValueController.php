<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActualValueMonthlyBase;
use App\Models\Header;
use Carbon\Carbon;
class ActualValueController extends Controller
{

    public function storeActualMonthlyValue(Request $request, $projectId)
    {
        $request->validate([
            'value' => 'required|numeric',
        ]);
    
        $value = $request->value;
    
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
    
        // Get the previous month instead of the current month
        $previousMonthFullName = Carbon::now()->subMonth()->format('F'); 
        $previousMonthAbbreviation = $monthMapping[$previousMonthFullName];
        $currentYear = Carbon::now()->year;
    
        $monthlyValueRecord = ActualValueMonthlyBase::firstOrCreate(
            ['projectId' => $projectId, 'year' => $currentYear],
            array_fill_keys(array_values($monthMapping), 0)
        );
    
        // Store the value for the previous month
        $monthlyValueRecord->$previousMonthAbbreviation = $value;
        $monthlyValueRecord->save();
    
        return response()->json(['message' => 'Monthly value saved successfully']);
    }
    
    
    public function calculateCumulativeValueBeforeCurrentMonth($projectId) {
        $currentMonth = now()->subMonth()->format('m'); // Get the previous month number
        $currentYear = now()->year;
        
        $header = Header::where('id', $projectId)->first();
    
        if (!$header) {
            return response()->json(['message' => 'Project not found'], 404);
        }
    
        $monthlyReports = ActualValueMonthlyBase::where('projectId', $projectId)
                            ->where('year', '<=', $currentYear)
                            ->get();
    
        $cumulativeActual = 0;
    
        foreach ($monthlyReports as $report) {
            foreach (['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'] as $index => $month) {
                // Only sum values up to the previous month
                if ($report->year < $currentYear || ($report->year == $currentYear && $index < $currentMonth)) {
                    $cumulativeActual += $report->{$month};
                }
            }
        }
    
        return response()->json([
            'cumulativeBudget' => $cumulativeActual
        ]);
    }
    public function getCurrentMonthActualValue(Request $request, $projectId)
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
    
        // Get the previous month and year
        $previousMonthFullName = Carbon::now()->subMonth()->format('F'); // 'January', 'February', etc.
        $previousMonthAbbreviation = $monthMapping[$previousMonthFullName];
        $currentYear = Carbon::now()->year;
    
        // Attempt to retrieve the record for the previous month and year
        $monthlyValueRecord = ActualValueMonthlyBase::where('projectId', $projectId)
                                            ->where('year', $currentYear)
                                            ->first();
    
        if (!$monthlyValueRecord) {
            return response()->json(['message' => 'No record found for the previous month'], 404);
        }
    
        // Return the value for the previous month
        return response()->json([
            'month' => $previousMonthFullName,
            'year' => $currentYear,
            'value' => $monthlyValueRecord->{$previousMonthAbbreviation},
        ]);
    }
}
