<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Deal;
use App\Models\CRM\Contact;
use App\Models\CRM\Company;
use App\Models\CRM\Activity;
use App\Models\CRM\Task; // Import Task model
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Retrieve CRM dashboard metrics with optional department filters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMetrics(Request $request)
    {
        // Validate query parameters
        $validated = $request->validate([
            'quarter'      => 'sometimes|string|in:Q1,Q2,Q3,Q4,all',
            'start_date'   => 'sometimes|date',
            'end_date'     => 'sometimes|date|after_or_equal:start_date',
            'department'   => 'sometimes|array',
            'department.*' => 'string', // Removed 'exists' validation for flexibility
        ]);

        // Extract query parameters with default values
        $quarter     = $request->query('quarter', 'all');
        $startDate   = $request->query('start_date');
        $endDate     = $request->query('end_date');
        $departments = $request->query('department'); // Array of departments

        // Determine date range based on quarter or specific dates
        if ($quarter && $quarter !== 'all') {
            $currentYear = Carbon::now()->year;
            switch ($quarter) {
                case 'Q1':
                    $start = Carbon::create($currentYear, 1, 1)->startOfDay();
                    $end   = Carbon::create($currentYear, 3, 31)->endOfDay();
                    break;
                case 'Q2':
                    $start = Carbon::create($currentYear, 4, 1)->startOfDay();
                    $end   = Carbon::create($currentYear, 6, 30)->endOfDay();
                    break;
                case 'Q3':
                    $start = Carbon::create($currentYear, 7, 1)->startOfDay();
                    $end   = Carbon::create($currentYear, 9, 30)->endOfDay();
                    break;
                case 'Q4':
                    $start = Carbon::create($currentYear, 10, 1)->startOfDay();
                    $end   = Carbon::create($currentYear, 12, 31)->endOfDay();
                    break;
                default:
                    // Default to last 30 days if quarter is invalid
                    $start = Carbon::now()->subDays(value: 30)->startOfDay();
                    $end   = Carbon::now()->endOfDay();
                    break;
            }
        } elseif ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end   = Carbon::parse($endDate)->endOfDay();
        } else {
            // Default to last 30 days
            $start = "2024-01-01 00:00:00 ";

            $end   = Carbon::now()->endOfDay();
        }

        // Base query for Deals
        $dealsQuery = Deal::whereBetween('created_at', [$start, $end]);

        // Apply department filter if provided
        if ($departments) {
            $dealsQuery->whereIn('department', $departments);
        }

        // Total Deals
        $totalDeals = $dealsQuery->count();

        // Total Actual Revenue (Closed Won)
        $actualRevenue = (clone $dealsQuery)->where('stage', 'Closed Won')->sum('amount');
        $customerLost = (clone $dealsQuery)->where('stage', 'Closed Lost')->sum('amount');

        // Total Forecast Revenue (Not Closed Won or Lost)
        $forecastRevenue = (clone $dealsQuery)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->sum('amount');

        // Deals by Stage
        $dealsByStage = (clone $dealsQuery)
            ->select('stage', DB::raw('count(*) as total'))
            ->groupBy('stage')
            ->get();

        // Deals by Department
        $dealsByDepartment = (clone $dealsQuery)
            ->select('department', DB::raw('count(*) as total'))
            ->groupBy('department')
            ->get();

        // Revenue by Department (Both Actual and Forecast)
        $revenueByDepartment = $this->getRevenueByDepartment($dealsQuery);
        Log::info($actualRevenue);

        // Conversion Rate
        $conversionRate = $this->calculateConversionRate($dealsQuery);

        // Average Deal Size for Actual Deals
        $averageDealSizeActual = (clone $dealsQuery)
            ->where('stage', 'Closed Won')
            ->average('amount') ?? 0;

        // Average Deal Size for Forecasted Deals
        $averageDealSizeForecast = (clone $dealsQuery)
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->average('amount') ?? 0;

        // Sales Pipeline Value (sum of 'Open' deals)
        $salesPipelineValue = (clone $dealsQuery)
            ->where('status', 'Open')
            ->sum('amount');

        // Activities Over Time
        $activitiesOverTime = $this->getActivitiesOverTime($start, $end, $departments);

        // Activities Per User
        $activitiesPerUser = $this->getActivitiesPerUser($start, $end, $departments);

        // Contacts Per User
        $contactsPerUser = $this->getContactsPerUser($start, $end, $departments);

        // Deals Per User
        $dealsPerUser = $this->getDealsPerUser($start, $end, $departments);

        // Task Metrics
        $taskMetrics = $this->getTaskMetrics($start, $end, $departments);

        // Total Companies
        $totalCompanies = Company::whereBetween('created_at', [$start, $end])->count();

        // Total Contacts
        $totalContacts = Contact::whereBetween('created_at', [$start, $end])->count();

        // Total Activities
        $totalActivities = Activity::whereBetween('created_at', [$start, $end])->count();

        // Return the metrics as JSON
        return response()->json([
            'totalCompanies'          => $totalCompanies,
            'totalContacts'           => $totalContacts,
            'totalDeals'              => $totalDeals,
            'totalActivities'         => $totalActivities,
            'actualRevenue'           => $actualRevenue,
            'forecastRevenue'         => $forecastRevenue,
            'customerLost'            => $customerLost,
            'revenueByDepartment'     => $revenueByDepartment,
            'dealsByStage'            => $dealsByStage,
            'dealsByDepartment'       => $dealsByDepartment,
            'conversionRate'          => $conversionRate,
            'averageDealSizeActual'   => $averageDealSizeActual,
            'averageDealSizeForecast' => $averageDealSizeForecast,
            'salesPipelineValue'      => $salesPipelineValue,
            'activitiesOverTime'      => $activitiesOverTime,
            'activitiesPerUser'       => $activitiesPerUser,
            'contactsPerUser'         => $contactsPerUser,
            'dealsPerUser'            => $dealsPerUser,
            // Include Task Metrics
            'tasks'                   => $taskMetrics,
        ], 200);
    }

    /**
     * Calculate conversion rate.
     *
     * @param \Illuminate\Database\Eloquent\Builder $dealsQuery
     * @return float
     */
    private function calculateConversionRate($dealsQuery)
    {
        $totalDeals = (clone $dealsQuery)->count();

        $convertedDeals = (clone $dealsQuery)
            ->where('stage', 'Closed Won')
            ->count();

        if ($totalDeals == 0) {
            return 0.00;
        }

        return round(($convertedDeals / $totalDeals) * 100, 2);
    }

    /**
     * Retrieve activities over time within the specified date range and departments.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @param array|null $departments
     * @return \Illuminate\Support\Collection
     */
    private function getActivitiesOverTime($start, $end, $departments)
    {
        $activitiesQuery = Activity::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$start, $end]);

        // Apply department filter if Activity model has 'department' field
        if ($departments) {
            // Uncomment the following line if Activity has a 'department' field
            // $activitiesQuery->whereIn('department', $departments);
        }

        $activities = $activitiesQuery
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return $activities;
    }

    /**
     * Retrieve activities per user.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @param array|null $departments
     * @return \Illuminate\Support\Collection
     */
    private function getActivitiesPerUser($start, $end, $departments)
    {
        $activitiesQuery = Activity::select('user_id', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$start, $end]);

        // Apply department filter if Activity model has 'department' field
        // if ($departments) {
        //     // Uncomment the following line if Activity has a 'department' field
        //     // $activitiesQuery->whereIn('department', $departments);
        // }

        $activitiesPerUser = $activitiesQuery
            ->groupBy('user_id')
            ->with('user:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'user_id'   => $item->user_id,
                    'user_name' => $item->user ? $item->user->name : 'Unknown User',
                    'total'     => $item->total,
                ];
            });

        return $activitiesPerUser;
    }

    /**
     * Retrieve contacts per user.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @param array|null $departments
     * @return \Illuminate\Support\Collection
     */
    private function getContactsPerUser($start, $end, $departments)
    {
        $contactsQuery = Contact::select('created_by', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$start, $end]);

        // Apply department filter if Contact model has 'department' field
        // if ($departments) {
        //     // Uncomment the following line if Contact has a 'department' field
        //     // $contactsQuery->whereIn('department', $departments);
        // }

        $contactsPerUser = $contactsQuery
            ->groupBy('created_by')
            ->with('createdBy:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'user_id'   => $item->created_by,
                    'user_name' => $item->createdBy ? $item->createdBy->name : 'Unknown User',
                    'total'     => $item->total,
                ];
            });

        return $contactsPerUser;
    }

    /**
     * Retrieve deals per user.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @param array|null $departments
     * @return \Illuminate\Support\Collection
     */
    private function getDealsPerUser($start, $end, $departments)
    {
        $dealsQuery = Deal::select('created_by', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$start, $end]);

        // Apply department filter
        if ($departments) {
            $dealsQuery->whereIn('department', $departments);
        }

        $dealsPerUser = $dealsQuery
            ->groupBy('created_by')
            ->with('createdBy:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'user_id'   => $item->created_by,
                    'user_name' => $item->createdBy ? $item->createdBy->name : 'Unknown User',
                    'total'     => $item->total,
                ];
            });

        return $dealsPerUser;
    }

    /**
     * Calculate revenue by department.
     *
     * @param \Illuminate\Database\Eloquent\Builder $dealsQuery
     * @return \Illuminate\Support\Collection
     */
    private function getRevenueByDepartment($dealsQuery)
    {
        // Clone the base query for actual revenue
        $actualRevenueQuery = (clone $dealsQuery)
            ->select('department', DB::raw('SUM(amount) as actual_revenue'))
            ->where('stage', 'Closed Won')
            ->groupBy('department');

        // Clone the base query for forecast revenue
        $forecastRevenueQuery = (clone $dealsQuery)
            ->select('department', DB::raw('SUM(amount) as forecast_revenue'))
            ->whereNotIn('stage', ['Closed Won', 'Closed Lost'])
            ->groupBy('department');

        // Execute queries and key by department for easy access
        $actualRevenue = $actualRevenueQuery->get()->keyBy('department');
        $forecastRevenue = $forecastRevenueQuery->get()->keyBy('department');

        // Merge department keys from both actual and forecast
        $departments = $actualRevenue->keys()->merge($forecastRevenue->keys())->unique();

        // Compile revenue data per department
        $revenueByDepartment = $departments->map(function ($dept) use ($actualRevenue, $forecastRevenue) {
            return [
                'department'       => $dept,
                'actual_revenue'   => $actualRevenue->has($dept) ? $actualRevenue[$dept]->actual_revenue : 0,
                'forecast_revenue' => $forecastRevenue->has($dept) ? $forecastRevenue[$dept]->forecast_revenue : 0,
            ];
        });

        return $revenueByDepartment->values();
    }

    /**
     * Retrieve Task-related metrics.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @param array|null $departments
     * @return array
     */
    private function getTaskMetrics($start, $end, $departments)
    {
        // Base query for Tasks
        $tasksQuery = Task::whereBetween('created_at', [$start, $end]);

        // Apply department filter if applicable
        if ($departments) {
            // Assuming Task model has a 'department' field
            // If not, adjust accordingly based on associated records
            // For example, if associated_record_type is 'Company', you might join with companies
            // and filter based on company department
            // Here's an example assuming a 'department' field exists
            // $tasksQuery->whereIn('department', $departments);
        }

        // Total Tasks
        $totalTasks = $tasksQuery->count();

        // Tasks by Status
        $tasksByStatus = $tasksQuery
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // Tasks by Priority
        $tasksByPriority = Task::aggregateByPriority()
        ->whereBetween('created_at', ['2024-10-04 00:00:00', '2024-11-03 23:59:59'])
        ->whereNull('deleted_at')
        ->get()
        ->map(function($item) {
            return [
                'priority' => ucfirst($item->normalized_priority),
                'total' => $item->total,
            ];
        });
    
    Log::info($tasksByPriority);
        // Tasks by Assignee
        $tasksByAssignee = Task::whereBetween('created_at', [$start, $end])
            ->select('assigned_to', DB::raw('count(*) as total'))
            ->groupBy('assigned_to')
            ->with('assignee:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'user_id'   => $item->assigned_to,
                    'user_name' => $item->assignee ? $item->assignee->name : 'Unassigned',
                    'total'     => $item->total,
                ];
            });

        // Tasks Over Time
        $tasksOverTime = $tasksQuery
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return [
            'totalTasks'      => $totalTasks,
            'tasksByStatus'   => $tasksByStatus,
            'tasksByPriority' => $tasksByPriority,
            'tasksByAssignee' => $tasksByAssignee,
            'tasksOverTime'   => $tasksOverTime,
        ];
    }

    /**
     * Retrieve the list of unique departments.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDepartments()
    {
        // Fetch unique departments from Deals
        $departments = Deal::select('department')->distinct()->pluck('department');

        return response()->json([
            'departments' => $departments,
        ], 200);
    }
}
