<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReminderSalaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $project;
    public $financeUserName;
    public function __construct($project, $financeUserName)
    {
        $this->project = $project;
        $this->financeUserName = $financeUserName;
    }


    public function build()
    {
        return $this->subject('Reminder: Salary Value Not Filled')
                    ->view('emails.SalaryReminder')
                    ->with([
                        'projectName' => $this->project->projectName,
                        'projectManagerName' => $this->project->projectManagerName ?? 'Unknown', // Handle potential nulls
                        'financeUserName' => $this->financeUserName,
                    ]);
    }
}
