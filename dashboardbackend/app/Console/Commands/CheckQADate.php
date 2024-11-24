<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Tender;
use App\Models\TenderingUser;
use App\Mail\QADateNotification;
use Carbon\Carbon;

class CheckQADate extends Command
{
    protected $signature = 'check:qadate';
    protected $description = 'Check Q_ADate and send an email one day before';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Log::info('CheckQADate command started.');
        Log::info('Current date and time: ' . Carbon::now()->toDateTimeString());

        $tenders = Tender::whereDate('Q_ADate', Carbon::tomorrow())->get();

        if ($tenders->isEmpty()) {
            Log::info('No tenders found with Q_ADate set to tomorrow.');
        } else {
            foreach ($tenders as $tender) {
                Log::info('Processing tender ID: ' . $tender->id);

                $roles = $this->getRolesBySelectedOption($tender->selectedOption);
                foreach ($roles as $role) {
                    // Filter users by role and branch
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
                            Mail::to($user->email)->send(new QADateNotification($tender, $user->name));
                            Log::info("QADate notification email sent to {$user->email} for tender ID {$tender->id}");
                        } catch (\Exception $e) {
                            Log::error("Failed to send QADate email for tender ID {$tender->id}: " . $e->getMessage());
                        }
                    }
                }
            }
        }

        Log::info('CheckQADate command finished.');
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
