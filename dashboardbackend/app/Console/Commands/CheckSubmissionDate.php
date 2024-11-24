<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Tender;
use App\Models\TenderingUser;
use App\Mail\SubmissionDateNotification;
use Carbon\Carbon;

class CheckSubmissionDate extends Command
{
    protected $signature = 'check:submissiondate';
    protected $description = 'Check submissionDate and send an email seven days before if rfpSubmitted is 0';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Log::info('CheckSubmissionDate command started.');
        Log::info('Current date and time: ' . Carbon::now()->toDateTimeString());

        $tenders = Tender::whereDate('submissionDate', Carbon::now()->addDays(7)->toDateString())
                          ->where('rfpSubmitted', 0)
                          ->get();

        if ($tenders->isEmpty()) {
            Log::info('No tenders found with submissionDate set to seven days from now where rfpSubmitted is 0.');
        } else {
            foreach ($tenders as $tender) {
                Log::info('Processing tender ID: ' . $tender->id);

                $roles = $this->getRolesBySelectedOption($tender->selectedOption);
                foreach ($roles as $role) {
                    // Check for users based on role and branch
                    $users = TenderingUser::where('role', $role)
                        ->where(function($query) use ($tender) {
                            if ($tender->branch === 'UAE') {
                                $query->where('branch', 'UAE');
                            } elseif ($tender->branch === 'KSA') {
                                $query->where('branch', 'KSA');
                            }
                        })
                        ->get();

                    if ($users->isEmpty()) {
                        Log::warning("No users found with role: $role and branch: {$tender->branch}");
                        continue;
                    }

                    foreach ($users as $user) {
                        try {
                            Mail::to($user->email)->send(new SubmissionDateNotification($tender, $user->name));
                            Log::info("SubmissionDate notification email sent to {$user->email} for tender ID {$tender->id}");
                        } catch (\Exception $e) {
                            Log::error("Failed to send SubmissionDate email for tender ID {$tender->id}: " . $e->getMessage());
                        }
                    }
                }
            }
        }

        Log::info('CheckSubmissionDate command finished.');
    }

    private function getRolesBySelectedOption($selectedOption)
    {
        $roleMapping = [
            'PMC' => ['Project Management','Facility Management', 'Asset Management'],
            'PMC & CS' => ['Project Management','Facility Management', 'Asset Management'],
            'PMC, CS & Design' => ['Project Management','Facility Management', 'Asset Management'],
            'FO' => ['Fit-out'],
            'FOM' => ['Fit-out'],
            'DNP' => ['Fit-out'],
            'FMMA' => ['Facility Management', 'Asset Management'],
            'AFM' => ['Facility Management', 'Asset Management'],
            'FMC' => ['Facility Management', 'Asset Management'],
            'TFM' => ['Facility Management', 'Asset Management'],
            'AM' => ['Facility Management', 'Asset Management']
        ];

        return $roleMapping[$selectedOption] ?? [];
    }
}
