<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\Header;
use App\Models\ActualHr;
use App\Models\User;
use App\Mail\ReminderActualStaffMail;
use App\Mail\EscalationActualStaffMail;
use App\Mail\FinalActualStaffEscalationMail;
class SendActualStaffProjectReminderEmails extends Command
{
    protected $signature = 'send:project-reminde-actual-emails';
    protected $description = 'Send email reminders to project managers if actual staff is not filled';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
{
    // Fetch all projects
    $projects = Header::all();
    $currentMonthAbbreviation = strtolower(Carbon::now()->format('M')); // Converts month to lowercase, e.g., 'aug'
    $currentYear = Carbon::now()->year;

    foreach ($projects as $project) {
        // Find the actual value record for the project
        $actualValue = ActualHr::where('projectId', $project->id)
            ->where('year', $currentYear)
            ->first();
        Log::info($project);
        Log::info($actualValue);

        // Ensure we check the correct month column
        if ($actualValue && isset($actualValue->{$currentMonthAbbreviation}) && $actualValue->{$currentMonthAbbreviation} == 0) {
            $daysSinceStartOfMonth = Carbon::now()->day;

            // Look up the project manager's email
            $projectManager = $this->getProjectManager($project->projectManagerName);

            if (!$projectManager) {
                // If the project manager is not found, log an error and skip this project
                Log::error("Project manager not found for project: " . $project->projectName);
                continue;
            }

            if ($daysSinceStartOfMonth == 5) {
                Mail::to($projectManager->email)->send(new ReminderActualStaffMail($project));
            } elseif ($daysSinceStartOfMonth == 8) {
                Mail::to($projectManager->email)->send(new EscalationActualStaffMail($project));
            } elseif ($daysSinceStartOfMonth == 10) {
                $departmentHeadEmails = $this->getDepartmentHeadEmails($project->projectType, $project->branch);
                Mail::to($projectManager->email)
                    ->cc($departmentHeadEmails)
                    ->send(new FinalActualStaffEscalationMail($project));
            }
        }
    }
}

    private function getProjectManager($projectManagerName)
    {
        // Query the `users` table to find the project manager by name
        return User::where('name', $projectManagerName)->first();
    }

    private function getDepartmentHeadEmails($mainScope, $branch)
    {
        // Define the role mappings with their respective main scope values
        $roles = [
            'Project Management' => ['PMC', 'PMC & CS', 'PMC, CS & Design'],
            'Fit Out' => ['FO', 'FOM', 'DNP'],
            'Facility Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM','PMC', 'PMC & CS', 'PMC, CS & Design'],
            'Asset Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM','PMC', 'PMC & CS', 'PMC, CS & Design']
        ];
    
        Log::info("MainScope: $mainScope");
    
        $departmentHeadEmails = [];
    
        // Iterate through all roles and collect department heads that match the main scope
        foreach ($roles as $role => $options) {
            if (in_array($mainScope, $options)) {
                // Get all department heads for the given role and branch
                $departmentHeads = User::where('department', $role)
                    ->where('branch', $branch)
                    ->get(); // Use get() to fetch all matching records
    
                // Add the emails to the list
                foreach ($departmentHeads as $departmentHead) {
                    Log::info("Department head found: " . $departmentHead->name . " with email: " . $departmentHead->email);
                    $departmentHeadEmails[] = $departmentHead->email;
                }
            }
        }
    
        // Check if any department head emails were found
        if (!empty($departmentHeadEmails)) {
            return $departmentHeadEmails;
        } else {
            Log::warning("No department head found for main scope: $mainScope and branch: $branch");
            return ['default_head@example.com']; // Fallback email if no department head is found
        }
    }
}
