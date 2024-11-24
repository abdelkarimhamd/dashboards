<?php

namespace App\Http\Controllers\TenderingControllers;

use App\Http\Controllers\Controller;
use App\Models\Tender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\TenderingUser;

class TRFController extends Controller
{
    public function getAssignedTRF()
    {
        $user = Auth::user();
        $facilityManagementOptions = ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'];
        $assetManagementOptions = ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'];
        $fitout = ['FO', 'FOM', 'DNP'];
        $projectmanger=['PMC', 'PMC & CS', 'PMC, CS & Design'];

        // Determine which tenders to retrieve based on the user's role
        $tendersQuery = Tender::query();

        switch ($user->role) {



            case 'Business Development':
                $tendersQuery->where('status', 'in progress');
                break;
            case 'Fit-out':
                $tendersQuery->where('status', 'TRF Fit-out review');
                break;
            case 'Project Management':
                $tendersQuery->where('status', 'TRF project management review');
                break;

            case 'Facility Management':
                $tendersQuery->where(function ($query) use ($facilityManagementOptions, $assetManagementOptions) {
                    $query->where('status', 'TRF facility management review')
                        ->orWhere(function ($query) use ($facilityManagementOptions, $assetManagementOptions) {
                            $query->where('status', 'TRF facility and asset management review')
                                ->whereIn('selectedOption', $facilityManagementOptions)
                                ->whereIn('selectedOption', $assetManagementOptions);
                        });
                });
                break;

            case 'Asset Management':
                $tendersQuery->where(function ($query) use ($assetManagementOptions, $facilityManagementOptions) {
                    $query->where('status', 'TRF  asset review')->orWhere('status', 'TRF asset management review')
                        ->orWhere(function ($query) use ($assetManagementOptions, $facilityManagementOptions) {
                            $query->where('status', 'TRF facility and asset management review')
                                ->whereIn('selectedOption', $assetManagementOptions)
                                ->whereIn('selectedOption', $facilityManagementOptions);
                        });
                });
                break;

            case 'Executives (CEO)':
                $tendersQuery->where('status', 'TRF ceo review');
                break;

            case 'Executives Managing Director':
                $tendersQuery->where('status', 'TRF Executive Managing Director review');
                break;
            case 'President':
                $tendersQuery->where('status', 'TRF President review');
                break;

            default:
                return response()->json([
                    'message' => 'No tenders available for your role.'
                ], 403);
        }

        

            // Count the number of TRFs
            $trfCount = $tendersQuery->count();
          

        // Eager load the created_by and updated_by relationships
        $tenders = $tendersQuery->select('id', 'tenderTitle', 'branch', 'created_by', 'updated_by', 'updated_at', 'status')
            ->with([
                'createdBy' => function ($query) {
                    $query->select('id', 'name');
                },
                'updatedBy' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
            ->get()
            ->map(function ($tender) {
                return [
                    'id' => $tender->id,
                    'tenderTitle' => $tender->tenderTitle,
                    'created_by_name' => $tender->createdBy->name ?? 'Unknown',
                    'updated_by_name' => $tender->updatedBy->name ?? 'Unknown',
                    'updated_at' => $tender->updated_at,
                    'branch' => $tender->branch,
                    'status' => $tender->status,
                ];
            });

        return response()->json(['tenders' => $tenders, 'trfCount' => $trfCount], 200);
    }

    

    public function rejectTRFStatus($id, Request $request)
    {
        $user = Auth::user();
        $tender = Tender::findOrFail($id);

        // Validate that the rejection reason is optional but must be a string if provided
        $request->validate([
            'rejection_reason' => 'nullable|string|max:255'
        ]);

        $tenderOldStatus = $tender->status;

        // Update the tender status to 'rejected'
        $tender->status = 'TRF rejected';

        // Set the rejection reason if provided
        if ($request->has('rejection_reason')) {
            $tender->rejection_reason = $request->input('rejection_reason');
        }

        $tender->rejected_by = $user->name;
        $tender->rejected_at = now(); // Capture the time of rejection
        $tender->save();

        // Log the rejection for audit purposes, including the rejection reason if provided
        Log::info("Tender ID {$tender->id} was rejected by {$user->name} with reason: " . ($tender->rejection_reason ?? 'No reason provided'));

        // Optionally, send an email notification about the rejection
        $this->sendStatusChangeNotification($tender, $tenderOldStatus, $tender->status);

        return response()->json(['message' => 'Tender rejected successfully.'], 200);
    }

    public function updateTRFStatus($id)
    {

        $user = Auth::user();
        $tender = Tender::findOrFail($id);
        $facilityManagementOptions = ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'];
        $assetManagementOptions = ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'];
        $fitoutOptions = ['FO', 'FOM', 'DNP'];
        $projectMangementOptions = ['PMC', 'PMC & CS', 'PMC, CS & Design'];

        $newStatus = '';
        // $valueChanged = $request->input('tender_value') != $tender->tender_value;
        // $durationChanged = $request->input('contractPeriod') != $tender->contractPeriod;

        // if ($valueChanged || $durationChanged) {
        //     // If value or duration changed, reset status to relevant review stage
        //     return $this->($tender, $user, $facilityManagementOptions, $assetManagementOptions, $fitoutOptions);
        // } else {
        //     // Normal approval flow
        //     return $this->($tender, $user, $facilityManagementOptions, $assetManagementOptions, $fitoutOptions);
        // }
        switch ($tender->status) {
            case 'in progress':
                if (in_array($tender->selectedOption, $facilityManagementOptions) && in_array($tender->selectedOption, $assetManagementOptions)) {
                    $newStatus = 'TRF facility and asset management review';
                } elseif (in_array($tender->selectedOption, $facilityManagementOptions)) {
                    $newStatus = 'TRF facility management review';
                } elseif (in_array($tender->selectedOption, $assetManagementOptions)) {
                    $newStatus = 'TRF asset management review';
                } elseif (in_array($tender->selectedOption, $fitoutOptions)) {
                    $newStatus = 'TRF Fit-out review';
                } elseif (in_array($tender->selectedOption, $projectMangementOptions)) {
                    $newStatus = 'TRF project management review';
                }
                break;

            case 'TRF facility management review':
                if (in_array($tender->selectedOption, $facilityManagementOptions)) {
                    $newStatus = 'TRF ceo review';
                }
                break;

            case 'TRF asset management review':
                if (in_array($tender->selectedOption, $assetManagementOptions)) {
                    $newStatus = 'TRF ceo review';
                }
                break;

            case 'TRF Fit-out review':
                $newStatus = 'TRF ceo review';
                break;
            case 'TRF project management review':
                $newStatus = 'TRF ceo review';
                break;

            case 'TRF facility and asset management review':
                $newStatus = 'TRF ceo review';
                break;

            case 'TRF ceo review':
                $newStatus = 'TRF Executive Managing Director review';
                break;

            case 'TRF Executive Managing Director review':
                if ($tender->tender_value > 5000000) {
                    $newStatus = 'TRF President review';
                    $this->sendReminderToCEOAndExecutiveDirector($tender);
                } else
                    $newStatus = 'TRF completed';
                break;

            case 'TRF President review':

                $newStatus = 'TRF completed';



                break;

            default:
                return response()->json([
                    'message' => 'Invalid status transition or unauthorized action.'
                ], 403);
        }

        if ($newStatus) {
            $previousStatus = $tender->status;
            $tender->status = $newStatus;
            $tender->approved_by = $user->name;
            if ($newStatus === "TRF completed") {
                $tender->trfProcess = true;
            }
            $tender->save();
            $this->sendStatusChangeNotification($tender, $previousStatus, $newStatus);

            return response()->json(['message' => 'Tender status updated successfully.', 'status' => $tender->status], 200);
        } else {
            return response()->json([
                'message' => 'No valid status update available.'
            ], 400);
        }
    }




    private function sendReminderToCEOAndExecutiveDirector($tender)
    {
        // Get the CEO and Executive Director
        $ceo = TenderingUser::where('role', 'Executives (CEO)')->first();
        $executiveDirector = TenderingUser::where('role', 'Executives Managing Director')->first();

        if ($ceo && $executiveDirector) {
            // Prepare email data, including the recipient's name
            $emailData = [
                'tender' => $tender,
                'ceoName' => $ceo->name,
                'executiveDirectorName' => $executiveDirector->name
            ];

            // Send the reminder email
            Mail::send('emails.CEO_Executive_Reminder', $emailData, function ($message) use ($ceo, $executiveDirector) {
                $message->to([$ceo->email, $executiveDirector->email])
                    ->subject('Reminder: President’s action required on tender');
            });

            return response()->json(['success' => 'Reminder email sent to CEO and Executive Director.']);
        } else {
            return response()->json(['error' => 'CEO or Executive Director not found'], 404);
        }
    }
    private function sendStatusChangeNotification($tender, $previousStatus, $newStatus)
    {
        $targetedUsers = $this->getTargetedUsersByRole($newStatus, $tender);

        if ($targetedUsers->isNotEmpty()) {
            $president = $targetedUsers->firstWhere('role', 'President');
            $ccUsers = $targetedUsers->whereIn('role', ['Executives (CEO)', 'Executives Managing Director']);

            foreach ($targetedUsers as $targetedUser) {
                // Prepare email data
                $emailData = [
                    'tender' => $tender,
                    'previousStatus' => $previousStatus,
                    'newStatus' => $newStatus,
                    'targetedUser' => $targetedUser,
                ];

                // Select email template based on new status
                $emailTemplate = $this->getEmailTemplateByStatus($newStatus);
                $emailSubject = $this->getEmailSubjectByStatus(newStatus: $newStatus);

                // Send email to each targeted user
                Mail::send($emailTemplate, $emailData, function ($message) use ($targetedUser, $ccUsers, $emailSubject) {
                    $message->to($targetedUser->email)
                        ->subject($emailSubject);

                    // Add CEO and Executive Director to CC if the role is President
                    if ($targetedUser->role == 'President') {
                        foreach ($ccUsers as $ccUser) {
                            $message->cc($ccUser->email);
                        }
                    }
                });

                Log::info("Email notification sent to " . $targetedUser->email);
            }
        } else {
            Log::warning("No targeted users found for the status: " . $newStatus);
        }
    }

    private function getEmailTemplateByStatus($newStatus)
    {
        switch ($newStatus) {
            case 'TRF completed':
                return 'emails.trf_completed'; // Template for TRF completed
                // Add other cases as needed for different statuses
            default:
                return 'emails.status_change'; // Default template
        }
    }
    private function getEmailSubjectByStatus($newStatus)
    {
        switch ($newStatus) {
            case 'TRF completed':
                return 'Tender Process Completed';
                // Add other cases as needed for different statuses
            default:
                return 'Tender Status Updated';
        }
    }
    private function getTargetedUsersByRole($newStatus, $tender)
    {
        // Log::info($newStatus,"testttt");
        switch ($newStatus) {
            case 'TRF facility and asset management review':
                return TenderingUser::whereIn('role', ['Facility Management', 'Asset Management'])->get();
            case 'TRF facility management review':
                return TenderingUser::where('role', 'Facility Management')->get();
            case 'TRF asset management review':
                // Log::info("message");
                return TenderingUser::where('role', 'Asset Management')->get();
            case 'TRF Fit-out review':
                return TenderingUser::where('role', 'Fit-out')->get();
            case 'TRF ceo review':
                return TenderingUser::where('role', 'Executives (CEO)')->get();
            case 'TRF Executive Managing Director review':
                return TenderingUser::where('role', 'Executives Managing Director')->get();
            case 'TRF Project Management review':
                return TenderingUser::where('role', 'Project Management')->get();
            case 'TRF President review':
                return TenderingUser::whereIn('role', ['President', 'Executives (CEO)', 'Executives Managing Director'])->get();
            case 'TRF completed':
                $users = collect();

                // Fetch and merge all Executive Managers, CEOs, and Business Development users
                $executiveManagers = TenderingUser::where('role', 'Executives Managing Director')->get();
                $ceos = TenderingUser::where('role', 'Executives (CEO)')->get();
                $bd = TenderingUser::where('role', 'Business Development')->get();

                if ($executiveManagers->isNotEmpty()) {
                    $users = $users->merge($executiveManagers);
                }
                if ($ceos->isNotEmpty()) {
                    $users = $users->merge($ceos);
                }
                if ($bd->isNotEmpty()) {
                    $users = $users->merge($bd);
                }

                // Determine the department heads based on the main scope
                $mainScope = $tender->selectedOption;
                $facilityManagementOptions = ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'];
                $assetManagementOptions = ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'];
                $projectmanger=['PMC', 'PMC & CS', 'PMC, CS & Design'];

                $fitoutOptions = ['FO', 'FOM', 'DNP'];

                $departmentHeads = collect(); // Initialize as an empty collection

                // Merge users for Fit-out, Asset Management, and Facility Management if applicable
                if (in_array($mainScope, $fitoutOptions)) {
                    $departmentHeads = $departmentHeads->merge(TenderingUser::where('role', 'Fit-out')->get());
                }
                if (in_array($mainScope, $assetManagementOptions)) {
                    $departmentHeads = $departmentHeads->merge(TenderingUser::where('role', 'Asset Management')->get());
                }
                if (in_array($mainScope, $facilityManagementOptions)) {
                    $departmentHeads = $departmentHeads->merge(TenderingUser::where('role', 'Facility Management')->get());
                }
                if (in_array($mainScope, $projectmanger)) {
                    $departmentHeads = $departmentHeads->merge(TenderingUser::where('role', 'Project Management')->get());
                }

                // Merge the department heads into the main users collection
                if ($departmentHeads->isNotEmpty()) {
                    $users = $users->merge($departmentHeads);
                }

                return $users;

            default:
                return collect();
        }
    }
}
