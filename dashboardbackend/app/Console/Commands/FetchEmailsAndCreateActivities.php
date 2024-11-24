<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\CRM\OutlookController;

class FetchEmailsAndCreateActivities extends Command
{
    protected $signature = 'emails:fetch-and-create-activities {userId}';


    protected $description = 'Fetch check and create activities for related deals';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $userId = $this->argument('userId'); // Retrieve userId as expected

        if (!$userId) {
            $this->error('User ID is missing.');
            return;
        }

        $this->info("Creating activities from emails for user ID: $userId");

        // Instantiate OutlookController
        $outlookController = new OutlookController();

        // Call the method to fetch emails and create activities, passing the userId
        $response = $outlookController->createActivitiesFromEmails($userId);

        $this->info('Emails fetched and activities created. ' . $response);
    }


}
