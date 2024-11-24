<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tender;
use App\Mail\ExtensionDateNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\TenderingUser;

class SendExtensionDateNotifications extends Command
{
    protected $signature = 'send:extension-date-notifications';
    protected $description = 'Send email notifications for tenders with extension dates';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $tenders = Tender::whereNotNull('extinsionDate')->get();
        
        foreach ($tenders as $tender) {
            // Your logic to send email notifications
            $roles = [
                'Project Management' => ['PMC', 'PMC & CS', 'PMC, CS & Design'],
                'Fit-out' => ['FO', 'FOM', 'DNP'],
                'Facility Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM','PMC', 'PMC & CS', 'PMC, CS & Design'],
                'Asset Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM','PMC', 'PMC & CS', 'PMC, CS & Design']
            ];

            foreach ($roles as $role => $options) {
                if (in_array($tender->selectedOption, $options)) {
                    // Check for user branch in addition to role
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
                            Mail::to($user->email)->send(new ExtensionDateNotification($tender, $user->name));
                            Log::info("ExtensionDate notification email sent to {$user->email} for tender ID {$tender->id}");
                        } catch (\Exception $e) {
                            Log::error("Failed to send ExtensionDate email for tender ID {$tender->id}: " . $e->getMessage());
                        }
                    }
                }
            }
        }
        
        $this->info('Extension date notifications sent successfully!');
    }
}
