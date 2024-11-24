<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EscalationActualInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $project;

    public function __construct($project)
    {
        $this->project = $project;
    }

    public function build()
    {
        return $this->subject('Escalation: Actual Invoice Value Still Not Filled')
                    ->view('emails.ActualInvoiceEscalation')
                    ->with([
                       'projectName' => $this->project->projectName,
                        'projectManagerName' => $this->project->projectManagerName ?? 'Unknown', // Handle potential nulls
                    ]);
    }
}
