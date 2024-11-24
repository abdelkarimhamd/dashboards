<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Tender;
use App\Models\TenderingUser;
use App\Mail\SiteVisitDateNotification;
use Carbon\Carbon;

class CheckSiteVisitDate extends Command
{
    protected $signature = 'check:sitevisitdate';
    protected $description = 'Check siteVisitDate and send an email two days before';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Log::info('CheckSiteVisitDate command started.');
        Log::info('Current date and time: ' . Carbon::now()->toDateTimeString());

        $tenders = Tender::whereDate('siteVisitDate', Carbon::now()->addDays(2)->toDateString())->get();

        if ($tenders->isEmpty()) {
            Log::info('No tenders found with siteVisitDate set to two days from now.');
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
                            Mail::to($user->email)->send(new SiteVisitDateNotification($tender, $user->name));
                            Log::info("SiteVisitDate notification email sent to {$user->email} for tender ID {$tender->id}");
                        } catch (\Exception $e) {
                            Log::error("Failed to send SiteVisitDate email for tender ID {$tender->id}: " . $e->getMessage());
                        }
                    }
                }
            }
        }

        Log::info('CheckSiteVisitDate command finished.');
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

