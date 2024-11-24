<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReminderMail;
use Carbon\Carbon;

class SendEmailReminder extends Command
{
    protected $signature = 'email:send-reminder';
    protected $description = 'Send email reminder every minute';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Executing the deadline reminder command.');

        $currentDateAndTime = Carbon::now()->format('Y-m-d H:i');
        $this->info($currentDateAndTime);

        if ($currentDateAndTime === "2024-05-30 16:10") {
            Mail::to('doniaahmad09@gmail.com')->send(new ReminderMail());
            $this->info('Deadline reminder email sent.');
        }
    }
}