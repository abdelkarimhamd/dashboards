<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EscalationSalaryMail extends Mailable
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
        return $this->subject('Escalation: Salary Value Still Not Filled')
                    ->view('emails.SalaryEscalation')
                    ->with([
                       'projectName' => $this->project->projectName,
                        'projectManagerName' => $this->project->projectManagerName ?? 'Unknown', // Handle potential nulls
                        'financeUserName' => $this->financeUserName,
                    ]);
    }
}
