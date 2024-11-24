<?php
namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\OrgChart;
use Illuminate\Support\Facades\Log;

class OrgChartController extends Controller
{
    // Fetch all org charts with related project, employee, position, and manager relationships
    public function index()
    {
        // Get all org charts with employee, position, manager, and project relationships
        $orgCharts = OrgChart::with(['employee', 'position', 'manager', 'project'])
            ->orderBy('hierarchy_level', 'asc')
            ->get();

        return response()->json($orgCharts);
    }

    // Get org charts by project and build hierarchy
    public function getOrgChartByProject($projectId)
    {
        // Get all org charts for a specific project ordered by hierarchy level
        $orgChartData = OrgChart::with(['employee', 'position', 'manager', 'project'])
            ->where('project_id', $projectId)
            ->orderBy('hierarchy_level', 'asc')
            ->get();

        Log::info("Project ID: " . $projectId);
        Log::info("Org Chart Data: ", $orgChartData->toArray());

        // Build hierarchical structure, ensuring that only employees with "accepted" status are added
        $orgChartTree = $this->buildHierarchy($orgChartData);
        Log::info("Built Org Chart Tree: ", $orgChartTree);

        return response()->json($orgChartTree);
    }

    // Helper function to build hierarchy
    private function buildHierarchy($orgChartData)
    {
        $orgChartArray = $orgChartData->toArray();
        $orgChartMap = [];
        $tree = [];

        // Step 1: Build a map of all nodes (employee_id -> node), filtering by "accepted" status
        foreach ($orgChartArray as $node) {
            if (!empty($node['employee']) && $node['employee']['status'] === 'accepted') {
                $node['children'] = []; // Initialize children array for each node
                $orgChartMap[$node['employee_id']] = $node;
            }
        }

        // Step 2: Link children to their managers
        foreach ($orgChartArray as $node) {
            if (!empty($node['employee_id']) && !empty($node['manager_id']) && isset($orgChartMap[$node['employee_id']])) {
                $managerId = $node['manager_id'];

                // Add to the manager's children array if the manager exists
                if (isset($orgChartMap[$managerId])) {
                    $orgChartMap[$managerId]['children'][] = &$orgChartMap[$node['employee_id']]; // Use reference to keep link
                    Log::info("Adding {$node['employee']['first_name']} to manager {$orgChartMap[$managerId]['employee']['first_name']}");
                }
            }
        }

        // Step 3: Build the root tree (nodes without a manager)
        foreach ($orgChartMap as $node) {
            if (empty($node['manager_id'])) {
                $tree[] = $node;
            }
        }

        // Log final tree structure
        Log::info("Final Org Chart Tree: ", $tree);

        return $tree;
    }
}
