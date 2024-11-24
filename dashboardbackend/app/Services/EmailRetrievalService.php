<?php

namespace App\Services;

use App\Models\CRM\Activity;
use Illuminate\Support\Facades\Http;

class EmailRetrievalService
{
    protected $crlintApiUrl;
    protected $crlintApiToken;

    public function __construct()
    {
        $this->crlintApiUrl = config('services.crlint.api_url'); // Define CRLint API URL in config
        $this->crlintApiToken = config('services.crlint.api_token'); // Define CRLint API token in config
    }

    /**
     * Retrieve and save emails from CRLint CRM
     */
    public function fetchAndSaveEmails()
    {
        // Example API call to CRLint
        $response = Http::withToken($this->crlintApiToken)->get("{$this->crlintApiUrl}/emails");

        if ($response->successful()) {
            $emails = $response->json();

            foreach ($emails as $email) {
                $this->storeEmailAsActivity($email);
            }
        }
    }

    /**
     * Store an email as an Activity if it doesn't already exist
     */
    protected function storeEmailAsActivity(array $email)
    {
        $existingActivity = Activity::where('email_from', $email['from'])
            ->where('email_received_at', $email['received_at'])
            ->first();

        if (!$existingActivity) {
            Activity::create([
                'type' => 'email',
                'description' => $email['body'] ?? 'No description available',
                'outcome' => 'Received', // default outcome, adjust as needed
                'activity_date' => $email['received_at'],
                'company_id' => $email['company_id'] ?? null,
                'lead_id' => $email['lead_id'] ?? null,
                'user_id' => $email['user_id'] ?? null,
                'email_from' => $email['from'],
                'email_subject' => $email['subject'],
                'email_received_at' => $email['received_at'],
                'deal_id' => $email['deal_id'] ?? null,
            ]);
        }
    }
}
