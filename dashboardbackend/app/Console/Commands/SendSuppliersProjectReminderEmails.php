<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Header;
use App\Models\SuppliersMonthlyBase;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Mail\EscalationSuppliersMail;
use App\Mail\ReminderSuppliersMail;
use App\Mail\FinalSuppliersEscalationMail;
class SendSuppliersProjectReminderEmails extends Command
{
    protected $signature = 'send:send-suppliers-project-reminder-emails';
    protected $description = 'Send email reminders to human resources users if salary value is not filled';

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
            $actualValue = SuppliersMonthlyBase::where('projectId', $project->id)
                ->where('year', $currentYear)
                ->first();


            // Ensure we check the correct month column
            if ($actualValue && isset($actualValue->{$currentMonthAbbreviation}) && $actualValue->{$currentMonthAbbreviation} == 0) {
                $daysSinceStartOfMonth = Carbon::now()->day;

                // Get finance role users' emails and names
                $financeUsers = $this->getFinanceUsers($project->branch);

                foreach ($financeUsers as $financeUser) {
                    if ($daysSinceStartOfMonth == 5) {
                        Mail::to($financeUser->email)->send(new ReminderSuppliersMail($project, $financeUser->name));
                    } elseif ($daysSinceStartOfMonth == 8) {
                        Mail::to($financeUser->email)->send(new EscalationSuppliersMail($project, $financeUser->name));
                    } elseif ($daysSinceStartOfMonth == 10) {
                        Log::info($financeUser->name);
                        Mail::to($financeUser->email)
                            ->cc($this->getCeoEmail())
                            ->send(new FinalSuppliersEscalationMail($project, $financeUser->name));
                    }
                }
            }
        }
    }

    private function getFinanceUsers($branch)
    {
        // Get all users with the finance role
        $financeUsers = User::where('role', 'Procurement')
                            ->where('branch', $branch)
                            ->get();
        Log::info($financeUsers);
        return $financeUsers;
    }

    private function getCeoEmail()
    {
        // Assuming CEO's email is stored in a configuration or environment variable
        return config('mail.ceo_email', 'nabhan@morganti.com.sa');
    }
    
}