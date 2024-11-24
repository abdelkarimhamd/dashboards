<?php

namespace App\Http\Controllers\PLDashboardControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FcInvoice;
use App\Models\ActualInvoice;
use App\Models\ActualStaff;
use App\Models\PlanStaff;
use App\Models\Hocostoverhd;
use App\Models\Cashin;
use App\Models\Cashout;

use App\Models\ActualValueMonthlyBase;
use App\Models\CashInMonthlyBase;
use App\Models\ActualHr;
use App\Models\CertifiedInvoice;
use App\Models\ManpowerValueMonthlyBase;
use App\Models\SalaryMonthlyBase;
use App\Models\PettyCashMonthlyBase;
use App\Models\SuppliersMonthlyBase;
use Illuminate\Support\Facades\Log;
class InsertProjectDataController extends Controller{
public function storeAcInvoiceMonthlyValues(Request $request)
{ 
     
    $validatedData = $request->validate([
        'ProjectID' => 'nullable|integer|exists:project_details,ProjectID',
        'Year' => 'required|integer',
        'branch' => 'required|string|max:255',
        'M01' => 'required|numeric',
        'M02' => 'required|numeric',
        'M03' => 'required|numeric',
        'M04' => 'required|numeric',
        'M05' => 'required|numeric',
        'M06' => 'required|numeric',
        'M07' => 'required|numeric',
        'M08' => 'required|numeric',
        'M09' => 'required|numeric',
        'M10' => 'required|numeric',
        'M11' => 'required|numeric',
        'M12' => 'required|numeric',
    ]);

    $total = $validatedData['M01'] + $validatedData['M02'] + $validatedData['M03'] +
    $validatedData['M04'] + $validatedData['M05'] + $validatedData['M06'] +
    $validatedData['M07'] + $validatedData['M08'] + $validatedData['M09'] +
    $validatedData['M10'] + $validatedData['M11'] + $validatedData['M12'];


    $findAttributes = [
        'ProjectID' => $validatedData['ProjectID'],
        'Year' => $validatedData['Year'],
        'branch' => $validatedData['branch']
    ];

    $createAttributes = $validatedData + [
        'Total' => $total,
        'VarianceYTD' => 0,
        'Performance' => 0
    ];

    $invoice = ActualInvoice::updateOrCreate($findAttributes, $createAttributes);
   

    return response()->json($invoice, 201);
}

public function updateActualFcInvoiceVarianceAndPerformance(Request $request)
{
    $validatedData = $request->validate([
        'ProjectID' => 'required|integer|exists:project_details,ProjectID',
        'VarianceYTD' => 'required|numeric',
        'Performance' => 'required|numeric'
    ]);

    $invoice = ActualInvoice::where([
        'ProjectID' => $validatedData['ProjectID'],
    ])->first();

    if (!$invoice) {
        return response()->json(['message' => 'Actual invoice not found'], 404);
    }

    $invoice->update([
        'VarianceYTD' => $validatedData['VarianceYTD'],
        'Performance' => $validatedData['Performance']
    ]);

    return response()->json([
        'message' => 'Actual invoice updated successfully',
        'data' => $invoice
    ], 200);
}


public function storeFcInvoiceMonthlyValues(Request $request)
{
    $validatedData = $request->validate([
        'ProjectID' => 'nullable|integer|exists:project_details,ProjectID',
        'Year' => 'required|integer',
        'branch' => 'required|string|max:255',
        'M01' => 'required|numeric',
        'M02' => 'required|numeric',
        'M03' => 'required|numeric',
        'M04' => 'required|numeric',
        'M05' => 'required|numeric',
        'M06' => 'required|numeric',
        'M07' => 'required|numeric',
        'M08' => 'required|numeric',
        'M09' => 'required|numeric',
        'M10' => 'required|numeric',
        'M11' => 'required|numeric',
        'M12' => 'required|numeric',
    ]);

    $total = $validatedData['M01'] + $validatedData['M02'] + $validatedData['M03'] +
         $validatedData['M04'] + $validatedData['M05'] + $validatedData['M06'] +
         $validatedData['M07'] + $validatedData['M08'] + $validatedData['M09'] +
         $validatedData['M10'] + $validatedData['M11'] + $validatedData['M12']; 

    $findAttributes = [
        'ProjectID' => $validatedData['ProjectID'],
        'Year' => $validatedData['Year'],
        'branch' => $validatedData['branch']
    ];

    $createAttributes = $validatedData + [
        'Total' => $total,
        'VarianceYTD' => 0,
        'Performance' => 0  
    ];

    $invoice = FcInvoice::updateOrCreate($findAttributes, $createAttributes);

    return response()->json($invoice, 201);
}
    public function storeActualStaffMonthlyValues(Request $request)
    {
        $validatedData = $request->validate([
            'ProjectID' => 'nullable|integer|exists:project_details,ProjectID',
            'Year' => 'required|integer',
            'branch' => 'required|string|max:255',
            'M01' => 'required|numeric',
            'M02' => 'required|numeric',
            'M03' => 'required|numeric',
            'M04' => 'required|numeric',
            'M05' => 'required|numeric',
            'M06' => 'required|numeric',
            'M07' => 'required|numeric',
            'M08' => 'required|numeric',
            'M09' => 'required|numeric',
            'M10' => 'required|numeric',
            'M11' => 'required|numeric',
            'M12' => 'required|numeric',
        ]);
    
        $total = $validatedData['M01'] + $validatedData['M02'] + $validatedData['M03'] +
         $validatedData['M04'] + $validatedData['M05'] + $validatedData['M06'] +
         $validatedData['M07'] + $validatedData['M08'] + $validatedData['M09'] +
         $validatedData['M10'] + $validatedData['M11'] + $validatedData['M12'];

        // $totalPlanStaff = PlanStaff::where('ProjectID', $validatedData['ProjectID'])
        //                             ->where('Year', $validatedData['Year'])
        //                             ->sum('Total');
    
        // $variance = $total - $totalPlanStaff;
        // $performance = ($totalPlanStaff == 0) ? 0 : ($total / $totalPlanStaff) * 100; 
        $findAttributes = [
            'ProjectID' => $validatedData['ProjectID'],
            'Year' => $validatedData['Year'],
            'branch' => $validatedData['branch']
        ];
    
        $createAttributes = $validatedData + [
            'Total' => $total,
            'VarianceYTD' => 0,
            'Performance' => 0
        ];
    
        
        $invoice = ActualStaff::updateOrCreate($findAttributes, $createAttributes);
    
        return response()->json($invoice, 201);
    }
    

    public function storePlanStaffMonthlyValues(Request $request)
{
    $validatedData = $request->validate([
        'ProjectID' => 'nullable|integer|exists:project_details,ProjectID',
        'Year' => 'required|integer',
        'branch' => 'required|string|max:255',
        'M01' => 'required|numeric',
        'M02' => 'required|numeric',
        'M03' => 'required|numeric',
        'M04' => 'required|numeric',
        'M05' => 'required|numeric',
        'M06' => 'required|numeric',
        'M07' => 'required|numeric',
        'M08' => 'required|numeric',
        'M09' => 'required|numeric',
        'M10' => 'required|numeric',
        'M11' => 'required|numeric',
        'M12' => 'required|numeric',
    ]);

    $total = $validatedData['M01'] + $validatedData['M02'] + $validatedData['M03'] +
         $validatedData['M04'] + $validatedData['M05'] + $validatedData['M06'] +
         $validatedData['M07'] + $validatedData['M08'] + $validatedData['M09'] +
         $validatedData['M10'] + $validatedData['M11'] + $validatedData['M12'];


    // $totalActualStaff = ActualStaff::where('ProjectID', $validatedData['ProjectID'])
    //                                 ->where('Year', $validatedData['Year'])
    //                                 ->sum('Total');

    // $variance = $totalActualStaff - $total;
    // $performance = ($total == 0) ? 0 : ($totalActualStaff / $total) * 100;

    $findAttributes = [
        'ProjectID' => $validatedData['ProjectID'],
        'Year' => $validatedData['Year'],
        'branch' => $validatedData['branch']
    ];


    $createAttributes = $validatedData + [
        'Total' => $total,
        'VarianceYTD' => 0,
        'Performance' => 0
    ];

    $invoice = PlanStaff::updateOrCreate($findAttributes, $createAttributes);

    return response()->json($invoice, 201);
}

public function updateActualPlanStaffVarianceAndPerformance(Request $request)
{
    $validatedData = $request->validate([
        'ProjectID' => 'required|integer|exists:project_details,ProjectID',
        'VarianceYTD' => 'required|numeric',
        'Performance' => 'required|numeric'
    ]);

    $acStaff = ActualStaff::where([
        'ProjectID' => $validatedData['ProjectID'],
    ])->first();

    if (!$acStaff) {
        return response()->json(['message' => 'Actual invoice not found'], 404);
    }

    $acStaff->update([
        'VarianceYTD' => $validatedData['VarianceYTD'],
        'Performance' => $validatedData['Performance']
    ]);

    return response()->json([
        'message' => 'Actual Staff updated successfully',
        'data' => $acStaff
    ], 200);
}

public function updatePlanActualStaffVarianceAndPerformance(Request $request)
{
    $validatedData = $request->validate([
        'ProjectID' => 'required|integer|exists:project_details,ProjectID',
        'VarianceYTD' => 'required|numeric',
        'Performance' => 'required|numeric'
    ]);

    $plStaff = PlanStaff::where([
        'ProjectID' => $validatedData['ProjectID'],
    ])->first();

    if (!$plStaff) {
        return response()->json(['message' => 'Actual invoice not found'], 404);
    }

    $plStaff->update([
        'VarianceYTD' => $validatedData['VarianceYTD'],
        'Performance' => $validatedData['Performance']
    ]);

    return response()->json([
        'message' => 'Actual Staff updated successfully',
        'data' => $plStaff
    ], 200);
}

public function storeCertifiedMonthlyValues(Request $request)
{
    $validatedData = $request->validate([
        'ProjectID' => 'nullable|integer|exists:project_details,ProjectID',
        'Year' => 'required|integer',
        'branch' => 'required|string|max:255',
        'M01' => 'required|numeric',
        'M02' => 'required|numeric',
        'M03' => 'required|numeric',
        'M04' => 'required|numeric',
        'M05' => 'required|numeric',
        'M06' => 'required|numeric',
        'M07' => 'required|numeric',
        'M08' => 'required|numeric',
        'M09' => 'required|numeric',
        'M10' => 'required|numeric',
        'M11' => 'required|numeric',
        'M12' => 'required|numeric',
    ]);

    $total = $validatedData['M01'] + $validatedData['M02'] + $validatedData['M03'] +
         $validatedData['M04'] + $validatedData['M05'] + $validatedData['M06'] +
         $validatedData['M07'] + $validatedData['M08'] + $validatedData['M09'] +
         $validatedData['M10'] + $validatedData['M11'] + $validatedData['M12'];


    $findAttributes = [
        'ProjectID' => $validatedData['ProjectID'],
        'Year' => $validatedData['Year'],
        'branch' => $validatedData['branch']
    ];

    $createAttributes = $validatedData + [
        'Total' => $total,
        'VarianceYTD' => 0, 
        'Performance' => 0
    ];

  
    $invoice = CertifiedInvoice::updateOrCreate($findAttributes, $createAttributes);

    return response()->json($invoice, 201);
}
public function storeHoCostOverHdMonthlyValues(Request $request)
{
    $validatedData = $request->validate([
        'ProjectID' => 'nullable|integer|exists:project_details,ProjectID',
        'Year' => 'required|integer',
        'branch' => 'required|string|max:255',
        'M01' => 'required|numeric',
        'M02' => 'required|numeric',
        'M03' => 'required|numeric',
        'M04' => 'required|numeric',
        'M05' => 'required|numeric',
        'M06' => 'required|numeric',
        'M07' => 'required|numeric',
        'M08' => 'required|numeric',
        'M09' => 'required|numeric',
        'M10' => 'required|numeric',
        'M11' => 'required|numeric',
        'M12' => 'required|numeric',
    ]);

    $total = $validatedData['M01'] + $validatedData['M02'] + $validatedData['M03'] +
         $validatedData['M04'] + $validatedData['M05'] + $validatedData['M06'] +
         $validatedData['M07'] + $validatedData['M08'] + $validatedData['M09'] +
         $validatedData['M10'] + $validatedData['M11'] + $validatedData['M12'];


    $findAttributes = [
        'ProjectID' => $validatedData['ProjectID'],
        'Year' => $validatedData['Year'],
        'branch' => $validatedData['branch']
    ];

    $createAttributes = $validatedData + [
        'Total' => $total,
        'VarianceYTD' => 0, 
        'Performance' => 0
    ];

  
    $invoice = Hocostoverhd::updateOrCreate($findAttributes, $createAttributes);

    return response()->json($invoice, 201);
}

public function storeCashinMonthlyValues(Request $request)
{
    $validatedData = $request->validate([
        'ProjectID' => 'nullable|integer|exists:project_details,ProjectID',
        'Year' => 'required|integer',
        'branch' => 'required|string|max:255',
        'M01' => 'required|numeric',
        'M02' => 'required|numeric',
        'M03' => 'required|numeric',
        'M04' => 'required|numeric',
        'M05' => 'required|numeric',
        'M06' => 'required|numeric',
        'M07' => 'required|numeric',
        'M08' => 'required|numeric',
        'M09' => 'required|numeric',
        'M10' => 'required|numeric',
        'M11' => 'required|numeric',
        'M12' => 'required|numeric',
    ]);

    $total = $validatedData['M01'] + $validatedData['M02'] + $validatedData['M03'] +
         $validatedData['M04'] + $validatedData['M05'] + $validatedData['M06'] +
         $validatedData['M07'] + $validatedData['M08'] + $validatedData['M09'] +
         $validatedData['M10'] + $validatedData['M11'] + $validatedData['M12']; 

    // $actualInvoiceTotal = ActualInvoice::where('ProjectID', $validatedData['ProjectID'])
    //                                    ->where('Year', $validatedData['Year'])
    //                                    ->sum('Total');

    // $variance = $total - $actualInvoiceTotal;
    // $performance = ($actualInvoiceTotal == 0) ? 0 : ($total / $actualInvoiceTotal) * 100; 

    $findAttributes = [
        'ProjectID' => $validatedData['ProjectID'],
        'Year' => $validatedData['Year'],
        'branch' => $validatedData['branch']
    ];


    $createAttributes = $validatedData + [
        'Total' => $total,
        'VarianceYTD' => 0,
        'Performance' => 0
    ];

    
    $invoice = Cashin::updateOrCreate($findAttributes, $createAttributes);

    return response()->json($invoice, 201);
}

public function updateCashinfVarianceAndPerformance(Request $request)
{
    $validatedData = $request->validate([
        'ProjectID' => 'required|integer|exists:project_details,ProjectID',
        'VarianceYTD' => 'required|numeric',
        'Performance' => 'required|numeric'
    ]);

    $cashin = Cashin::where([
        'ProjectID' => $validatedData['ProjectID'],
    ])->first();

    if (!$cashin) {
        return response()->json(['message' => 'Cashin not found'], 404);
    }

    $cashin->update([
        'VarianceYTD' => $validatedData['VarianceYTD'],
        'Performance' => $validatedData['Performance']
    ]);

    return response()->json([
        'message' => 'Cashin updated successfully',
        'data' => $cashin
    ], 200);
}

public function storeCashoutMonthlyValues(Request $request)
{
    $validatedData = $request->validate([
        'ProjectID' => 'nullable|integer|exists:project_details,ProjectID',
        'Year' => 'required|integer',
        'branch' => 'required|string|max:255',
        'M01' => 'required|numeric',
        'M02' => 'required|numeric',
        'M03' => 'required|numeric',
        'M04' => 'required|numeric',
        'M05' => 'required|numeric',
        'M06' => 'required|numeric',
        'M07' => 'required|numeric',
        'M08' => 'required|numeric',
        'M09' => 'required|numeric',
        'M10' => 'required|numeric',
        'M11' => 'required|numeric',
        'M12' => 'required|numeric',
    ]);

    $total = $validatedData['M01'] + $validatedData['M02'] + $validatedData['M03'] +
         $validatedData['M04'] + $validatedData['M05'] + $validatedData['M06'] +
         $validatedData['M07'] + $validatedData['M08'] + $validatedData['M09'] +
         $validatedData['M10'] + $validatedData['M11'] + $validatedData['M12']; 

    // $actualInvoiceTotal = ActualInvoice::where('ProjectID', $validatedData['ProjectID'])
    //                                    ->where('Year', $validatedData['Year'])
    //                                    ->sum('Total');

    // $variance = $total - $actualInvoiceTotal;
    // $performance = ($actualInvoiceTotal == 0) ? 0 : ($total / $actualInvoiceTotal) * 100; 

    $findAttributes = [
        'ProjectID' => $validatedData['ProjectID'],
        'Year' => $validatedData['Year'],
        'branch' => $validatedData['branch']
    ];


    $createAttributes = $validatedData + [
        'Total' => $total,
        'VarianceYTD' => 0,
        'Performance' => 0
    ];

    
    $invoice = Cashout::updateOrCreate($findAttributes, $createAttributes);

    return response()->json($invoice, 201);
}

public function updateCashoutVarianceAndPerformance(Request $request)
{
    $validatedData = $request->validate([
        'ProjectID' => 'required|integer|exists:project_details,ProjectID',
        'VarianceYTD' => 'required|numeric',
        'Performance' => 'required|numeric'
    ]);

    $cashout = Cashout::where([
        'ProjectID' => $validatedData['ProjectID'],
    ])->first();

    if (!$cashout) {
        return response()->json(['message' => 'Cashout not found'], 404);
    }

    $cashout->update([
        'VarianceYTD' => $validatedData['VarianceYTD'],
        'Performance' => $validatedData['Performance']
    ]);

    return response()->json([
        'message' => 'Cashout updated successfully',
        'data' => $cashout
    ], 200);
}


public function getDataByProjectId(Request $request, $projectId, $year)
{
    // Validate ProjectID and year
    if (!$projectId || !$year) {
        return response()->json(['error' => 'ProjectID and Year are required'], 400);
    }

    // Define the columns to select
    //$monthColumns = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'december'];
    $monthColumnsnb = ['M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'M12'];

    // Query the data
    $data = [
        'FcInvoices' => FcInvoice::where('ProjectID', $projectId)->where('Year', $year)->select($monthColumnsnb)->get(),
        'ActualInvoices' => ActualInvoice::where('ProjectID', $projectId)->where('Year', $year)->select($monthColumnsnb)->get(),
        'ActualStaffs' => ActualStaff::where('ProjectID', $projectId)->where('Year', $year)->select($monthColumnsnb)->get(),
        'PlanStaffs' => PlanStaff::where('ProjectID', $projectId)->where('Year', $year)->select($monthColumnsnb)->get(),
        'HoCostOverhds' => Hocostoverhd::where('ProjectID', $projectId)->where('Year', $year)->select($monthColumnsnb)->get(),
        'certifiedInvoices' => CertifiedInvoice::where('ProjectID', $projectId)->where('Year', $year)->select($monthColumnsnb)->get(),
        'CashIns' => Cashin::where('ProjectID', $projectId)->where('Year', $year)->select($monthColumnsnb)->get(),
        'CashOuts' => Cashout::where('ProjectID', $projectId)->where('Year', $year)->select($monthColumnsnb)->get(),
    ];



    // Check if all data is empty
    if (empty($data['FcInvoices']) && empty($data['ActualInvoices']) && empty($data['ActualStaffs']) &&
        empty($data['PlanStaffs']) && empty($data['HoCostOverhds']) && empty($data['certifiedInvoices']) && empty($data['CashIns']) && empty($data['CashOuts'])) {
        return response()->json(['error' => 'No data found for the given ProjectID and Year'], 404);
    }

    return response()->json($data, 200);
}
private function calculateCashOuts($projectId, $year, $monthColumns)
{
    // Initialize an array to hold the monthly totals
    $cashOuts = [];

    foreach ($monthColumns as $month) {
        $suppliersSum = SuppliersMonthlyBase::where('projectId', $projectId)
                            ->where('year', $year)
                            ->sum($month);

        $pettyCashSum = PettyCashMonthlyBase::where('projectId', $projectId)
                            ->where('year', $year)
                            ->sum($month);

        $salarySum = SalaryMonthlyBase::where('projectId', $projectId)
                            ->where('year', $year)
                            ->sum($month);

        // Sum the values for each month and format it with four decimal places
        $cashOuts[$month] = number_format($suppliersSum + $pettyCashSum + $salarySum, 4, '.', '');
    }

    // Wrap the result in an array to match the required JSON format
    return [$cashOuts];
}

public function getTotalDataByProjectId(Request $request, $projectId)
{
    if (!$projectId) {
        return response()->json(['error' => 'ProjectID is required'], 400);
    }

    $data = [
        'FcInvoicesTotal' => FcInvoice::where('ProjectID', $projectId)->sum('Total'),
        'ActualInvoicesTotal' => ActualInvoice::where('ProjectID', $projectId)->sum('Total'),
        'ActualStaffsTotal' => ActualStaff::where('ProjectID', $projectId)->sum('Total'),
        'PlanStaffsTotal' => PlanStaff::where('ProjectID', $projectId)->sum('Total'),
        'HoCostOverhdsTotal' => Hocostoverhd::where('ProjectID', $projectId)->sum('Total'),
        'CertifiedInvoiceTotal' => CertifiedInvoice::where('ProjectID', $projectId)->sum('Total'),
        'CashInsTotal' => Cashin::where('ProjectID', $projectId)->sum('Total'),
        'CashOutsTotal' => Cashout::where('ProjectID', $projectId)->sum('Total')
    ];

    return response()->json($data);
}

}
