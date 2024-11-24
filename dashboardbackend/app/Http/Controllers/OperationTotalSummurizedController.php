<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActualHr;
use App\Models\ActualInvoice;
use App\Models\ActualStaff;
use App\Models\ActualValueMonthlyBase;
use App\Models\Cashin;
use App\Models\CashInMonthlyBase;
use App\Models\Cumulativeacinvoice;
use App\Models\ExpectedCollectionValue;
use App\Models\ExpectedPettyCashValue;
use App\Models\ExpectedSalaryValue;
use App\Models\ExpectedSuppliersValue;
use App\Models\FcInvoice;
use App\Models\Header;
use App\Models\KeyIssuesNotes;
use App\Models\ManpowerValueMonthlyBase;
use App\Models\OperationProjectImage;
use App\Models\PettyCashMonthlyBase;
use App\Models\PmFcInvoiceCurrentMonth;
use App\Models\SalaryMonthlyBase;
use App\Models\SuppliersMonthlyBase;
use App\Models\Tender;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
class OperationTotalSummurizedController extends Controller
{
    public function getMonthlyProjectSummary(Request $request)
{
    // Get the month and year passed by the API
    $requestedMonth = $request->input('month'); // Format: 'January', 'February', etc.
    $requestedYear = $request->input('year'); // Assuming the year is passed as well

    if (!$requestedMonth || !$requestedYear) {
        return response()->json(['error' => 'Month and Year parameters are required'], 400);
    }

    // Map the month name to its corresponding column name in the `monthly_report` table
    $monthColumns = [
        'January' => 'jan', 'February' => 'feb', 'March' => 'mar', 'April' => 'apr',
        'May' => 'may', 'June' => 'jun', 'July' => 'jul', 'August' => 'aug',
        'September' => 'sep', 'October' => 'oct', 'November' => 'nov', 'December' => 'december'
    ];

    // Check if the passed month exists in the month map
    if (!isset($monthColumns[$requestedMonth])) {
        return response()->json(['error' => 'Invalid month passed'], 400);
    }

    // Get the column name corresponding to the passed month
    $expectedMonthColumn = $monthColumns[$requestedMonth];

    // Get the previous month for actual values
    $previousMonth = Carbon::create()->month(date('m', strtotime($requestedMonth)))->subMonth()->format('F');
    $actualMonthColumn = $monthColumns[$previousMonth]; // Use previous month for actuals

    // Get active projects' IDs and Names
    $activeProjects = Header::where('status', 'active')
        ->select('id', 'projectName')
        ->get();

    $activeProjectIds = $activeProjects->pluck('id')->toArray();

    // Filter active projects based on their non-zero value in the `monthly_report` for the given month and year
    $filteredProjectIds = DB::table('monthly_reports')
        ->whereIn('projectId', $activeProjectIds)
        ->where('year', $requestedYear) // Check the year
        ->where($actualMonthColumn, '>', 0) // Check if the value for the given month is greater than zero
        ->pluck('projectId')
        ->toArray();

    

    // If no projects meet the condition, return an appropriate response
    if (empty($filteredProjectIds)) {
        return response()->json(['message' => 'No projects with non-zero values for the selected month and year'], 200);
    }

    // Get the count and total project value for the filtered projects
    $projectCount = count($filteredProjectIds);
    $totalProjectValue = Header::whereIn('id', $filteredProjectIds)->sum('projectValue');

    // Actual staff value for the previous month (filtered by active projects)
    $totalActualStaffValue = ActualHr::whereIn('projectId', $filteredProjectIds)->sum($actualMonthColumn);

    // Actual collection value for the previous month (filtered by active projects)
    $totalActualCollectionValue = CashInMonthlyBase::whereIn('projectId', $filteredProjectIds)->sum($actualMonthColumn);

    // Expected collection value for the current month (filtered by active projects)
    $totalExpectedCollectionValue = ExpectedCollectionValue::whereIn('projectId', $filteredProjectIds)->sum($expectedMonthColumn);

    // Actual invoice value for the previous month (filtered by active projects)
    $totalActualInvoiceValue = ActualValueMonthlyBase::whereIn('projectId', $filteredProjectIds)->sum($actualMonthColumn);

   
// Expected invoice value for the current month (filtered by active projects)
    $totalExpectedInvoiceValue = PmFcInvoiceCurrentMonth::whereIn('projectId', $filteredProjectIds)->sum($expectedMonthColumn);

    // Cash-out actual value (salary, petty cash, suppliers) for the previous month (filtered by active projects)
    $totalActualCashOut = SalaryMonthlyBase::whereIn('projectId', $filteredProjectIds)->sum($actualMonthColumn) +
        PettyCashMonthlyBase::whereIn('projectId', $filteredProjectIds)->sum($actualMonthColumn) +
        SuppliersMonthlyBase::whereIn('projectId', $filteredProjectIds)->sum($actualMonthColumn);

    // Cash-out expected value for the current month (filtered by active projects)
    $totalExpectedCashOut = ExpectedSalaryValue::whereIn('projectId', $filteredProjectIds)->sum($expectedMonthColumn) +
        ExpectedPettyCashValue::whereIn('projectId', $filteredProjectIds)->sum($expectedMonthColumn) +
        ExpectedSuppliersValue::whereIn('projectId', $filteredProjectIds)->sum($expectedMonthColumn);

    $totalCumulativeActualInvoice = 0;
    // Initialize an array for projects' key issues and images
    $projectsDetails = [];

    foreach ($filteredProjectIds as $projectId) {
        $project = $activeProjects->firstWhere('id', $projectId);

        // Fetch notes from KeyIssuesNotes related to the project
        $keyIssueNotes = KeyIssuesNotes::where('projectId', $projectId)->pluck('note')->toArray();

        // Fetch the image related to the project from the OperationProjectImage table
        $operationImage = OperationProjectImage::where('project_id', $projectId)->value('image_path');

        if ($operationImage) {
            $fullImagePath = 'https://app.morgantigcc.com/morganti_dashboard/dashboardbackend/public/' . $operationImage;
        } else {
            $fullImagePath = null;
        }

         // Calculate the cumulative actual invoice for each project up to the previous month
         $cumulativeInvoice = $this->calculateCumulativeActualInvoice($projectId);
         $totalCumulativeActualInvoice += $cumulativeInvoice;

        // Add to project details array
        $projectsDetails[] = [
            'projectId' => $projectId,
            'projectName' => $project->projectName,
            'keyIssueNotes' => $keyIssueNotes,
            'operationImage' => $fullImagePath,
            'cumulativeActualInvoice' => $cumulativeInvoice,
        ];
    }

    // Prepare the data to return
    $monthlyData = [
        'month' => ucfirst($requestedMonth),
        'year' => $requestedYear,
        'totalProjects' => $projectCount,
        'totalProjectValue' => $totalProjectValue,
        'totalActualStaffValue' => $totalActualStaffValue,
        'totalActualCollectionValue' => $totalActualCollectionValue,
        'totalExpectedCollectionValue' => $totalExpectedCollectionValue,
        'totalActualInvoiceValue' => $totalActualInvoiceValue,
        'totalExpectedInvoiceValue' => $totalExpectedInvoiceValue,
        'totalActualCashOut' => $totalActualCashOut,
        'totalExpectedCashOut' => $totalExpectedCashOut,
        'totalCumulativeActualInvoice' => $totalCumulativeActualInvoice,
        'projectsDetails' => $projectsDetails, // Include project-specific data
    ];

   

    // Return the data
    return response()->json($monthlyData);
}

public function countSubmittedTenders()
{
    // Define date range for the previous month
    $previousMonthStart = Carbon::now()->subMonth()->startOfMonth();
    $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();

     // Define the valid selectedOption values
     $validOptions = ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'];

     // Get tenders for cumulative 'Submitted To Client' and 'No Response' counts
     $allTenders = Tender::where('created_at', '<=', Carbon::now()->subMonth()->endOfMonth())
         ->whereIn('selectedOption', $validOptions) // Filter by valid options
         ->get();
    // For 'Submitted To Client' status (cumulative up to last month)
    $submittedTenders = $allTenders->where('status', 'Submitted To Client')
        ->where('completed', true)
        ->where('no_response', false)
        ->where('lost', false);
        
    $countSubmitted = $submittedTenders->count();
    $totalSubmittedValue = $submittedTenders->sum('tender_value');
    $submittedTitles = $submittedTenders->pluck('tenderTitle');

    // For 'No Response' status (cumulative up to last month)
    $noResponseTenders = $allTenders->where('no_response', true)
        ->where('status', 'no response')
        ->where('no_response', true);
    $countNoResponse = $noResponseTenders->count();
    $totalNoResponseValue = $noResponseTenders->sum('tender_value');
    $noResponseTitles = $noResponseTenders->pluck('tenderTitle');

    // For 'Awarded' status (only for the previous month)
    $awardedTenders = Tender::where('status', 'Awarded')
        ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
        ->whereIn('selectedOption', $validOptions)
        ->get();
    $countAwarded = $awardedTenders->count();
    $totalAwardedValue = $awardedTenders->sum('tender_value');
    $awardedTitles = $awardedTenders->pluck('tenderTitle');

    // For 'Likely Awarded Tenders' (cumulative up to last month with probability >= 70)
    $likelyAwardedTenders = $allTenders->where('probability', '>=', 70);
    $countLikelyAwardedTenders = $likelyAwardedTenders->count();
    $totalLikelyAwardedTenderValue = $likelyAwardedTenders->sum('tender_value');
    $likelyAwardedTenderTitles = $likelyAwardedTenders->pluck('tenderTitle');

    // Return the counts and related data as a JSON response
    return response()->json([
        'submitted_count' => $countSubmitted,
        'submitted_total_value' => $totalSubmittedValue,
        'submitted_titles' => $submittedTitles->toArray(), // Send as array
        'awarded_count' => $countAwarded,
        'awarded_total_value' => $totalAwardedValue,
        'awarded_titles' => $awardedTitles->toArray(), // Send as array
        'no_response_count' => $countNoResponse,
        'no_response_total_value' => $totalNoResponseValue,
        'no_response_titles' => $noResponseTitles->toArray(), // Send as array
        'likely_awards_count' => $countLikelyAwardedTenders,
        'likely_awards_total_value' => $totalLikelyAwardedTenderValue,
        'likely_awards_titles' => $likelyAwardedTenderTitles->toArray(), // Send as array
    ], 200, ['Content-Type' => 'application/json; charset=UTF-8']);
}

    
private function calculateCumulativeActualInvoice($projectId)
{
    $currentMonth = now()->subMonth()->format('m'); // Get the previous month number
    $currentYear = now()->year;

    // Fetch all monthly reports up to the current year for the project
    $monthlyReports = ActualValueMonthlyBase::where('projectId', $projectId)
                        ->where('year', '<=', $currentYear)
                        ->get();

    $cumulativeActualInvoice = 0;

    // Loop through the reports and calculate the cumulative invoice value
    foreach ($monthlyReports as $report) {
        foreach (['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'] as $index => $month) {
            // Sum values up to the previous month
            if ($report->year < $currentYear || ($report->year == $currentYear && $index < $currentMonth)) {
                $cumulativeActualInvoice += $report->{$month};
            }
        }
    }

    return $cumulativeActualInvoice;
}

}
