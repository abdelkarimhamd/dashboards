<?php

namespace App\Http\Controllers\TenderingControllers;

use App\Http\Controllers\Controller;
use App\Mail\TenderGoNotificationMail;
use Illuminate\Http\Request;
use App\Models\Tender;
use App\Models\TenderingUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\TenderNotificationMail;
use App\Mail\TenderNotToGONotificationMail;
use App\Models\FactSheet;

class TenderingDecisionController extends Controller
{
    public function goDecision($id)
    {
        try {
            $factSheet = FactSheet::where('tender_id', $id)->first();

            if (!$factSheet) {
                return response()->json(['message' => 'Fact Sheet not found for this tender.'], 404);
            }
            $tender = Tender::findOrFail($id);
            if ($tender->tender_value > 30000000 && $tender->status == "awaiting ceo decision") {
                $tender->status = 'awaiting managing director decision';
            } else {
                $tender->gono = 1;

                $tender->status = 'in progress';
            }
            $tender->save();
            Log::info("Tender ID {$id} marked as Go.");

            $businessDevelopmentUsers = TenderingUser::where('role', 'Business Development')->get();
            foreach ($businessDevelopmentUsers as $user) {
                try {
                    $data = [
                        'tenderTitle' => $tender->tenderTitle,
                        'selectedOption' => $tender->selectedOption, // Include the required field
                        'location' => $tender->location,
                        'sourceOption' => $tender->sourceOption,
                        'tender_value' => $tender->tender_value,
                        'message' => 'The tender for the project "' . $tender->tenderTitle . '" has been marked as "Go".',
                        'link' => 'http://app.morgantigcc.com:3001/tenderingDashboard'
                    ];

                    Mail::to($user->email)->send(new TenderGoNotificationMail($data, $user->name));

                    Log::info("Notification email sent to {$user->email} for tender titled {$tender->tenderTitle}");
                } catch (\Exception $e) {
                    Log::error("Failed to send notification email to {$user->email} for tender titled {$tender->tenderTitle}: " . $e->getMessage());
                }
            }
            // Redirect to dashboard and set `showAdditionalFields` to true
            return response()->json(['message' => 'Tender marked as Go']);
        } catch (\Exception $e) {
            Log::error("Failed to update tender ID {$id}: " . $e->getMessage());
            return response()->json(['message' => 'Failed to mark tender as Go', 'error' => $e->getMessage()], 500);
        }
    }

    public function notToGoDecision($id)
    {
        try {
            $tender = Tender::findOrFail($id);
            $tender->gono = 0;
            $tender->status = 'rejected';
            $tender->save();
            Log::info("Tender ID {$id} marked as Not to Go.");

            // Send notification to Business Development users
            $businessDevelopmentUsers = TenderingUser::where('role', 'Business Development')->get();
            foreach ($businessDevelopmentUsers as $user) {
                try {
                    $data = [
                        'tenderTitle' => $tender->tenderTitle,
                        'selectedOption' => $tender->selectedOption, // Include the required field
                        'location' => $tender->location,
                        'sourceOption' => $tender->sourceOption,
                        'tender_value' => $tender->tender_value,
                        'message' => 'The tender for the project "' . $tender->tenderTitle . '" has been marked as "Not to Go".',
                        'link' => 'http://app.morgantigcc.com:3001/tenderingDashboard'
                    ];

                    Mail::to($user->email)->send(new TenderNotToGONotificationMail($data, $user->name));
                    Log::info("Notification email sent to {$user->email} for tender titled {$tender->tenderTitle}");
                } catch (\Exception $e) {
                    Log::error("Failed to send notification email to {$user->email} for tender titled {$tender->tenderTitle}: " . $e->getMessage());
                }
            }

            return response()->json(['message' => 'Tender marked as Not to Go']);
        } catch (\Exception $e) {
            Log::error("Failed to update tender ID {$id}: " . $e->getMessage());
            return response()->json(['message' => 'Failed to mark tender as Not to Go', 'error' => $e->getMessage()], 500);
        }
    }


    public function getGonoValue($id)
    {
        try {

            $tender = Tender::findOrFail($id);
            return response()->json(['gono' => $tender->status], 200);
        } catch (\Exception $e) {
            Log::error("Failed to fetch gono value for tender ID {$id}: " . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch gono value', 'error' => $e->getMessage()], 500);
        }
    }
}
