<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Tender;

class RfpSubmittedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $tender;

    /**
     * Create a new message instance.
     *
     * @param Tender $tender
     */
    public function __construct(Tender $tender)
    {
        $this->tender = $tender;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.rfp_submitted')
                    ->with([
                        'tenderTitle' => $this->tender->tenderTitle,
                    ])
                    ->subject('RFP Document Submitted');
    }
}