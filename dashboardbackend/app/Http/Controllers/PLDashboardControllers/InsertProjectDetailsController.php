<?php

namespace App\Http\Controllers\PLDashboardControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class InsertProjectDetailsController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'ProjectName' => 'required|string|max:255',
            'MainScope' => 'required|string|max:255',
            'YearSelected' => 'required|digits:4',
            'ProjectManager' => 'required|string|max:255',
            'branch' => 'required|string|max:255',
        ]);

        $projectDetail = ProjectDetail::create([
            'ProjectName' => $request->ProjectName,
            'MainScope' => $request->MainScope,
            'YearSelected' => $request->YearSelected,
            'ProjectManager' => $request->ProjectManager,
            'branch' => $request->branch 
        ]);

        return response()->json(['message' => 'Project successfully created', 'project' => $projectDetail], 201);
    }
    public function getManagedProjects($username)
{
    $projects = ProjectDetail::where('ProjectManager', $username)->get();
    return response()->json(['projects' => $projects], 200);
}
public function getAllProjectYears()
{
    $user = auth()->user();
    $userBranch = $user->branch;

    if (!$userBranch) {
        return response()->json(['error' => 'User branch not found'], 400);
    }

    // Fetch distinct years, convert to integers, and sort them
    $years = ProjectDetail::where('branch', $userBranch)
                          ->distinct()
                          ->pluck('YearSelected')
                          ->map(function ($year) {
                              return (int)$year; // Convert to integer
                          })
                          ->sort()
                          ->values() // Reset keys after sorting
                          ->all();

    return response()->json(['years' => $years], 200);
}


public function getUAEProjectYears()
{
    $years = ProjectDetail::where('branch', 'UAE')
                          ->distinct()
                          ->pluck('YearSelected')
                          ->map(function ($year) {
                              return (int)$year; // Convert to integer
                          })
                          ->sort()
                          ->values() // Reset keys after sorting
                          ->all();

    return response()->json(['years' => $years], 200);
}

public function getKSAPLProjectYears()
{
    $years = ProjectDetail::where('branch', 'KSA')
                          ->distinct()
                          ->pluck('YearSelected')
                          ->map(function ($year) {
                              return (int)$year; // Convert to integer
                          })
                          ->sort()
                          ->values() // Reset keys after sorting
                          ->all();

    
    return response()->json(['years' => $years], 200);
}
public function getAllProjectBranches()
{
    
    $branches = ProjectDetail::distinct()->pluck('branch')->sort()->all();

    return response()->json(['branches' => $branches], 200);
}

public function updateBranch(Request $request)
    {
        $request->validate([
            'branch' => 'required|string',
        ]);

        $user = auth()->user();
        
        if (!$user) {
            Log::error('User not authenticated');
            return response()->json(['error' => 'User not authenticated or not found'], 400);
        }

        $userId = $user->id;
        
        // Log the user ID and branch being updated
        Log::info('Updating branch for user', ['user_id' => $userId, 'branch' => $request->input('branch')]);

        try {
            DB::table('plusers')
                ->where('id', $userId) // Find the user by ID
                ->update(['branch' => $request->input('branch')]); // Update the branch field

            Log::info('Branch updated successfully', ['user_id' => $userId]);
            return response()->json(['message' => 'Branch updated successfully']);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error updating branch', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
public function getProjectsByYear($year)
{
    $user = auth()->user();
    $userBranch = $user->branch;

    if (!$userBranch) {
        return response()->json(['error' => 'User branch not found'], 400);
    }
    $projects = ProjectDetail::where('YearSelected', $year)
    ->where('branch', $userBranch)
    ->get(['ProjectName', 'ProjectID']);
    return response()->json(['projects' => $projects], 200);
}
public function getYearsForManager($managerUsername)
{
    $years = ProjectDetail::where('ProjectManager', $managerUsername)
                ->distinct()
                ->pluck('YearSelected')
                ->sort()
                ->all();
                

    return response()->json(['years' => $years], 200);
}

public function getProjectsByManagerAndYear($username, $year)
{
    $projects = ProjectDetail::where('ProjectManager', $username)
                              ->where('YearSelected', $year)
                              ->get();

    return response()->json(['projects' => $projects], 200);
}

public function getProjectDetailsByName($id,$year)
{
   
    $project = ProjectDetail::where('ProjectID', $id)
    ->where('YearSelected', $year)
    ->first();

    if (!$project) {
        return response()->json(['message' => 'Project not found'], 404);
    
}

    return response()->json(['project' => $project], 200);
}


}
