<?php

namespace App\Http\Controllers\PLDashboardControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
Use App\Models\ActualInvoice;
Use App\Models\Cashin;
Use App\Models\Cashout;
use App\Models\Cumulativeacinvoice;
use App\Models\CumulativeActualStaff;
use App\Models\CumulativeCashin;
use App\Models\CumulativeCashout;
use App\Models\Cumulativefcinvoice;
use App\Models\CumulativeHoCostOverHd;
use App\Models\CumulativePlanStaff;
Use App\Models\FcInvoice;


class GetProjectMonthlyData extends Controller
{
    public function getFcInvoiceMonthlyValues($projectID, $year)
{
    
        // Get the authenticated user and their branch
        $user = auth()->user();
        $userBranch = $user->branch;

        // Check if the branch is available
        if (!$userBranch) {
            return response()->json(['error' => 'User branch not found'], 400);
        }

    $months = ['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'];
    $monthlyData = [];

    foreach ($months as $month) {
        $monthlyData[$month] = FcInvoice::where('ProjectID', $projectID)->where('Year', $year)->where('branch', $userBranch)->sum($month);
    }

    return response()->json([
        'MonthlyData' => $monthlyData
    ], 200);
}

public function getAcInvoiceMonthlyValues($projectID, $year)
{
            // Get the authenticated user and their branch
            $user = auth()->user();
            $userBranch = $user->branch;
    
            // Check if the branch is available
            if (!$userBranch) {
                return response()->json(['error' => 'User branch not found'], 400);
            }
    $months = ['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'];
    $monthlyData = [];

    foreach ($months as $month) {
        $monthlyData[$month] = ActualInvoice::where('ProjectID', $projectID)->where('Year', $year)->where('branch', $userBranch)->sum($month);
    }

    return response()->json([
        'MonthlyData' => $monthlyData
    ], 200);
}

public function getCashinMonthlyValues($projectID, $year)
{
            // Get the authenticated user and their branch
            $user = auth()->user();
            $userBranch = $user->branch;
    
            // Check if the branch is available
            if (!$userBranch) {
                return response()->json(['error' => 'User branch not found'], 400);
            }
    $months = ['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'];
    $monthlyData = [];

    foreach ($months as $month) {
        $monthlyData[$month] = Cashin::where('ProjectID', $projectID)->where('Year', $year)->where('branch', $userBranch)->sum($month);
    }

    return response()->json([
        'MonthlyData' => $monthlyData
    ], 200);
}
public function getCashoutMonthlyValues($projectID, $year)
{
            // Get the authenticated user and their branch
            $user = auth()->user();
            $userBranch = $user->branch;
    
            // Check if the branch is available
            if (!$userBranch) {
                return response()->json(['error' => 'User branch not found'], 400);
            }
    $months = ['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'];
    $monthlyData = [];

    foreach ($months as $month) {
        $monthlyData[$month] = Cashout::where('ProjectID', $projectID)->where('Year', $year)->where('branch', $userBranch)->sum($month);
    }

    return response()->json([
        'MonthlyData' => $monthlyData
    ], 200);
}

// get cumulative data
public function getFcInvoiceCumulativeValues($year)
{
            // Get the authenticated user and their branch
            $user = auth()->user();
            $userBranch = $user->branch;
    
            // Check if the branch is available
            if (!$userBranch) {
                return response()->json(['error' => 'User branch not found'], 400);
            }
    $months = ['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'];
    $monthlyData = [];

    foreach ($months as $month) {
        $monthlyData[$month] = Cumulativefcinvoice::where('yearSelected', $year)->where('branch', $userBranch)->sum($month);
    }

    return response()->json([
        'MonthlyData' => $monthlyData
    ], 200);
}

public function getAcInvoiceCumulativeValues($year)
{
            // Get the authenticated user and their branch
            $user = auth()->user();
            $userBranch = $user->branch;
    
            // Check if the branch is available
            if (!$userBranch) {
                return response()->json(['error' => 'User branch not found'], 400);
            }
    $months = ['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'];
    $monthlyData = [];

    foreach ($months as $month) {
        $monthlyData[$month] = Cumulativeacinvoice::where('yearSelected', $year)->where('branch', $userBranch)->sum($month);
    }

    return response()->json([
        'MonthlyData' => $monthlyData
    ], 200);
}

public function getCashinCumulativeValues( $year)
{
            // Get the authenticated user and their branch
            $user = auth()->user();
            $userBranch = $user->branch;
    
            // Check if the branch is available
            if (!$userBranch) {
                return response()->json(['error' => 'User branch not found'], 400);
            }
    $months = ['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'];
    $monthlyData = [];

    foreach ($months as $month) {
        $monthlyData[$month] = CumulativeCashin::where('yearSelected', $year)->where('branch', $userBranch)->sum($month);
    }

    return response()->json([
        'MonthlyData' => $monthlyData
    ], 200);
}
public function getCashoutCumulativeValues($year)
{
            // Get the authenticated user and their branch
            $user = auth()->user();
            $userBranch = $user->branch;
    
            // Check if the branch is available
            if (!$userBranch) {
                return response()->json(['error' => 'User branch not found'], 400);
            }
    $months = ['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'];
    $monthlyData = [];

    foreach ($months as $month) {
        $monthlyData[$month] = CumulativeCashout::where('yearSelected', $year)->where('branch', $userBranch)->sum($month);
    }

    return response()->json([
        'MonthlyData' => $monthlyData
    ], 200);
}
public function getHoCostOverHdCumulativeValues($year)
{
            // Get the authenticated user and their branch
            $user = auth()->user();
            $userBranch = $user->branch;
    
            // Check if the branch is available
            if (!$userBranch) {
                return response()->json(['error' => 'User branch not found'], 400);
            }
    $months = ['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'];
    $monthlyData = [];

    foreach ($months as $month) {
        $monthlyData[$month] = CumulativeHoCostOverHd::where('yearSelected', $year)->where('branch', $userBranch)->sum($month);
    }

    return response()->json([
        'MonthlyData' => $monthlyData
    ], 200);
}
public function getActualStaffCumulativeValues($year)
{
            // Get the authenticated user and their branch
            $user = auth()->user();
            $userBranch = $user->branch;
    
            // Check if the branch is available
            if (!$userBranch) {
                return response()->json(['error' => 'User branch not found'], 400);
            }
    $months = ['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'];
    $monthlyData = [];

    foreach ($months as $month) {
        $monthlyData[$month] = CumulativeActualStaff::where('yearSelected', $year)->where('branch', $userBranch)->sum($month);
    }

    return response()->json([
        'MonthlyData' => $monthlyData
    ], 200);
}
public function getPlanStaffCumulativeValues($year)
{
            // Get the authenticated user and their branch
            $user = auth()->user();
            $userBranch = $user->branch;
    
            // Check if the branch is available
            if (!$userBranch) {
                return response()->json(['error' => 'User branch not found'], 400);
            }
    $months = ['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'];
    $monthlyData = [];

    foreach ($months as $month) {
        $monthlyData[$month] = CumulativePlanStaff::where('yearSelected', $year)->where('branch', $userBranch)->sum($month);
    }

    return response()->json([
        'MonthlyData' => $monthlyData
    ], 200);
}
}
