<?php

namespace App\Console\Commands;

use App\Mail\SubmissionCEOReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Tender;
use App\Models\TenderingUser;
use Carbon\Carbon;

class CheckCEOSubmissionDate extends Command
{
    protected $signature = 'check:submissionCEOdate';
    protected $description = 'Check submissionDate and send an email three days before';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
{
    Log::info('CheckSubmissionDate command started.');
    Log::info('Current date and time: ' . Carbon::now()->toDateTimeString());

    $tenders = Tender::whereDate('submissionDate', Carbon::now()->addDays(3)->toDateString())
                      ->get();

    if ($tenders->isEmpty()) {
        Log::info('No tenders found with submissionDate set to three days from now.');
    } else {
        foreach ($tenders as $tender) {
            Log::info('Processing tender ID: ' . $tender->id);

            $ceoEmail = 'nabhan@morganti.com.sa';

            try {
                Mail::to($ceoEmail)->send(new SubmissionCEOReminderNotification($tender));
                Log::info("SubmissionDate reminder email sent to {$ceoEmail} for tender ID {$tender->id}");
            } catch (\Exception $e) {
                Log::error("Failed to send SubmissionDate email for tender ID {$tender->id}: " . $e->getMessage());
            }
        }
    }

    Log::info('CheckSubmissionDate command finished.');
}

}
