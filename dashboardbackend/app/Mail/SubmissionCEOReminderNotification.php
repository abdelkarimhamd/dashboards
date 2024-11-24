<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Tender;

class SubmissionCEOReminderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $tender;

    /**
     * Create a new message instance.
     *
     * @return void
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
        return $this->view('emails.submissionCEOreminder')
                    ->subject('Tender Submission Reminder')
                    ->with([
                        'tenderTitle' => $this->tender->tenderTitle,
                        'submissionDate' => $this->tender->submissionDate,
                    ]);
    }
}
