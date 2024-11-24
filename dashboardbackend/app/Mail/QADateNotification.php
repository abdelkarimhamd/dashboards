<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Tender;

class QADateNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $tender;
    public $userName;

    /**
     * Create a new message instance.
     *
     * @param Tender $tender
     */
    public function __construct(Tender $tender, $userName)
    {
        $this->tender = $tender;
        $this->userName = $userName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.qadate_notification')
                    ->with([
                        'tenderTitle' => $this->tender->tenderTitle,
                        'Q_ADate' => $this->tender->Q_ADate,
                        'userName' => $this->userName,
                    ])
                    ->subject('Q&A Date Reminder');
    }
}