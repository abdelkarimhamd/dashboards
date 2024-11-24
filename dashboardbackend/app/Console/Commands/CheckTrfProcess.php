<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Tender;
use App\Models\TenderingUser;
use App\Mail\TrfProcessNotification;
use Carbon\Carbon;

class CheckTrfProcess extends Command
{
    protected $signature = 'check:trfprocess';
    protected $description = 'Check trfProcess and send an email daily starting 7 days before the submission date';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Log::info('CheckTrfProcess command started.');
        Log::info('Current date and time: ' . Carbon::now()->toDateTimeString());

        $tenders = Tender::where('trfProcess', 1)
                          ->whereDate('submissionDate', '>=', Carbon::now()->toDateString())
                          ->whereDate('submissionDate', '<=', Carbon::now()->addDays(7)->toDateString())
                          ->get();

        if ($tenders->isEmpty()) {
            Log::info('No tenders found with trfProcess set to 1 and submissionDate within the next 7 days.');
        } else {
            foreach ($tenders as $tender) {
                Log::info('Processing tender ID: ' . $tender->id);

                // Check for user based on role and branch
                $users = TenderingUser::where('role', 'Business Development')
                    ->where(function($query) use ($tender) {
                        if ($tender->branch === 'UAE') {
                            $query->where('branch', 'UAE');
                        } elseif ($tender->branch === 'KSA') {
                            $query->where('branch', 'KSA');
                        }
                    })
                    ->get();

                if ($users->isEmpty()) {
                    Log::warning("No users found with role: Business Development and branch: {$tender->branch}");
                } else {
                    foreach ($users as $user) {
                        try {
                            Mail::to($user->email)->send(new TrfProcessNotification($tender, $user->name));
                            Log::info("TrfProcess notification email sent to {$user->email} for tender ID {$tender->id}");
                        } catch (\Exception $e) {
                            Log::error("Failed to send TrfProcess email for tender ID {$tender->id}: " . $e->getMessage());
                        }
                    }
                }
            }
        }

        Log::info('CheckTrfProcess command finished.');
    }
}
