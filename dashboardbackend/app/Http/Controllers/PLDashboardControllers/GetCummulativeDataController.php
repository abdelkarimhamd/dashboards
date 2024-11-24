<?php

namespace App\Http\Controllers\PLDashboardControllers;

use App\Http\Controllers\Controller;
use App\Models\ActualInvoice;
use App\Models\ActualStaff;
use App\Models\Cashin;
use App\Models\Cashout;
use App\Models\CertifiedInvoice;
use App\Models\Cumulativeacinvoice;
use App\Models\CumulativeActualStaff;
use App\Models\CumulativeCashin;
use App\Models\CumulativeCashout;
use App\Models\Cumulativefcinvoice;
use App\Models\CumulativeHoCostOverHd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\CumulativePlanStaff;
use App\Models\FcInvoice;
use App\Models\Hocostoverhd;
use App\Models\PlanStaff;
use App\Models\CumulativeCertifiedInvoice;
class GetCummulativeDataController extends Controller
{
    public function handlePlanStaffCumulativeData($year)
    {
        $user = auth()->user();
        $userBranch = $user->branch;
    Log:info($year);
        if (!$userBranch) {
            return response()->json(['error' => 'User branch not found'], 400);
        }

        if (!preg_match('/^\d{4}$/', $year)) {
            return response()->json(['error' => 'Invalid year format. Year must be a four-digit number.'], 400);
        }
    
        $sumData = PlanStaff::where('Year', $year)
                        ->where('branch', $userBranch)
                         ->selectRaw('SUM(M01) AS M01, SUM(M02) AS M02, SUM(M03) AS M03, SUM(M04) AS M04, SUM(M05) AS M05, SUM(M06) AS M06, SUM(M07) AS M07, SUM(M08) AS M08, SUM(M09) AS M09, SUM(M10) AS M10, SUM(M11) AS M11, SUM(M12) AS M12')
                         ->first();
    
        if (!$sumData->M01 && !$sumData->M02) { // Checking if there's any data at all
            return response()->json(['info' => 'No cash-in data found for the selected year to compute sums.'], 404);
        }
    
        $attributes = [];
        foreach (['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'] as $month) {
            $attributes[$month] = $sumData->{$month} ?? 0;
        }
    
        $cumulative = CumulativePlanStaff::updateOrCreate(
            ['yearSelected' => $year,'branch' => $userBranch],
            $attributes
        );
    
        return response()->json($cumulative->only(['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12']));
    }

    public function handleActualStaffCumulativeData($year)
    {

        $user = auth()->user();
        $userBranch = $user->branch;

        if (!$userBranch) {
            return response()->json(['error' => 'User branch not found'], 400);
        }

        if (!preg_match('/^\d{4}$/', $year)) {
            return response()->json(['error' => 'Invalid year format. Year must be a four-digit number.'], 400);
        }
    
        $sumData = ActualStaff::where('Year', $year)
                        ->where('branch', $userBranch)
                         ->selectRaw('SUM(M01) AS M01, SUM(M02) AS M02, SUM(M03) AS M03, SUM(M04) AS M04, SUM(M05) AS M05, SUM(M06) AS M06, SUM(M07) AS M07, SUM(M08) AS M08, SUM(M09) AS M09, SUM(M10) AS M10, SUM(M11) AS M11, SUM(M12) AS M12')
                         ->first();
    
        if (!$sumData->M01 && !$sumData->M02) { // Checking if there's any data at all
            return response()->json(['info' => 'No cash-in data found for the selected year to compute sums.'], 404);
        }
    
        $attributes = [];
        foreach (['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'] as $month) {
            $attributes[$month] = $sumData->{$month} ?? 0;
        }
    
        $cumulative = CumulativeActualStaff::updateOrCreate(
            ['yearSelected' => $year,'branch' => $userBranch],
            $attributes
        );
    
        return response()->json($cumulative->only(['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12']));
    }
    public function handleActualInvoiceCumulativeData($year)
    {
        $user = auth()->user();
        $userBranch = $user->branch;

        if (!$userBranch) {
            return response()->json(['error' => 'User branch not found'], 400);
        }

        if (!preg_match('/^\d{4}$/', $year)) {
            return response()->json(['error' => 'Invalid year format. Year must be a four-digit number.'], 400);
        }
    
        $sumData = ActualInvoice::where('Year', $year)
                        ->where('branch', $userBranch)
                         ->selectRaw('SUM(M01) AS M01, SUM(M02) AS M02, SUM(M03) AS M03, SUM(M04) AS M04, SUM(M05) AS M05, SUM(M06) AS M06, SUM(M07) AS M07, SUM(M08) AS M08, SUM(M09) AS M09, SUM(M10) AS M10, SUM(M11) AS M11, SUM(M12) AS M12')
                         ->first();
    
        if (!$sumData->M01 && !$sumData->M02) { // Checking if there's any data at all
            return response()->json(['info' => 'No cash-in data found for the selected year to compute sums.'], 404);
        }
    
        $attributes = [];
        foreach (['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'] as $month) {
            $attributes[$month] = $sumData->{$month} ?? 0;
        }
    
        $cumulative = Cumulativeacinvoice::updateOrCreate(
            ['yearSelected' => $year,'branch' => $userBranch],
            $attributes
        );
    
        return response()->json($cumulative->only(['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12']));
    }
    public function handleFcInvoiceCumulativeData($year)

    {
        $user = auth()->user();
        $userBranch = $user->branch;

        if (!$userBranch) {
            return response()->json(['error' => 'User branch not found'], 400);
        }


        if (!preg_match('/^\d{4}$/', $year)) {
            return response()->json(['error' => 'Invalid year format. Year must be a four-digit number.'], 400);
        }
    
        $sumData = FcInvoice::where('Year', $year)
        ->where('branch', $userBranch)
                         ->selectRaw('SUM(M01) AS M01, SUM(M02) AS M02, SUM(M03) AS M03, SUM(M04) AS M04, SUM(M05) AS M05, SUM(M06) AS M06, SUM(M07) AS M07, SUM(M08) AS M08, SUM(M09) AS M09, SUM(M10) AS M10, SUM(M11) AS M11, SUM(M12) AS M12')
                         ->first();
    
        if (!$sumData->M01 && !$sumData->M02) { // Checking if there's any data at all
            return response()->json(['info' => 'No cash-in data found for the selected year to compute sums.'], 404);
        }
    
        $attributes = [];
        foreach (['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'] as $month) {
            $attributes[$month] = $sumData->{$month} ?? 0;
        }
    
        $cumulative = Cumulativefcinvoice::updateOrCreate(
            ['yearSelected' => $year,'branch' => $userBranch],
            $attributes
        );
    
        return response()->json($cumulative->only(['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12']));
    }

    public function handleCashInCumulativeData($year)
    {
        $user = auth()->user();
        $userBranch = $user->branch;

        if (!$userBranch) {
            return response()->json(['error' => 'User branch not found'], 400);
        }


        if (!preg_match('/^\d{4}$/', $year)) {
            return response()->json(['error' => 'Invalid year format. Year must be a four-digit number.'], 400);
        }
    
        $sumData = Cashin::where('Year', $year)
        ->where('branch', $userBranch)
                         ->selectRaw('SUM(M01) AS M01, SUM(M02) AS M02, SUM(M03) AS M03, SUM(M04) AS M04, SUM(M05) AS M05, SUM(M06) AS M06, SUM(M07) AS M07, SUM(M08) AS M08, SUM(M09) AS M09, SUM(M10) AS M10, SUM(M11) AS M11, SUM(M12) AS M12')
                         ->first();
    
        if (!$sumData->M01 && !$sumData->M02) { // Checking if there's any data at all
            return response()->json(['info' => 'No cash-in data found for the selected year to compute sums.'], 404);
        }
    
        $attributes = [];
        foreach (['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'] as $month) {
            $attributes[$month] = $sumData->{$month} ?? 0;
        }
    
        $cumulative = CumulativeCashin::updateOrCreate(
            ['yearSelected' => $year,'branch' => $userBranch],
            $attributes
        );
    
        return response()->json($cumulative->only(['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12']));
    }

    public function handleCashOutCumulativeData($year)
    {
        $user = auth()->user();
        $userBranch = $user->branch;

        if (!$userBranch) {
            return response()->json(['error' => 'User branch not found'], 400);
        }
        if (!preg_match('/^\d{4}$/', $year)) {
            return response()->json(['error' => 'Invalid year format. Year must be a four-digit number.'], 400);
        }
    
        $sumData = Cashout::where('Year', $year)
        ->where('branch', $userBranch)
                         ->selectRaw('SUM(M01) AS M01, SUM(M02) AS M02, SUM(M03) AS M03, SUM(M04) AS M04, SUM(M05) AS M05, SUM(M06) AS M06, SUM(M07) AS M07, SUM(M08) AS M08, SUM(M09) AS M09, SUM(M10) AS M10, SUM(M11) AS M11, SUM(M12) AS M12')
                         ->first();
    
        if (!$sumData->M01 && !$sumData->M02) { // Checking if there's any data at all
            return response()->json(['info' => 'No cash-in data found for the selected year to compute sums.'], 404);
        }
    
        $attributes = [];
        foreach (['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'] as $month) {
            $attributes[$month] = $sumData->{$month} ?? 0;
        }
    
        $cumulative = CumulativeCashout::updateOrCreate(
            ['yearSelected' => $year,'branch' => $userBranch],
            $attributes
        );
    
        return response()->json($cumulative->only(['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12']));
        
    }

    public function handleHoCostOverHdCumulativeData($year)
    {
        $user = auth()->user();
        $userBranch = $user->branch;

        if (!$userBranch) {
            return response()->json(['error' => 'User branch not found'], 400);
        }
        if (!preg_match('/^\d{4}$/', $year)) {
            return response()->json(['error' => 'Invalid year format. Year must be a four-digit number.'], 400);
        }
    
        $sumData = Hocostoverhd::where('Year', $year)
        ->where('branch', $userBranch)
                         ->selectRaw('SUM(M01) AS M01, SUM(M02) AS M02, SUM(M03) AS M03, SUM(M04) AS M04, SUM(M05) AS M05, SUM(M06) AS M06, SUM(M07) AS M07, SUM(M08) AS M08, SUM(M09) AS M09, SUM(M10) AS M10, SUM(M11) AS M11, SUM(M12) AS M12')
                         ->first();
    
        if (!$sumData->M01 && !$sumData->M02) { // Checking if there's any data at all
            return response()->json(['info' => 'No cash-in data found for the selected year to compute sums.'], 404);
        }
    
        $attributes = [];
        foreach (['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'] as $month) {
            $attributes[$month] = $sumData->{$month} ?? 0;
        }
    
        $cumulative = CumulativeHoCostOverHd::updateOrCreate(
            ['yearSelected' => $year,'branch' => $userBranch],
            $attributes
        );
    
        return response()->json($cumulative->only(['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12']));
      
    }

    public function handleCertifiedInvoiceCumulativeData($year)
    {
        $user = auth()->user();
        $userBranch = $user->branch;

        if (!$userBranch) {
            return response()->json(['error' => 'User branch not found'], 400);
        }
        if (!preg_match('/^\d{4}$/', $year)) {
            return response()->json(['error' => 'Invalid year format. Year must be a four-digit number.'], 400);
        }
    
        $sumData = CertifiedInvoice::where('Year', $year)
        ->where('branch', $userBranch)
                         ->selectRaw('SUM(M01) AS M01, SUM(M02) AS M02, SUM(M03) AS M03, SUM(M04) AS M04, SUM(M05) AS M05, SUM(M06) AS M06, SUM(M07) AS M07, SUM(M08) AS M08, SUM(M09) AS M09, SUM(M10) AS M10, SUM(M11) AS M11, SUM(M12) AS M12')
                         ->first();
    
        if (!$sumData->M01 && !$sumData->M02) { // Checking if there's any data at all
            return response()->json(['info' => 'No cash-in data found for the selected year to compute sums.'], 404);
        }
    
        $attributes = [];
        foreach (['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'] as $month) {
            $attributes[$month] = $sumData->{$month} ?? 0;
        }
    
        $cumulative = CumulativeCertifiedInvoice::updateOrCreate(
            ['yearSelected' => $year,'branch' => $userBranch],
            $attributes
        );
    
        return response()->json($cumulative->only(['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12']));
      
    }
    public function fetchGraphData(Request $request)
    {
        
        $validated = $request->validate([
            'projectID' => 'required|numeric',
        ]);

        $projectID = $request->input('projectID');

        $fcInvoiceData = DB::table('fcinvoice')
            ->where('ProjectID', $projectID)
            ->select('M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12')
            ->first();

        $actualInvoiceData = DB::table('actualinvoice')
            ->where('ProjectID', $projectID)
            ->select('M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12')
            ->first();

        $actualCashinData = DB::table('cashin')
            ->where('ProjectID', $projectID)
            ->select('M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12')
            ->first();

        $graphData = [
            'labels' => ['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'],
            'fcInvoice' => $fcInvoiceData,
            'actualInvoice' => $actualInvoiceData,
            'actualCashin' => $actualCashinData,
           
        ];

        return response()->json($graphData);
    }
}
