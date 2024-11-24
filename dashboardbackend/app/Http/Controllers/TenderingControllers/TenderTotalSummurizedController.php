<?php

namespace App\Http\Controllers\TenderingControllers;

use App\Http\Controllers\Controller;
use App\Models\Tender;
use Illuminate\Http\Request;
use Carbon\Carbon; 

class TenderTotalSummurizedController extends Controller
{
    public function getTendersForPreviousMonth()
    {
        // Get the current date
        $currentDate = Carbon::now();

        // Get the start and end dates for the previous month
        $startOfPreviousMonth = $currentDate->subMonth()->startOfMonth()->toDateString();
        $endOfPreviousMonth = $currentDate->endOfMonth()->toDateString();

        // Fetch tenders where submissionDate is within the previous month
        $tenders = Tender::whereBetween('created_at', [$startOfPreviousMonth, $endOfPreviousMonth])
        ->whereNotIn('status', ['rejected', 'canceled'])
        ->where('gono', 1) // gono must be 1
        ->select('employerName', 'location', 'tender_logo', 'tender_image', 'selectedOption', 'submissionDate', 'contactDuration', 'tender_value')
        ->get();

        $tenderCount = $tenders->count();
        // Log:info($tenderCount);
        // Return the tenders in JSON format
        return response()->json($tenders);
    }
}
