<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailRetrievalService;

class FetchEmailsFromCrLint extends Command
{
    protected $signature = 'emails:fetch-from-crlint';
    protected $description = 'Fetch new emails from CRLint CRM';

    protected $emailService;

    public function __construct(EmailRetrievalService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
    }

    public function handle()
    {
        $this->info('Fetching emails from CRLint...');
        $this->emailService->fetchAndSaveEmails();
        $this->info('Emails fetched and saved successfully!');
    }
}
