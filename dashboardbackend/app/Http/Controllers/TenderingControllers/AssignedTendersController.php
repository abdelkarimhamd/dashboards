<?php

namespace App\Http\Controllers\TenderingControllers;

use App\Http\Controllers\Controller;
use App\Models\Tender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
class AssignedTendersController extends Controller
{
    public function getAssignedTenders()
    {
        $user = Auth::user();
        $facilityManagementOptions = ['FMMA', 'AFM', 'FM', 'FMC', 'TFM'];
        $assetManagementOptions = ['FMMA', 'AFM', 'FMC', 'TFM', 'AM'];
        $projectManagmentOptions = ['PMC', 'PMC & CS', 'PMC, CS & Design'];

        // Determine which tenders to retrieve based on the user's role
        $tendersQuery = Tender::query();

        switch ($user->role) {
            case 'Coordinator Fit-out':
                $tendersQuery->where('status', 'under review by coordinator fit-out');
                break;

            case 'Coordinator Facility':
                $tendersQuery->where('status', 'under review by coordinator facility');
                break;

            case 'Coordinator Asset':
                $tendersQuery->where('status', 'under review by coordinator asset');
                break;

            case 'Project Management':
                $tendersQuery->where('status', 'under review by project management');
                break;

            case 'Fit-out':
                $tendersQuery->where('status', 'under review by head of fit-out');
                break;

            case 'Facility Management':
                $tendersQuery->where(function ($query) use ($facilityManagementOptions, $assetManagementOptions) {
                    $query->where('status', 'under review by head of facility management')
                        ->orWhere('status', 'under review by head of facility')
                        ->orWhere(function ($query) use ($facilityManagementOptions, $assetManagementOptions) {
                            $query->where('status', 'under review by head of asset and facility')
                                ->whereIn('selectedOption', $facilityManagementOptions)
                                ->whereIn('selectedOption', $assetManagementOptions);
                        });
                });
                break;

            case 'Asset Management':
                Log::info("asset");
                $tendersQuery->where(function ($query) use ($assetManagementOptions, $facilityManagementOptions) {
                    $query->where('status', 'under review by head of asset management')->orWhere('status', 'under review by head of asset')
                        ->orWhere(function ($query) use ($assetManagementOptions, $facilityManagementOptions) {
                            $query->where('status', 'under review by head of asset and facility')
                                ->whereIn('selectedOption', $assetManagementOptions)
                                ->whereIn('selectedOption', $facilityManagementOptions);
                        });
                });
                break;

            case 'Executives (CEO)':
                $tendersQuery->where('status', 'awaiting ceo decision');
                break;

            case 'Executives Managing Director':
                $tendersQuery->where('status', 'awaiting managing director decision');
                break;

            default:
                return response()->json([
                    'message' => 'No tenders available for your role.'
                ], 404);
        }
            // Count the tenders
            $tenderCount = $tendersQuery->count();
        // Eager load the created_by and updated_by relationships
        $tenders = $tendersQuery->select('id', 'tenderTitle','branch','tender_value' ,'created_by', 'updated_by', 'updated_at', 'status')
            ->with([
                'createdBy' => function ($query) {
                    $query->select('id', 'name');
                },
                'updatedBy' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
            ->get()
            ->map(function ($tender) {
                return [
                    'id' => $tender->id,
                    'tenderTitle' => $tender->tenderTitle,
                    'created_by_name' => $tender->createdBy->name ?? 'Unknown',
                    'updated_by_name' => $tender->updatedBy->name ?? 'Unknown',
                    'updated_at' => $tender->updated_at,
                    'branch' => $tender->branch,
                    'status' => $tender->status,
                    'tender_value' => $tender->tender_value,
                ];
            });

        return response()->json(['tenders' => $tenders,'tenderCount' => $tenderCount], 200);
    }

    public function getAssignedTendersCount()
    {
        $user = Auth::user();
        $facilityManagementOptions = ['FMMA', 'AFM', 'FM', 'FMC', 'TFM'];
        $assetManagementOptions = ['FMMA', 'AFM', 'FMC', 'TFM', 'AM'];
        $projectManagmentOptions = ['PMC', 'PMC & CS', 'PMC, CS & Design'];
    
        // Log the user's role to ensure it's being read correctly
        Log::info('User Role: ' . $user->role);
    
        // Determine which tenders to retrieve based on the user's role
        $tendersQuery = Tender::query();
    
        switch ($user->role) {
            case 'Coordinator Fit-out':
                $tendersQuery->where('status', 'under review by coordinator fit-out');
                break;
    
            case 'Coordinator Facility':
                $tendersQuery->where('status', 'under review by coordinator facility');
                break;
    
            case 'Coordinator Asset':
                $tendersQuery->where('status', 'under review by coordinator asset');
                break;
    
            case 'Project Management':
                $tendersQuery->where('status', 'under review by project management');
                break;
    
            case 'Fit-out':
                $tendersQuery->where('status', 'under review by head of fit-out');
                break;
    
            case 'Facility Management':
                $tendersQuery->where(function ($query) use ($facilityManagementOptions, $assetManagementOptions) {
                    $query->where('status', 'under review by head of facility management')
                        ->orWhere('status', 'under review by head of facility')
                        ->orWhere(function ($query) use ($facilityManagementOptions, $assetManagementOptions) {
                            $query->where('status', 'under review by head of asset and facility')
                                ->whereIn('selectedOption', $facilityManagementOptions)
                                ->whereIn('selectedOption', $assetManagementOptions);
                        });
                });
                break;
    
            case 'Asset Management':
                $tendersQuery->where(function ($query) use ($assetManagementOptions, $facilityManagementOptions) {
                    $query->where('status', 'under review by head of asset management')
                        ->orWhere('status', 'under review by head of asset')
                        ->orWhere(function ($query) use ($assetManagementOptions, $facilityManagementOptions) {
                            $query->where('status', 'under review by head of asset and facility')
                                ->whereIn('selectedOption', $assetManagementOptions)
                                ->whereIn('selectedOption', $facilityManagementOptions);
                        });
                });
                break;
    
            case 'Executives (CEO)':
                $tendersQuery->where('status', 'awaiting ceo decision');
                break;
    
            case 'Executives Managing Director':
                $tendersQuery->where('status', 'awaiting managing director decision');
                break;
            case 'President':
                
                break;
    
            default:
                return response()->json([
                    'message' => 'No tenders available for your role.'
                ], 404);
        }
    

    
        // Count the tenders
        $tenderCount = $tendersQuery->count();
      
    
        // Return only the count of assigned tenders
        return response()->json(['tenderCount' => $tenderCount], 200);
    }
    

    public function getTenderCountsByStatus(Request $request)
    {
        // Get the quarter from the request, default to the first quarter if not provided
        $quarter = $request->input('quarter', 1);
        $year = $request->input('year', date('Y')); // Get the year from the request, default to the current year
    
        // If "All Quarters/Year" is selected, fetch data for the entire year
        if ($quarter == 0) {
            $startDate = Carbon::create($year, 1, 1)->startOfDay();
            $endDate = Carbon::create($year, 12, 31)->endOfDay();
        } else {
            // Determine the start and end dates for the selected quarter using Carbon
            switch ($quarter) {
                case 1:
                    $startDate = Carbon::create($year, 1, 1)->startOfDay();
                    $endDate = Carbon::create($year, 3, 31)->endOfDay();
                    break;
                case 2:
                    $startDate = Carbon::create($year, 4, 1)->startOfDay();
                    $endDate = Carbon::create($year, 6, 30)->endOfDay();
                    break;
                case 3:
                    $startDate = Carbon::create($year, 7, 1)->startOfDay();
                    $endDate = Carbon::create($year, 9, 30)->endOfDay();
                    break;
                case 4:
                    $startDate = Carbon::create($year, 10, 1)->startOfDay();
                    $endDate = Carbon::create($year, 12, 31)->endOfDay();
                    break;
                default:
                    return response()->json(['error' => 'Invalid quarter specified'], 400);
            }
        }
    
        // Initialize counts for each role and status
        $counts = [
            'coordinator_fit_out' => 0,
            'coordinator_facility' => 0,
            'coordinator_asset' => 0,
            'project_management' => 0,
            'fit_out' => 0,
            'facility_management' => 0,
            'asset_management' => 0,
            'asset_and_facility' => 0,
            'executives_ceo' => 0,
            'executives_md' => 0,
        ];
    
        // Calculate counts for each status and role within the selected quarter or entire year
        $counts['coordinator_fit_out'] = Tender::where('status', 'under review by coordinator fit-out')
                                               ->whereBetween('created_at', [$startDate, $endDate])
                                               ->count();
    
        $counts['coordinator_facility'] = Tender::where('status', 'under review by coordinator facility')
                                                ->whereBetween('created_at', [$startDate, $endDate])
                                                ->count();
    
        $counts['coordinator_asset'] = Tender::where('status', 'under review by coordinator asset')
                                             ->whereBetween('created_at', [$startDate, $endDate])
                                             ->count();
    
        $counts['asset_and_facility'] = Tender::where('status', 'under review by head of asset and facility')
                                             ->whereBetween('created_at', [$startDate, $endDate])
                                             ->count();
    
        $counts['project_management'] = Tender::where('status', 'under review by project management')
                                              ->whereBetween('created_at', [$startDate, $endDate])
                                              ->count();
    
        $counts['fit_out'] = Tender::where('status', 'under review by head of fit-out')
                                   ->whereBetween('created_at', [$startDate, $endDate])
                                   ->count();
    
        $counts['facility_management'] = Tender::where(function ($query) {
            $query->where('status', 'under review by head of facility management')
                  ->orWhere('status', 'under review by head of facility')
                  ->orWhere(function ($query) {
                      $query->where('status', 'under review by head of asset and facility')
                            ->whereIn('selectedOption', ['option1', 'option2']); // Replace with your actual facility management options
                  });
        })->whereBetween('created_at', [$startDate, $endDate])->count();
    
        $counts['asset_management'] = Tender::where(function ($query) {
            $query->where('status', 'under review by head of asset management')
                  ->orWhere('status', 'under review by head of asset')
                  ->orWhere(function ($query) {
                      $query->where('status', 'under review by head of asset and facility')
                            ->whereIn('selectedOption', ['option3', 'option4']); // Replace with your actual asset management options
                  });
        })->whereBetween('created_at', [$startDate, $endDate])->count();
    
        $counts['executives_ceo'] = Tender::where('status', 'awaiting ceo decision')
                                          ->whereBetween('created_at', [$startDate, $endDate])
                                          ->count();
    
        $counts['executives_md'] = Tender::where('status', 'awaiting managing director decision')
                                         ->whereBetween('created_at', [$startDate, $endDate])
                                         ->count();
    
        return response()->json(['counts' => $counts]);
    }
    


}
