<?php
namespace App\Http\Controllers\PLDashboardControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectDetail;
use App\Models\FcInvoice;
use App\Models\ActualInvoice;
use App\Models\ActualStaff;
use App\Models\PlanStaff;
use App\Models\Hocostoverhd;
use App\Models\Cashin;
use App\Models\Cashout;
use App\Models\CertifiedInvoice;
use Illuminate\Support\Facades\Log;
class GetCummulativeProjectDataController extends Controller
{
    public function getAllProjectsByYear($year)
    {
       
        // Validate the year format
        if (!preg_match('/^\d{4}$/', $year)) {
            return response()->json(['error' => 'Invalid year format. Year must be a four-digit number.'], 400);
        }
Log:info($year);
        // Get the authenticated user and their branch
        $user = auth()->user();
        $userBranch = $user->branch;
        
        // Check if the branch is available
        if (!$userBranch) {
            return response()->json(['error' => 'User branch not found'], 400);
        }

        // Retrieve project details for the given year and user's branch
        $projectDetails = ProjectDetail::where('YearSelected', $year)
            ->where('branch', $userBranch)
            ->get();

        // Check if any projects are found
        if ($projectDetails->isEmpty()) {
            return response()->json(['message' => 'No projects found for the selected year and branch'], 404);
        }
        Log::info("Projects for the year: $year", $projectDetails->toArray());

        $projectIds = $projectDetails->pluck('ProjectID');
        $combinedData['projectdetails'] = $projectDetails;

        // Fetching FcInvoice data
        $fcInvoices = FcInvoice::whereIn('ProjectID', $projectIds)->where('Year', $year)->get()->groupBy('ProjectID');
        foreach ($projectDetails as $project) {
            $combinedData['fcinvoice'][$project->ProjectID] = $fcInvoices[$project->ProjectID] ?? collect([]);
        }

        // Fetching ActualInvoice data
        $actualInvoices = ActualInvoice::whereIn('ProjectID', $projectIds)->where('Year', $year)->get()->groupBy('ProjectID');
        foreach ($projectDetails as $project) {
            $combinedData['actualinvoice'][$project->ProjectID] = $actualInvoices[$project->ProjectID] ?? collect([]);
        }

        // Fetching ActualStaff data
        $actualStaffs = ActualStaff::whereIn('ProjectID', $projectIds)->where('Year', $year)->get()->groupBy('ProjectID');
        foreach ($projectDetails as $project) {
            $combinedData['actualstaff'][$project->ProjectID] = $actualStaffs[$project->ProjectID] ?? collect([]);
        }

        // Fetching PlanStaff data
        $planStaffs = PlanStaff::whereIn('ProjectID', $projectIds)->where('Year', $year)->get()->groupBy('ProjectID');
        foreach ($projectDetails as $project) {
            $combinedData['planstaff'][$project->ProjectID] = $planStaffs[$project->ProjectID] ?? collect([]);
        }

        // Fetching Hocostoverhd data
        $hocostoverhds = Hocostoverhd::whereIn('ProjectID', $projectIds)->where('Year', $year)->get()->groupBy('ProjectID');
        foreach ($projectDetails as $project) {
            $combinedData['hocostoverhd'][$project->ProjectID] = $hocostoverhds[$project->ProjectID] ?? collect([]);
        }

        // Fetching Cashin data
        $cashins = Cashin::whereIn('ProjectID', $projectIds)->get()->where('Year', $year)->groupBy('ProjectID');
        foreach ($projectDetails as $project) {
            $combinedData['cashin'][$project->ProjectID] = $cashins[$project->ProjectID] ?? collect([]);
        }

        // Fetching Cashout data
        $cashouts = Cashout::whereIn('ProjectID', $projectIds)->where('Year', $year)->get()->groupBy('ProjectID');
        foreach ($projectDetails as $project) {
            $combinedData['cashout'][$project->ProjectID] = $cashouts[$project->ProjectID] ?? collect([]);
        }

         // Fetching Cashout data
         $certifiedinvoices = CertifiedInvoice::whereIn('ProjectID', $projectIds)->where('Year', $year)->get()->groupBy('ProjectID');
         foreach ($projectDetails as $project) {
            $combinedData['certifiedinvoice'][$project->ProjectID] = $certifiedinvoices[$project->ProjectID] ?? collect([]);

         }
        
        
        return response()->json($combinedData);
    }
}
