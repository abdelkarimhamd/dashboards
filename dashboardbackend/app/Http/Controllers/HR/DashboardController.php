<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\Position;
use App\Models\HR\Project; // Assuming you have a Project model
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function getPositions()
    {
        return response()->json(Position::all());
    }
    public function getEmployeeStatusCount()
    {
        // Define the available statuses, including the new ones
        $statuses = [
            'accepted',
            'under_review',
            'to_be_interviewed',
            'waiting_hr_response',
            'client_interview',
            'rejected',
            'rejected_by_client',
            'external',
            'other'
        ];

        // Fetch the count of employees grouped by their status
        $statusCounts = Employee::select('status', DB::raw('count(*) as total'))
            ->whereIn('status', $statuses)
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();

        // Ensure all statuses are included in the response, even if they have a count of zero
        foreach ($statuses as $status) {
            if (!isset($statusCounts[$status])) {
                $statusCounts[$status] = 0; // Set count to 0 for missing statuses
            }
        }

        // Log the result for debugging purposes (optional)
        Log::info($statusCounts);

        // Return the counts as a JSON response
        return response()->json($statusCounts);
    }
    public function getProjects()
    {
        return response()->json(Project::all());
    }

    public function getEmployees()
    {
        return response()->json(Employee::all());
    }

    public function getEmployeeGrowth()
    {
        // Define the start and end date for the analysis period
        $startDate = Carbon::now()->subMonths(4); // For example, last 4 months
        $endDate = Carbon::now();

        // Group employees by month and count them
        $growthData = Employee::selectRaw('DATE_FORMAT(created_at, "%b") as month, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('created_at')
            ->get();

        // Prepare data for the response
        $months = [];
        $counts = [];

        // Fill the months and counts arrays
        foreach ($growthData as $data) {
            $months[] = $data->month; // Month abbreviation
            $counts[] = $data->count;  // Count of employees for that month
        }

        // Return the structured data
        return response()->json([
            'months' => $months,
            'counts' => $counts,
        ]);
    }

    public function getEmployeeDistribution()
    {
        // Get the distribution of employees by position, excluding those without a position
        $distribution = Employee::with('position')
            ->whereNotNull('position_id') // Exclude employees with null position_id
            ->select('position_id')
            ->get()
            ->groupBy('position_id')
            ->map(function ($group) {
                return [
                    'position_id' => $group[0]->position_id,
                    'total' => $group->count(),
                    'position' => $group[0]->position // Eager loaded position
                ];
            })
            ->values();

        Log::info($distribution);
        return response()->json($distribution);
    }

    public function getEmployeesByProject()
    {
        // Fetch employees and their associated project
        $employees = Employee::with('project')->get();

        // Group employees by project
        $grouped = $employees->groupBy(function ($employee) {
            // If the employee is not assigned to any project, group under 'Other'
            return $employee->project ? $employee->project->project_name : 'Other';
        });

        // Prepare the response structure
        $result = $grouped->map(function ($group, $projectName) {
            return [
                'project' => $projectName,
                'employees' => $group->map(function ($employee) {
                    return [
                        'employee_id' => $employee->id,
                        'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                        'position' => $employee->position ? $employee->position->position_name : 'No Position Assigned'
                    ];
                })
            ];
        })->values();

        return response()->json($result);
    }
}
