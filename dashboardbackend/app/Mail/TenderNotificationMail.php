<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\TenderFile;
use Illuminate\Support\Facades\Log;

class TenderNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $userName;

    /**
     * Create a new message instance.
     *
     * @param array $data
     * @param string $userName
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
        try {
      
            
    
            $email = $this->view('emails.tenderNotification')
                          ->subject('New Tender Notification')
                          ->with([
                              'data' => $this->data,
                              'userName' => $this->userName,
                          ]);
    
            if (isset($this->data['id'])) {
                $tenderId = $this->data['id'];
                Log::info('Tender ID:', ['tender_id' => $tenderId]);
    
                $tenderFiles = TenderFile::where('tender_id', $tenderId)->get();
    
                foreach ($tenderFiles as $file) {
                    $filePath = storage_path('app/public/' . $file->path);
                    Log::info('Checking file existence:', ['file_path' => $filePath]);
    
                    if (file_exists($filePath)) {
                        $email->attach($filePath, [
                            'as' => $file->name,
                            'mime' => mime_content_type($filePath),
                        ]);
                        Log::info('File attached successfully:', ['file_path' => $filePath]);
                    } else {
                        Log::warning('File does not exist:', ['file_path' => $filePath]);
                    }
                }
            }
    
            return $email;
        } catch (\Exception $e) {
            Log::error('Exception in TenderNotificationMail build(): ' . $e->getMessage());
            throw $e; // Re-throw the exception after logging it
        }
    }
    
}
