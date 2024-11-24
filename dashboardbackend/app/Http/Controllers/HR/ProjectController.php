<?php
namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Project;
use App\Models\HR\OrgChart; // Import the OrgChart model
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    // Get all projects
    public function index()
    {
        // Retrieve all projects from the database
        $projects = Project::all();
        return response()->json($projects);
    }

    // Store a new project (Create)
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'project_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        // Create the new project with the validated data
        $project = Project::create($validatedData);

        // After creating the project, create the org chart entry
        $orgChartData = [
            'project_id' => $project->id,   // Use the newly created project ID
            'position_id' => null,          // Set default or null for position_id
            'employee_id' => null,          // Set default or null for employee_id
            'manager_id' => null,           // Set default or null for manager_id
            'hierarchy_level' => 1,         // Set the default hierarchy level (e.g., 1 for the root of the org chart)
        ];

        // Create the OrgChart entry
        OrgChart::create($orgChartData);

        // Return the newly created project as a JSON response
        return response()->json($project, 201); // Status code 201 for "Created"
    }

    // Get a single project by ID (Read)
    public function show($id)
    {
        // Find the project by its ID
        $project = Project::findOrFail($id);

        // Return the project data as a JSON response
        return response()->json($project);
    }

    // Update a project (Update)
    public function update(Request $request, $id)
    {
        // Find the project by its ID
        $project = Project::findOrFail($id);

        // Validate the incoming request data
        $validatedData = $request->validate([
            'project_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        // Update the project with the validated data
        $project->update($validatedData);

        // Return the updated project as a JSON response
        return response()->json($project);
    }

    // Delete a project (Delete)
    public function destroy($id)
    {
        // Find the project by its ID
        $project = Project::findOrFail($id);

        // Delete the project from the database
        $project->delete();

        // Return a success message as a JSON response
        return response()->json(['message' => 'Project deleted successfully']);
    }
}
