<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenderGoNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $userName;

    /**
     * Create a new message instance.
     *
     * @param array $data
     */
    public function __construct($data, $userName)
    {
        $this->data = $data;
        $this->userName = $userName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.tenderGONotification')
                    ->subject('Tender marked as Go Notification')
                    ->with([
                        'data' => $this->data,
                        'userName' => $this->userName,
                    ]);
    }
}
