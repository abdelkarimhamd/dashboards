<?php

namespace App\Http\Controllers\FitOutControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FitOutProject;
use App\Models\FoActualMonthlyBase;
use App\Models\FoActualPercentage;
use App\Models\FoCashinMonthlyBase;
use App\Models\FoCashoutMonthlyBase;
use App\Models\FoPlanMonthlyBase;
use App\Models\FoPlanPercentage;
use Carbon\Carbon;
class FitOutProjectController extends Controller
{
    public function store(Request $request)
{
    // Validate the incoming request data
    $validatedData = $request->validate([
        'projectName' => 'required|string|max:255',
        'projectType' => 'required|string|max:255',
        'location' => 'required|string|max:255',
        'projectValue' => 'required|numeric',
        'approvedVO' => 'nullable|numeric',
        'revisedProjectValue' => 'required|numeric',
        'duration' => 'required|integer',
        'commencementDate' => 'required|date',
        'completionDate' => 'required|date',
        'approvedEOT' => 'nullable|integer',
        'updatedCompletionDate' => 'nullable|date',
        'projectManagerName' => 'required|string|max:255',
        'branch' => 'required|string|max:255',
    ]);

    // Use updateOrCreate to either update an existing project or create a new one
    $project = FitOutProject::updateOrCreate(
        // Match based on project name and branch (or any other unique fields)
        [
            'project_name' => $validatedData['projectName'],
            'branch' => $validatedData['branch'],
        ],
        // Update or create with these values
        [
            'project_type' => $validatedData['projectType'],
            'location' => $validatedData['location'],
            'project_value' => $validatedData['projectValue'],
            'approved_vo' => $validatedData['approvedVO'],
            'revised_project_value' => $validatedData['revisedProjectValue'],
            'duration' => $validatedData['duration'],
            'commencement_date' => $validatedData['commencementDate'],
            'completion_date' => $validatedData['completionDate'],
            'approved_eot' => $validatedData['approvedEOT'],
            'updated_completion_date' => $validatedData['updatedCompletionDate'],
            'project_manager_name' => $validatedData['projectManagerName'],

        ]
    );

    $startDate = Carbon::createFromFormat('Y-m-d', $validatedData['commencementDate']);
    $projectDuration = $validatedData['duration'];
    $branch = $validatedData['branch'];
    $projectId = $project->id;

    // Initialize related data for the new or updated project
    $this->initializePlanPercentage($projectId, $branch, $projectDuration, $startDate);
    $this->initializeActualPercentage($projectId, $branch, $projectDuration, $startDate);
    $this->initializeFoPlanValue($projectId, $branch, $projectDuration, $startDate);
    $this->initializeFoActualValue($projectId, $branch, $projectDuration, $startDate);
    $this->initializeFoCashIn($projectId, $branch, $projectDuration, $startDate);
    $this->initializeFoCashOut($projectId, $branch, $projectDuration, $startDate);

    return response()->json(['message' => 'Fit-out project saved successfully!', 'project' => $project], 200);
}

protected function initializePlanPercentage($projectId, $branch, $duration, Carbon $startDate)
{
    $endDate = $startDate->copy()->addMonths($duration - 1);
    $currentDate = $startDate->copy();

    while ($currentDate->lessThanOrEqualTo($endDate)) {
        // Check if a record for this project, branch, and year already exists
        $planPercentage = FoPlanPercentage::firstOrCreate(
            [
                'projectId' => $projectId,
                'branch' => $branch,
                'year' => $currentDate->year,
            ],
            // If the record does not exist, initialize months to 0
            [
                'jan' => 0,
                'feb' => 0,
                'mar' => 0,
                'apr' => 0,
                'may' => 0,
                'jun' => 0,
                'jul' => 0,
                'aug' => 0,
                'sep' => 0,
                'oct' => 0,
                'nov' => 0,
                'december' => 0,
            ]
        );

        if ($currentDate->year < $endDate->year) {
            $currentDate->addYear()->startOfYear();
        } else {
            break;
        }
    }

    return response()->json(['message' => 'Plan Percentage initialized successfully'], 201);
}

protected function initializeFoPlanValue($projectId, $branch, $duration, Carbon $startDate)
{
    $endDate = $startDate->copy()->addMonths($duration - 1);
    $currentDate = $startDate->copy();

    while ($currentDate->lessThanOrEqualTo($endDate)) {
        // Check if a record for this project, branch, and year already exists
        $planValue = FoPlanMonthlyBase::firstOrCreate(
            [
                'projectId' => $projectId,
                'branch' => $branch,
                'year' => $currentDate->year,
            ],
            [
                'jan' => 0,
                'feb' => 0,
                'mar' => 0,
                'apr' => 0,
                'may' => 0,
                'jun' => 0,
                'jul' => 0,
                'aug' => 0,
                'sep' => 0,
                'oct' => 0,
                'nov' => 0,
                'december' => 0,
            ]
        );

        if ($currentDate->year < $endDate->year) {
            $currentDate->addYear()->startOfYear();
        } else {
            break;
        }
    }

    return response()->json(['message' => 'Plan Value initialized successfully'], 201);
}


protected function initializeFoActualValue($projectId, $branch, $duration, Carbon $startDate)
{
    $endDate = $startDate->copy()->addMonths($duration - 1);
    $currentDate = $startDate->copy();

    while ($currentDate->lessThanOrEqualTo($endDate)) {
        // Check if a record for this project, branch, and year already exists
        $actualValue = FoActualMonthlyBase::firstOrCreate(
            [
                'projectId' => $projectId,
                'branch' => $branch,
                'year' => $currentDate->year,
            ],
            [
                'jan' => 0,
                'feb' => 0,
                'mar' => 0,
                'apr' => 0,
                'may' => 0,
                'jun' => 0,
                'jul' => 0,
                'aug' => 0,
                'sep' => 0,
                'oct' => 0,
                'nov' => 0,
                'december' => 0,
            ]
        );

        if ($currentDate->year < $endDate->year) {
            $currentDate->addYear()->startOfYear();
        } else {
            break;
        }
    }

    return response()->json(['message' => 'Actual Value initialized successfully'], 201);
}


protected function initializeActualPercentage($projectId, $branch, $duration, Carbon $startDate)
{
    $endDate = $startDate->copy()->addMonths($duration - 1);
    $currentDate = $startDate->copy();

    while ($currentDate->lessThanOrEqualTo($endDate)) {
        // Use firstOrCreate to avoid overwriting existing records
        $planPercentage = FoActualPercentage::firstOrCreate(
            [
                'projectId' => $projectId,
                'branch' => $branch,
                'year' => $currentDate->year,
            ],
            [
                'jan' => 0,
                'feb' => 0,
                'mar' => 0,
                'apr' => 0,
                'may' => 0,
                'jun' => 0,
                'jul' => 0,
                'aug' => 0,
                'sep' => 0,
                'oct' => 0,
                'nov' => 0,
                'december' => 0,
            ]
        );

        if ($currentDate->year < $endDate->year) {
            $currentDate->addYear()->startOfYear();
        } else {
            break;
        }
    }

    return response()->json(['message' => 'Actual Percentage initialized successfully'], 201);
}

protected function initializeFoCashIn($projectId, $branch, $duration, Carbon $startDate)
{
    $endDate = $startDate->copy()->addMonths($duration - 1);
    $currentDate = $startDate->copy();

    while ($currentDate->lessThanOrEqualTo($endDate)) {
        // Use firstOrCreate to avoid overwriting existing records
        $cashIn = FoCashinMonthlyBase::firstOrCreate(
            [
                'projectId' => $projectId,
                'branch' => $branch,
                'year' => $currentDate->year,
            ],
            [
                'jan' => 0,
                'feb' => 0,
                'mar' => 0,
                'apr' => 0,
                'may' => 0,
                'jun' => 0,
                'jul' => 0,
                'aug' => 0,
                'sep' => 0,
                'oct' => 0,
                'nov' => 0,
                'december' => 0,
            ]
        );

        if ($currentDate->year < $endDate->year) {
            $currentDate->addYear()->startOfYear();
        } else {
            break;
        }
    }

    return response()->json(['message' => 'Cash In initialized successfully'], 201);
}
protected function initializeFoCashOut($projectId, $branch, $duration, Carbon $startDate)
{
    $endDate = $startDate->copy()->addMonths($duration - 1);
    $currentDate = $startDate->copy();

    while ($currentDate->lessThanOrEqualTo($endDate)) {
        // Use firstOrCreate to avoid overwriting existing records
        $cashOut = FoCashoutMonthlyBase::firstOrCreate(
            [
                'projectId' => $projectId,
                'branch' => $branch,
                'year' => $currentDate->year,
            ],
            [
                'jan' => 0,
                'feb' => 0,
                'mar' => 0,
                'apr' => 0,
                'may' => 0,
                'jun' => 0,
                'jul' => 0,
                'aug' => 0,
                'sep' => 0,
                'oct' => 0,
                'nov' => 0,
                'december' => 0,
            ]
        );

        if ($currentDate->year < $endDate->year) {
            $currentDate->addYear()->startOfYear();
        } else {
            break;
        }
    }

    return response()->json(['message' => 'Cash Out initialized successfully'], 201);
}

public function show($projectName)
{
    // Find the project by project_name instead of ID
    $project = FitOutProject::where('project_name', $projectName)->first();

    if (!$project) {
        return response()->json(['message' => 'Project not found'], 404);
    }

    return response()->json($project);
}

public function update(Request $request, $projectName)
{
    // Validate incoming data
    $validatedData = $request->validate([
        'projectType' => 'required|string|max:255',
        'location' => 'required|string|max:255',
        'projectValue' => 'required|numeric',
        'approvedVO' => 'nullable|numeric',
        'revisedProjectValue' => 'required|numeric',
        'duration' => 'required|integer',
        'commencementDate' => 'required|date',
        'completionDate' => 'required|date',
        'approvedEOT' => 'nullable|integer',
        'updatedCompletionDate' => 'nullable|date',
        'projectManagerId' => 'required|integer',
        'branch' => 'required|string|max:255',
    ]);

    // Find the project
    $project = FitOutProject::where('project_name', $projectName)->first();

    if (!$project) {
        return response()->json(['message' => 'Project not found'], 404);
    }

    // Update the project
    $project->update([
        'project_type' => $validatedData['projectType'],
        'location' => $validatedData['location'],
        'project_value' => $validatedData['projectValue'],
        'approved_vo' => $validatedData['approvedVO'],
        'revised_project_value' => $validatedData['revisedProjectValue'],
        'duration' => $validatedData['duration'],
        'commencement_date' => $validatedData['commencementDate'],
        'completion_date' => $validatedData['completionDate'],
        'approved_eot' => $validatedData['approvedEOT'],
        'updated_completion_date' => $validatedData['updatedCompletionDate'],
        'project_manager_id' => $validatedData['projectManagerId'],
        'branch' => $validatedData['branch'],
    ]);

    return response()->json(['message' => 'Project updated successfully', 'project' => $project], 200);
}

}
