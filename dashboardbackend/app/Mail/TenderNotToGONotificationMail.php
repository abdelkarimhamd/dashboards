<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TenderNotToGONotificationMail extends Mailable
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
        return $this->view('emails.tenderNotToGONotification')
                    ->subject('Tender Notification')
                    ->with([
                        'data' => $this->data,
                        'userName' => $this->userName,
                    ]);
    }
}

