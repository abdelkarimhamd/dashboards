<?php
namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Microsoft\Graph\Graph;
use App\Models\OutlookToken;
use App\Models\CRM\Activity;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OutlookController extends Controller
{
    public function storeToken(Request $request)
    {
        Log::info('Authorization Header:', [$request->header('Authorization')]);
        $userId = auth()->user()->id;

        Log::info('Authenticated User ID:', [$userId]);
        $request->validate(['accessToken' => 'required']);

        if (auth()->check()) {
            OutlookToken::updateOrCreate(
                ['user_id' => $userId],
                ['access_token' => $request->accessToken, 'expires_at' => Carbon::now()->addHour()]
            );

            // Call the command and pass the user ID
            Artisan::call('emails:fetch-and-create-activities', ['userId' => $userId]);

            return response()->json(['message' => 'Token stored successfully and command dispatched']);
        } else {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    }

    public function getEmails($userId)
    {
        Log::info('Fetching emails for user ID:', [$userId]);

        // Retrieve the access token from the database
        $token = OutlookToken::where('user_id', $userId)->first();

        // Check if the token exists and is still valid
        if (!$token || $token->expires_at < Carbon::now()) {
            Log::warning('Token expired or missing for user ID:', [$userId]);
            return ['error' => 'Token expired. Please re-authenticate.'];
        }

        $graph = new Graph();
        $graph->setAccessToken($token->access_token);

        try {
            // Fetch up to 10 Inbox messages
            $inboxResponse = $graph->createRequest("GET", "/me/mailFolders/inbox/messages?\$top=50")
                ->setReturnType(null)
                ->execute();
            $inboxMessages = $inboxResponse->getBody();
            if (is_string($inboxMessages)) {
                $inboxMessages = json_decode($inboxMessages, true)['value'];
            } else {
                $inboxMessages = $inboxMessages['value'];
            }

            // Fetch up to 10 Sent Items messages
            $sentResponse = $graph->createRequest("GET", "/me/mailFolders/sentitems/messages?\$top=50")
                ->setReturnType(null)
                ->execute();
            $sentMessages = $sentResponse->getBody();
            if (is_string($sentMessages)) {
                $sentMessages = json_decode($sentMessages, true)['value'];
            } else {
                $sentMessages = $sentMessages['value'];
            }

            // Process Inbox messages
            $inboxMessagesArray = $this->convertMessagesToArray($inboxMessages, 'inbox');

            // Process Sent Items messages
            $sentMessagesArray = $this->convertMessagesToArray($sentMessages, 'sentitems');

            Log::info('Emails fetched successfully for user ID:', [$userId]);

            // Merge messages
            $allMessages = array_merge($inboxMessagesArray, $sentMessagesArray);

            // Return the array directly instead of a response
            return $allMessages;

        } catch (\Exception $e) {
            Log::error('Failed to fetch emails:', ['user_id' => $userId, 'error' => $e->getMessage()]);
            return ['error' => 'Failed to fetch emails'];
        }
    }

    private function convertMessagesToArray($messages, $folderName)
    {
        $messagesArray = [];
        foreach ($messages as $message) {
            // Get 'from' address
            $fromEmail = $message['from']['emailAddress']['address'] ?? null;

            // Get 'to' addresses
            $toAddresses = [];
            if (isset($message['toRecipients']) && is_array($message['toRecipients'])) {
                foreach ($message['toRecipients'] as $recipient) {
                    if (isset($recipient['emailAddress']['address'])) {
                        $toAddresses[] = $recipient['emailAddress']['address'];
                    }
                }
            }

            $messagesArray[] = [
                'id' => $message['id'] ?? '',
                'subject' => $message['subject'] ?? '',
                'from' => $fromEmail,
                'to' => $toAddresses,
                'received_at' => $message['receivedDateTime'] ?? null,
                'body_preview' => $message['bodyPreview'] ?? '',
                'folder' => $folderName,
            ];
        }
        return $messagesArray;
    }

    public function createActivitiesFromEmails($userId)
    {
        Log::info('Creating activities from emails for user ID:', [$userId]);

        // Get the emails as an array
        $emails = $this->getEmails($userId);

        // Check if there was an error fetching emails
        if (isset($emails['error'])) {
            Log::warning('Error in email response:', ['error' => $emails['error']]);
            return response()->json(['error' => $emails['error']], 401);
        }

        foreach ($emails as $email) {
            // Now you can use array syntax
            if (Activity::where('email_id', $email['id'])->exists()) {
                Log::info("Activity already exists for email ID: {$email['id']}");
                continue; // Skip if it already exists
            }

            // Collect 'from' and 'to' addresses to check against contacts
            $contactEmails = [];
            if (isset($email['from'])) {
                $contactEmails[] = $email['from'];
            }
            if (isset($email['to']) && is_array($email['to'])) {
                $contactEmails = array_merge($contactEmails, $email['to']);
            }

            $dealId = null;
            $relatedContactEmail = null;
            foreach ($contactEmails as $contactEmail) {
                // Check if the contact email exists in your CRM
                $contact = \App\Models\CRM\Contact::where('email', $contactEmail)->first();
                if ($contact) {
                    $dealContact = \App\Models\CRM\DealContact::where('contact_id', $contact->id)->first();
                    if ($dealContact) {
                        $dealId = $dealContact->deal_id;
                        $relatedContactEmail = $contactEmail;
                        break; // Stop after finding a related deal
                    }
                }
            }

            if (!$dealId) {
                Log::warning('No related deal found for email:', [
                    'email_id' => $email['id'],
                    'contact_emails' => $contactEmails,
                ]);
                continue; // Skip if no related deal is found
            }

            Log::info('Creating activity for email:', [
                'subject' => $email['subject'],
                'contact_email' => $relatedContactEmail,
                'deal_id' => $dealId,
            ]);

            Activity::create([
                'type' => 'Email',
                'deal_id' => $dealId,
                'email_id' => $email['id'],
                'email_subject' => $email['subject'] ?? '',
                'email_from' => $email['from'],
                'email_to' => implode(', ', $email['to'] ?? []),
            'email_received_at' => Carbon::parse($email['received_at'])->format('Y-m-d H:i:s'),

                'description' => $email['body_preview'] ?? '',
                'user_id' => $userId,
            ]);
        }

        Log::info('Activities created successfully from emails for user ID:', [$userId]);
        return response()->json(['message' => 'Activities created']);
    }
}
