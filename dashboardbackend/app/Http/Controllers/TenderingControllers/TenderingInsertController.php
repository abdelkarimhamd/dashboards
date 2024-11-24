<?php

namespace App\Http\Controllers\TenderingControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tender;
use Illuminate\Support\Facades\Mail;
use App\Mail\RfpSubmittedNotification;
use App\Mail\ExtensionDateNotification;
use App\Models\TenderingUser;
use Illuminate\Support\Facades\Log;
use App\Mail\TenderNotificationMail;
use App\Models\FactSheet;
use App\Models\TenderFile;
use App\Mail\TenderStatusChangeMail;
use App\Mail\TenderFinalStatusNotificationMail;
use App\Mail\TenderRejectionNotificationMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
class TenderingInsertController extends Controller
{


    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'tenderTitle' => 'required|string|max:255',
            'tenderNumber' => 'nullable|string|max:255',
            'employerName' => 'nullable|string|max:255',
            'location' => 'required|string|max:255',
            'branch' => 'required|string|max:255',
            'selectedOption' => 'required|string|max:255',
            'sourceOption' => 'required|string|max:255',
            'estimatedNbr' => 'nullable|integer',
            'companyPreQuilifiedOption' => 'nullable|string|max:255',
            'contactDuration' => 'nullable|string|max:255',
            'scopeServices' => 'nullable|string',
            'submissionDate' => 'nullable|date',
            'startDate' => 'nullable|date',
            'contractType' => 'nullable|string|max:255',
            'receivedDate' => 'nullable|date',
            'jobexDate' => 'nullable|date',
            'Q_ADate' => 'nullable|date',
            'extinsionDate' => 'nullable|date',
            'siteVisitDate' => 'nullable|date',
            'estimatedMargin' => 'nullable|numeric',
            'validityPeriod' => 'nullable|integer',
            'conditions' => 'nullable|string',
            'positionRecommendation' => 'nullable|string',
            'currencyOptions' => 'nullable|string|max:255',
            'performanceBond' => 'nullable|string|max:255',
            'retention' => 'nullable|string|max:255',
            'bid_bond' => 'nullable|string|max:255',
            'languageOptions' => 'nullable|string|max:255',
            'trfProcess' => 'nullable|boolean',
            'rfpSubmitted' => 'nullable|boolean',
            'rfpDocuments.*' => 'nullable|file|mimes:pdf,doc,docx,txt,jpg,png,jpeg,zip,rar,xlsx,msg',
            'projectName' => 'nullable|string|max:255',
            'companyOrPartnershipName' => 'nullable|string|max:255',
            'contractPeriod' => 'nullable|string|max:255',
            'dateTenderReceived' => 'nullable|date',
            'completed' => 'nullable|boolean',
            'lost' => 'nullable|boolean',
            'no_response' => 'nullable|boolean',
            'tender_value' => 'nullable|numeric',
            'hyperlink' => 'nullable|url|max:255',
            'projectSize'=> 'nullable|string|max:255',
            'probability' => 'nullable|numeric|min:0|max:100',
            'tender_logo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'tender_image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);
    
        // Handle the main tender creation process
        $validatedData['created_by'] = auth()->user()->id;
        $validatedData['updated_by'] = auth()->user()->id;
    
        // Determine the status based on the selected option
        $status = '';
        $fitOutOptions = ['FO', 'FOM', 'DNP'];
        $facilityManagementOptions = ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'];
        $assetManagementOptions = ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'];
        $projectMangementOptions = ['PMC', 'PMC & CS', 'PMC, CS & Design'];
    
        if (in_array($validatedData['selectedOption'], $fitOutOptions)) {
            $status = 'under review by coordinator fit-out';
        } elseif (in_array($validatedData['selectedOption'], $facilityManagementOptions)) {
            $status = 'under review by coordinator facility';
        } elseif (in_array($validatedData['selectedOption'], $facilityManagementOptions) && in_array($validatedData['selectedOption'], $assetManagementOptions)) {
            $status = 'under review by head of asset and facility';
        } elseif (in_array($validatedData['selectedOption'], $assetManagementOptions)) {
            $status = 'under review by head of asset';
        } elseif (in_array($validatedData['selectedOption'], $projectMangementOptions)) {
            $status = 'under review by project management';
        } else {
            return response()->json(['message' => 'Invalid selected option.'], 400);
        }
    
        $validatedData['status'] = $status;
    
        // Create the tender
        $tender = Tender::create($validatedData);
    
          // Handle file uploads for tender logo and tender project image, and store paths if uploaded
          if ($request->hasFile('tender_logo')) {
            // Get the uploaded file
            $logoFile = $request->file('tender_logo');
        
            // Define the path where you want to store the file in the public folder
            $logoPath = 'logos/' . time() . '_' . $logoFile->getClientOriginalName();
        
            // Move the file to the public path
            $logoFile->move(public_path('logos'), $logoPath);
        
            // Save the path in the validated data
            $validatedData['tender_logo'] = $logoPath;
        }
        
        if ($request->hasFile('tender_image')) {
            // Get the uploaded file
            $imageFile = $request->file('tender_image');
        
            // Define the path where you want to store the file in the public folder
            $imagePath = 'project_images/' . time() . '_' . $imageFile->getClientOriginalName();
        
            // Move the file to the public path
            $imageFile->move(public_path('project_images'), $imagePath);
        
            // Save the path in the validated data
            $validatedData['tender_image'] = $imagePath;
        }
    
    
        $tender->save(); // Save the updated tender with the file paths
    
        // Handle file uploads for RFP documents
        $rfpDocumentPaths = [];
        if ($request->hasFile('rfpDocuments')) {
            foreach ($request->file('rfpDocuments') as $file) {
                try {
                    $filePath = $file->store('documents', 'public');
                    $rfpDocumentPaths[] = $filePath;
    
                    // Optionally, store file info in a separate table
                    TenderFile::create([
                        'tender_id' => $tender->id,
                        'name' => $file->getClientOriginalName(),
                        'path' => $filePath,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error storing file:', ['message' => $e->getMessage()]);
                }
            }
        }
    
        // Update the tender with the RFP document paths
        $tender->update(['rfpdocument' => json_encode($rfpDocumentPaths)]);
    
        // Send extension date notification if needed
        if (!empty($validatedData['extinsionDate'])) {
            $this->sendExtensionDateNotification($tender);
        }
    
        // Send new tender notification
        $this->sendNewTenderNotification($request, $tender, $validatedData);
    
        if (isset($validatedData['no_response']) && $validatedData['no_response']) {
            $tender->no_response_start_date = now();
            $tender->save();
        }
    
        return response()->json(['message' => 'Tender created successfully.', 'rfpdocument' => $rfpDocumentPaths]);
    }



    public function getTenderFiles($tenderId)
    {
        // Find all files related to the tender ID
        $files = TenderFile::where('tender_id', $tenderId)->get();

        // If no files are found, return a message
        if ($files->isEmpty()) {
            return response()->json(['message' => 'No files found for this tender.'], 404);
        }

        // Create an array to store the file details
        $fileLinks = [];

        foreach ($files as $file) {
            $fileLinks[] = [
                'name' => $file->name,
                'url' => asset('storage/app/public/' . $file->path)
            ];
        }

        // Return the file links
        return response()->json(['files' => $fileLinks], 200);
    }
    public function getAllTitles()
    {
        // Get the current authenticated user
        $user = auth()->user();
        $userBranch = $user->branch;

        if ($userBranch === 'KSA & UAE') {
            // If branch is "KSA & UAE", show all tenders
            $tenders = Tender::select('id', 'tenderTitle', 'gono', 'status','canceled_reason','not_submitted_reason')
                ->distinct()
                ->get()
                ->map(function ($tender) {
                    return [
                        'id' => $tender->id,
                        'title' => $tender->tenderTitle,
                        'status' => $tender->status,
                        'canceled_reason' => $tender->canceled_reason ? $tender->canceled_reason:null ,
                        'not_submitted_reason' => $tender->not_submitted_reason ? $tender->not_submitted_reason:null ,

                        'gono' => $tender->gono // Include the gono field
                    ];
                });
        } else {
            // Otherwise, filter tenders by the user's branch
            $tenders = Tender::where('branch', $userBranch)
                ->select('id', 'tenderTitle', 'gono', 'status','canceled_reason','not_submitted_reason')
                ->distinct()
                ->get()
                ->map(function ($tender) {
                    return [
                        'id' => $tender->id,
                        'title' => $tender->tenderTitle,
                        'status' => $tender->status,
                        'canceled_reason' => $tender->canceled_reason ? $tender->canceled_reason:null ,
                        'not_submitted_reason' => $tender->not_submitted_reason ? $tender->not_submitted_reason:null ,

                        'gono' => $tender->gono // Include the gono field
                    ];
                });
        }

        return response()->json(['tenders' => $tenders], 200);
    }
    public function getTenderDataById($id)
    {

        $tenders = Tender::where('id', $id)->first();

        if (!$tenders) {
            return response()->json(['message' => 'Project not found'], 404);
        }


        return response()->json(['tenders' => $tenders], 200);
    }

    public function getRole()
    {
        $start = microtime(true);

        $user = auth()->user();
        $role = $user->role;

        $duration = microtime(true) - $start;

        return response()->json([
            'role' => $role,
        ], 200);
    }

    public function updateTenders(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'tenderTitle' => 'required|string|max:255',
            'tenderNumber' => 'nullable|string|max:255',
            'employerName' => 'nullable|string|max:255',
            'location' => 'required|string|max:255',
            'branch' => 'required|string|max:255',
            'selectedOption' => 'required|string|max:255',
            'sourceOption' => 'required|string|max:255',
            'estimatedNbr' => 'nullable|integer',
            'companyPreQuilifiedOption' => 'nullable|string|max:255',
            'contactDuration' => 'nullable|string|max:255',
            'scopeServices' => 'nullable|string',
            'submissionDate' => 'nullable|date',
            'startDate' => 'nullable|date',
            'contractType' => 'nullable|string|max:255',
            'receivedDate' => 'nullable|date',
            'jobexDate' => 'nullable|date',
            'Q_ADate' => 'nullable|date',
            'extinsionDate' => 'nullable|date',
            'siteVisitDate' => 'nullable|date',
            'estimatedMargin' => 'nullable|numeric',
            'validityPeriod' => 'nullable|integer',
            'conditions' => 'nullable|string',
            'positionRecommendation' => 'nullable|string',
            'currencyOptions' => 'nullable|string|max:255',
            'performanceBond' => 'nullable|string|max:255',
            'retention' => 'nullable|string|max:255',
            'bid_bond' => 'nullable|string|max:255',
            'languageOptions' => 'nullable|string|max:255',
            'trfProcess' => 'nullable|boolean',
            'rfpSubmitted' => 'nullable|boolean',
            'projectName' => 'nullable|string|max:255',
            'companyOrPartnershipName' => 'nullable|string|max:255',
            'contractPeriod' => 'nullable|string|max:255',
            'dateTenderReceived' => 'nullable|date',
            'status' => 'nullable|string',
            'financing' => 'nullable|string|max:255',
            'completed' => 'nullable|boolean',
            'lost' => 'nullable|boolean',
            'no_response' => 'nullable|boolean',
            'tender_value' => 'nullable|numeric',
            'hyperlink' => 'nullable|url|max:255',
            'canceled_reason' => 'nullable|string', // Add this for cancel reason
            'projectSize'=> 'nullable|string|max:255',
            'probability' => 'nullable|numeric|min:0|max:100',
            'tender_logo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048', // Optional logo upload
            'tender_image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048', // Optional image upload
        ]);
    
        // Fetch the existing tender
        $existingTender = Tender::findOrFail($id);
    
        // Track if the value or submission date has changed
        $valueChanged = isset($validatedData['tender_value']) && $existingTender->tender_value !== $validatedData['tender_value'];
        $submissionDateChanged = isset($validatedData['submissionDate']) && $existingTender->submissionDate !== $validatedData['submissionDate'];
    
        // If status is changing to "canceled", ensure that canceled_reason is provided
        if (isset($validatedData['status']) && $validatedData['status'] === 'canceled') {
            $request->validate([
                'canceled_reason' => 'required|string',
            ]);
        }
    
        // Add the updated_by field to the validated data
        $validatedData['updated_by'] = auth()->user()->id;
    
        if ($request->hasFile('tender_logo')) {
            // Get the uploaded file
            $logoFile = $request->file('tender_logo');
        
            // Define the path where you want to store the file in the public folder
            $logoPath = 'logos/' . time() . '_' . $logoFile->getClientOriginalName();
        
            // Move the file to the public path
            $logoFile->move(public_path('logos'), $logoPath);
        
            // Save the path in the validated data
            $validatedData['tender_logo'] = $logoPath;
        }
        
        if ($request->hasFile('tender_image')) {
            // Get the uploaded file
            $imageFile = $request->file('tender_image');
        
            // Define the path where you want to store the file in the public folder
            $imagePath = 'project_images/' . time() . '_' . $imageFile->getClientOriginalName();
        
            // Move the file to the public path
            $imageFile->move(public_path('project_images'), $imagePath);
        
            // Save the path in the validated data
            $validatedData['tender_image'] = $imagePath;
        }
    
    
        // Update the tender with the validated data
        $tender = Tender::updateOrCreate(['id' => $id], $validatedData);
    
        // Check if extinsionDate has changed
        if (isset($validatedData['extinsionDate']) && $existingTender->extinsionDate !== $validatedData['extinsionDate']) {
            $this->sendExtensionDateNotification($existingTender);
        }
    
        // Logic for managing no_response status
        if ($tender->completed) {
            $tender->no_response = false;
            $tender->no_response_start_date = null;
            $tender->no_response_days = 0;
        } elseif ($tender->no_response) {
            if (!$tender->no_response_start_date) {
                $tender->no_response_start_date = now();
            } else {
                $tender->no_response_days = now()->diffInDays($tender->no_response_start_date);
            }
        } else {
            if ($tender->no_response_start_date) {
                $tender->no_response_days = now()->diffInDays($tender->no_response_start_date);
                $tender->no_response_start_date = null;
            }
        }
    
        $tender->save();
    
        // If the tender value or submission date has changed, update the status
        if ($valueChanged || $submissionDateChanged) {
            // Assign status based on the selectedOption (project type)
            return $this->assignDepartmentHeadBasedOnProjectType($tender);
        }
    
        // If the status is 'rejected', send emails
        if (isset($validatedData['status']) && $validatedData['status'] === 'canceled') {
            $this->sendRejectionEmailNotifications($tender);
        }
    
        return response()->json(['message' => 'Tender updated successfully.', 'tender' => $tender], 200);
    }
    

    private function assignDepartmentHeadBasedOnProjectType($tender)
{
    $user = Auth::user();
    // Define the project types and corresponding departments
    $facilityManagementOptions = ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'];
    $assetManagementOptions = ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'];
    $projectManagmentOptions = ['PMC', 'PMC & CS', 'PMC, CS & Design'];

    $fitoutOptions = ['FO', 'FOM', 'DNP'];
    $newStatus = '';
    if ($tender->status == 'in progress'){
        $newStatus = 'in progress';
    }
    // Assign the status based on the selectedOption (project type)
    elseif (in_array($tender->selectedOption, $facilityManagementOptions) && in_array($tender->selectedOption, $assetManagementOptions)) {
        $tender->status = 'TRF facility and asset management review';
        $newStatus = 'TRF facility and asset management review';
    }
    elseif (in_array($tender->selectedOption, $facilityManagementOptions)) {
        $tender->status = 'TRF facility management review';
        $newStatus = 'TRF facility management review';
    } elseif (in_array($tender->selectedOption, $assetManagementOptions)) {
        $tender->status = 'TRF asset management review';
        $newStatus = 'TRF asset management review';
    }  elseif (in_array($tender->selectedOption, $projectManagmentOptions)) {
        $tender->status = 'TRF project management review';
        $newStatus = 'TRF project management review';
    }elseif (in_array($tender->selectedOption, $fitoutOptions)) {
        $tender->status = 'TRF Fit-out review';
        $newStatus = 'TRF Fit-out review';
    } else {
        return response()->json(['message' => 'Invalid project type for tender status assignment.'], 400);
    }


    if ($newStatus) {
        $previousStatus = $tender->status;
        $tender->status = $newStatus;
        $tender->approved_by = $user->name;
        if($newStatus === "TRF completed"){
            $tender->trfProcess = true;
        }
        $tender->save();
       $this->sendTRFStatusChangeNotification($tender, $previousStatus, $newStatus);

        return response()->json(['message' => 'Tender status updated successfully.', 'status' => $tender->status], 200);
    } else {
        return response()->json([
            'message' => 'No valid status update available.'
        ], 400);
    }
    // Save the updated status
    $tender->save();

   
    // Return success response
    return response()->json(['message' => 'Tender status updated successfully.', 'status' => $tender->status], 200);
}

private function sendTRFStatusChangeNotification($tender, $previousStatus, $newStatus)
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
                'targetedUser' => $targetedUser, // Add targetedUser to the data array
            ];
    
            // Select email template based on new status
            $emailTemplate = $this->getEmailTemplateByStatus($newStatus);
            $emailSubject = $this->getEmailSubjectByStatus($newStatus);

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
            
            // Merge the department heads into the main users collection
            if ($departmentHeads->isNotEmpty()) {
                $users = $users->merge($departmentHeads);
            }
            
            return $users;            
            
        default:
            return collect(); 
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

    private function sendRejectionEmailNotifications($tender)
{
    // Define roles and their corresponding options
    $roles = [
        'Project Management' => ['PMC', 'PMC & CS', 'PMC, CS & Design'],
        'Fit-out' => ['FO', 'FOM', 'DNP'],
       
        'Facility Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'],
      
        'Asset Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'],
                    'Coordinator Fit-out' => ['FO', 'FOM', 'DNP'],
            'Coordinator Facility' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'FM'],
            'Coordinator Asset' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM'],

      
    ];

    $selectedOption = $tender->selectedOption;
    $targetRoles = [];

    // Determine target roles based on the selected option
    foreach ($roles as $role => $options) {
        if (in_array($selectedOption, $options)) {
            $targetRoles[] = $role;
        }
    }

    // Add CEO, Business Developers, and Director roles
    $targetRoles = array_merge($targetRoles, ['Business Development']);

    // If tender value is greater than 5 million, add President role
    // if ($tender->tender_value > 5000000) {
    //     $targetRoles[] = 'President';
    // }

    if (!empty($targetRoles)) {
        // Fetch users with the matching roles
        $users = TenderingUser::whereIn('role', $targetRoles)->get();

        if ($users->isNotEmpty()) {
            foreach ($users as $user) {
                // Prepare email data
                $emailData = [
                    'tender' => $tender,
                    'user' => $user,
                ];

                // Send email to each user
                try {
                    Mail::to($user->email)->send(new TenderRejectionNotificationMail($emailData));
                    Log::info("Email notification sent to {$user->email} for cancelled tender ID {$tender->id}");
                } catch (\Exception $e) {
                    Log::error("Failed to send email for tender ID {$tender->id} to {$user->email}: " . $e->getMessage());
                }
            }
        } else {
            Log::warning('No users found with the matching roles for cancelled email notification.');
        }
    } else {
        Log::info('No matching roles found for the selected option; no cancelled email notifications sent.');
    }
}

    protected function sendExtensionDateNotification($tender)
    {
        $roles = [
            'Project Management' => ['PMC', 'PMC & CS', 'PMC, CS & Design'],
            'Fit-out' => ['FO', 'FOM', 'DNP'],
            'Facility Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'],
            'Asset Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design']
        ];

        foreach ($roles as $role => $options) {
            if (in_array($tender->selectedOption, $options)) {
                $user = TenderingUser::where('role', $role)->first();
                if ($user) {
                    try {
                        // Mail::to($user->email)->send(new ExtensionDateNotification($tender, $user->name));
                        Log::info("ExtensionDate notification email sent to {$user->email} for tender ID {$tender->id}");
                    } catch (\Exception $e) {
                        Log::error("Failed to send ExtensionDate email for tender ID {$tender->id}: " . $e->getMessage());
                    }
                } else {
                    Log::warning("No user found with role: $role");
                }
            }
        }
    }

    protected function sendRfpSubmittedNotification($tender, $validatedData)
    {
        $roles = [
            'Project Management' => ['PMC', 'PMC & CS', 'PMC, CS & Design'],
            'Fit-out' => ['FO', 'FOM', 'DNP'],
            'Facility Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'],
            'Asset Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design']
        ];

        foreach ($roles as $role => $options) {
            if (in_array($tender->selectedOption, $options)) {
                $user = TenderingUser::where('role', $role)->first();
                if ($user) {
                    try {
                        // Mail::to($user->email)->send(new RfpSubmittedNotification($tender));
                        Log::info("Email sent to {$user->email} for tender ID {$tender->id}");
                    } catch (\Exception $e) {
                        Log::error("Failed to send email for tender ID {$tender->id}: " . $e->getMessage());
                    }
                } else {
                    Log::warning("No user found with role: $role");
                }
            }
        }
    }
    protected function sendNewTenderNotification(Request $request, $tender, $validatedData)
    {
        // Define roles and their corresponding options
        $roles = [
            'Project Management' => ['PMC', 'PMC & CS', 'PMC, CS & Design'],
            'Fit-out' => ['FO', 'FOM', 'DNP'],
            'Coordinator Fit-out' => ['FO', 'FOM', 'DNP'],
            'Facility Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'],
            'Coordinator Facility' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'FM'],
            'Asset Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'],
            //'Coordinator Asset' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM']
        ];
    
        foreach ($roles as $role => $options) {
            if (in_array($tender->selectedOption, $options)) {
                $users = TenderingUser::where('role', $role)->get(); // Get all users with the role
                
                if ($users->isNotEmpty()) {
                    $validatedData['id'] = $tender->id;
    
                    // Determine the coordinator role and retrieve all coordinators' emails
                    $coordinatorRole = $this->getCoordinatorRole($role);
                    $coordinators = TenderingUser::where('role', $coordinatorRole)->get();
                    $ccEmails = $coordinators->pluck('email')->toArray(); // Get all emails of coordinators as an array
    
                    foreach ($users as $user) {
                        try {
                            // Send email with CC to all coordinators
                            Mail::to($user->email)
                                ->cc($ccEmails)
                                ->send(new TenderNotificationMail($tender, $user->name));
                            
                            Log::info("Email sent to {$user->email} with CC to " . implode(',', $ccEmails) . " for tender ID {$tender->id}");
                        } catch (\Exception $e) {
                            Log::error("Failed to send email for tender ID {$tender->id} to {$user->email}: " . $e->getMessage());
                        }
                    }
                } else {
                    Log::warning("No users found with role: $role");
                }
            }
        }
    }
    
    // Helper function to determine the coordinator role based on the role
    private function getCoordinatorRole($role)
    {
        switch ($role) {
            case 'Fit-out':
                return 'Coordinator Fit-out';
            case 'Facility Management':
                return 'Coordinator Facility';
            case 'Asset Management':
                return 'Coordinator Asset';
            default:
                return null;
        }
    }
    

    public function updateStatus(Request $request, $id)
    {
        $tender = Tender::findOrFail($id);
        $user = auth()->user();

        // Validate the recommendation field only if the user's role is one of the specified roles
        if (in_array($user->role, ['Asset Management', 'Facility Management', 'Fit-out','Project Management','Coordinator Facility','Coordinator Fit-out','Executives (CEO)','Coordinator Asset'])) {
            $validatedData = $request->validate([
                'recommendation' => 'required|string',
            ]);
        } else {
            $validatedData = [];
        }

        // Find the FactSheet associated with the tender
        $factSheet = FactSheet::where('tender_id', $id)->first();

        if (!$factSheet) {
            return response()->json(['message' => 'Fact Sheet not found for this tender.'], 404);
        }
    // Update the recommendation based on role level
    if (in_array($user->role, ['Coordinator Fit-out', 'Coordinator Facility', 'Coordinator Asset'])) {
        $factSheet->recommendation = $validatedData['recommendation'];
    } elseif (in_array($user->role, ['Asset Management', 'Facility Management', 'Project Management', 'Fit-out'])) {
        $factSheet->recommendation_level_2 = $validatedData['recommendation'];
    } elseif (in_array($user->role, ['Executives (CEO)'])) {
        $factSheet->recommendation_level_3 = $validatedData['recommendation'];
    } else {
        return response()->json(['message' => 'Unauthorized action for your role.'], 403);
    }

    $factSheet->save();
    // Previous status before changing
    $previousStatus = $tender->status;

        // Determine role and apply appropriate status logic
        switch ($user->role) {
            case 'Coordinator Fit-out':
                if ($tender->status === 'pending') {
                    $tender->status = 'under review by coordinator fit-out';
                } elseif ($tender->status === 'under review by coordinator fit-out') {
                    // Mail::to($request->user())->send(new MailableClass);
                    $tender->status = 'under review by head of fit-out';
                }
                break;

            case 'Coordinator Facility':
                if ($tender->status === 'pending') {
                    $tender->status = 'under review by coordinator facility';
                } elseif ($tender->status === 'under review by coordinator facility') {
                    $tender->status = 'under review by head of asset and facility';
                }
                break;

            case 'Coordinator Asset':
                if ($tender->status === 'pending') {
                    $tender->status = 'under review by coordinator asset';
                } elseif ($tender->status === 'under review by coordinator asset') {
                    $tender->status = 'under review by head of asset management';
                }
                break;

            case 'Project Management':
            case 'Fit-out':
            case 'Facility Management':
            case 'Asset Management':
                if (in_array($tender->status, [
                    'under review by project management',
                    'under review by head of fit-out',
                    'under review by head of facility management',
                    'under review by head of asset and facility',
                    'under review by head of asset management',
                    'under review by head of asset'
                ])) {
                    $tender->status = 'awaiting ceo decision';
                }
                break;

            case 'Executives (CEO)':
                if ($tender->status === 'awaiting ceo decision') {
                    $tender->status = 'awaiting managing director decision';
                }
                break;

            case 'Executives Managing Director':
                if ($tender->status === 'awaiting managing director decision') {
                    $tender->status = 'final decision: go';
                }
                break;

            default:
                return response()->json(['message' => 'Unauthorized action for your role.'], 403);
        }

        $tender->save();

        $this->sendStatusChangeNotification($tender, $previousStatus, $tender->status);

        return response()->json(['message' => 'Tender status updated successfully.', 'status' => $tender->status], 200);
    }

// Method to send email notifications based on status change
private function sendStatusChangeNotification($tender, $previousStatus, $newStatus)
{
    // Determine the target users based on the new status
    $targetedUsers = $this->getTargetedUsersByStatus($newStatus);

    foreach ($targetedUsers as $targetedUser) {
        // Prepare email data
        $emailData = [
            'tender' => $tender,
            'previousStatus' => $previousStatus,
            'newStatus' => $newStatus,
            'targetedUser' => $targetedUser,
        ];

        // Send email to each targeted user
        Mail::to($targetedUser->email)
            ->send(new TenderStatusChangeMail($emailData));

        Log::info("Email notification sent to {$targetedUser->email} for status change from {$previousStatus} to {$newStatus}.");
    }
}

// Helper method to determine targeted users based on the new status
private function getTargetedUsersByStatus($newStatus)
{
    // Define roles and status mapping for email notifications
    $roleMapping = [
        'under review by head of fit-out' => ['Fit-out'],
        'under review by head of asset and facility' => ['Facility Management', 'Asset Management'],
        'under review by head of asset management' => ['Asset Management'],
        'under review by head of facility management'=>['Facility Management'],
        'awaiting ceo decision' => ['Executives (CEO)'],
        'awaiting managing director decision' => ['Executives Managing Director'],
        'final decision: go' => ['Executives Managing Director'],
    ];

    // Get roles based on new status
    $roles = $roleMapping[$newStatus] ?? [];

    // Fetch users with matching roles
    return TenderingUser::whereIn('role', $roles)->get();
}

public function finalStatus(Request $request, $id)
{
    $tender = Tender::findOrFail($id);
    Log::info('Received request to update status for tender', [
        'tender_id' => $id,
        'request_data' => $request->all()
    ]);

    // Check and update the status based on the request input
    if ($request->has('no_response')) {
        $tender->status = 'no response';
        $tender->no_response = true;
        $tender->no_response_start_date = now();
        $tender->lost = false;
        
    } elseif ($request->has('awarded')) {
        $tender->status = 'Awarded';
        $tender->no_response = false;
        $tender->lost = false;
        $tender->completed = true;
    } elseif ($request->has('submitted_to_client')) {
        $tender->status = 'Submitted To Client';
        $tender->completed = true;
        $tender->lost = false;
         $tender->completed = false;
    } elseif ($request->has('status') && $request->input('status') === 'rejected/lost') {
        $tender->status = 'rejected/lost';
        $tender->lost = true;
        $tender->no_response = false;
        $tender->completed = true;
    }
    elseif ($request->has('status') && $request->input('status') === 'Not Submitted') {
        $tender->status = 'Not Submitted';
        $tender->lost = false;
        $tender->not_submitted_reason = $request->input('not_submitted_reason');
        $tender->no_response = false;
        $tender->completed = false;
    }
     else {
        return response()->json(['message' => 'Not allowed to change the status'], 403);
    }

    $tender->save();

    // Send notifications if necessary
    $this->sendEmailNotifications($tender);
    
    return response()->json(['message' => 'Tender status updated successfully.', 'status' => $tender->status], 200);
}

public function getFinalStatusTenderCounts(Request $request)
{
    // Get the quarter and year from the request; default to the first quarter and the current year if not provided
    $quarter = $request->input('quarter', 1); // Use 1 as default if not provided
    $year = $request->input('year', date('Y')); // Default to the current year if no year is provided

    // If "All Quarters/Year" is selected, fetch data for the entire year
    if ($quarter == 0) {
        $startDate = Carbon::create($year, 1, 1)->startOfDay();
        $endDate = Carbon::create($year, 12, 31)->endOfDay();
    } else {
        // Determine the start and end dates for the selected quarter and year using Carbon
        switch ($quarter) {
            case 1:
                $startDate = Carbon::create($year, 1, 1)->startOfDay();
                $endDate = Carbon::create($year, 3, 31)->endOfDay();
                break;
            case 2:
                $startDate = Carbon::create($year, 4, 1)->startOfDay();
                $endDate = Carbon::create($year, 6, 30)->endOfDay();
                break;
            case 3:
                $startDate = Carbon::create($year, 7, 1)->startOfDay();
                $endDate = Carbon::create($year, 9, 30)->endOfDay();
                break;
            case 4:
                $startDate = Carbon::create($year, 10, 1)->startOfDay();
                $endDate = Carbon::create($year, 12, 31)->endOfDay();
                break;
            default:
                return response()->json(['error' => 'Invalid quarter specified'], 400);
        }
    }

    // Define roles and their associated project main scopes
    $roles = [
        'Project Management' => ['PMC', 'PMC & CS', 'PMC, CS & Design'],
        'Fit-out' => ['FO', 'FOM', 'DNP'],
        'Coordinator Fit-out' => ['FO', 'FOM', 'DNP'],
        'Facility Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'],
        'Coordinator Facility' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'FM'],
        'Asset Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'],
        'Coordinator Asset' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM'],
    ];

    // Initialize counts and total values for each status
    $counts = [
        'no_response' => 0,
        'awarded' => 0,
        'submitted_to_client' => 0,
        'rejected_lost' => 0, // Add lost/rejected tenders
    ];

    $values = [
        'no_response_total_value' => 0,
        'awarded_total_value' => 0,
        'submitted_to_client_total_value' => 0,
        'rejected_lost_total_value' => 0, // Add total value for lost/rejected tenders
    ];

    // Get the current authenticated user
    $user = auth()->user();
    $userRole = $user->role;

    // Start by filtering tenders by the date range
    $query = Tender::query()->whereBetween('created_at', [$startDate, $endDate]);

    // Filter tenders by the user's role if the user is not a CEO, Executive Director, or President
    if (!in_array($userRole, ['Business Development', 'Executives (CEO)', 'Executives Managing Director', 'President'])) {
        // If the user is not a CEO, Executive Director, or President, filter by project type
        if (array_key_exists($userRole, $roles)) {
            $projectTypes = $roles[$userRole]; // Get the associated project types for the user's role
            $query->whereIn('selectedOption', $projectTypes);
        } else {
            return response()->json(['error' => 'Invalid user role specified'], 403);
        }
    }

    // Get all filtered tenders for the user role and quarter/year
    $tenders = $query->get();

    // Calculate counts and total values for each status from the filtered tenders
    $counts['no_response'] = $tenders->where('status', 'no response')->count();
    $values['no_response_total_value'] = $tenders->where('status', 'no response')->sum('tender_value');

    $counts['awarded'] = $tenders->where('status', 'Awarded')->count();
    $values['awarded_total_value'] = $tenders->where('status', 'Awarded')->sum('tender_value');

    $counts['submitted_to_client'] = $tenders->where('completed', true)->count();
    $values['submitted_to_client_total_value'] = $tenders->where('completed', true)->sum('tender_value');

    $counts['rejected_lost'] = $tenders->where('status', 'rejected/lost')->count();
    $values['rejected_lost_total_value'] = $tenders->where('status', 'rejected/lost')->sum('tender_value');

    // Calculate success rate: (total awarded / total submitted to client) * 100
    $totalSubmittedToClient = $counts['submitted_to_client'];
    $totalAwarded = $counts['awarded'];
    $successRate = ($totalSubmittedToClient > 0) ? ($totalAwarded / $totalSubmittedToClient) * 100 : 0;

    return response()->json(['counts' => $counts, 'values' => $values, 'success_rate' => round($successRate, 2)]);
}


    private function sendEmailNotifications($tender)
    {
        // Define roles and their corresponding options
        $roles = [
            'Project Management' => ['PMC', 'PMC & CS', 'PMC, CS & Design'],
            'Fit-out' => ['FO', 'FOM', 'DNP'],
            'Coordinator Fit-out' => ['FO', 'FOM', 'DNP'],
            'Facility Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'],
            'Coordinator Facility' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'FM'],
            'Asset Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'],
            'Coordinator Asset' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM'],
        ];
        $selectedOption = $tender->selectedOption;
        $targetRoles = [];
    
        // Determine target roles based on the selected option
        foreach ($roles as $role => $options) {
            if (in_array($selectedOption, $options)) {
                $targetRoles[] = $role;
            }
        }
    
        // Fetch users with the matching roles
        $users = [];
        if (!empty($targetRoles)) {
            $users = TenderingUser::whereIn('role', $targetRoles)->get();
        }
    
        // Fetch CEO, Executive Director, and President separately
        $ceo = TenderingUser::where('role', 'Executives (CEO)')->first();
        $executiveDirector = TenderingUser::where('role', 'Executives Managing Director')->first();
        $president = TenderingUser::where('role', 'President')->first();
    
        // Combine all the recipients into one collection
        $recipients = collect($users);
        if ($ceo) {
            $recipients->push($ceo);
        }
        if ($executiveDirector) {
            $recipients->push($executiveDirector);
        }
        if ($president) {
            $recipients->push($president);
        }
    
        // Send emails individually to each recipient
        if ($recipients->isNotEmpty()) {
            foreach ($recipients as $recipient) {
                // Prepare email data for each user
                $emailData = [
                    'tender' => $tender,
                    'user' => $recipient,
                ];
    
                try {
                    // Send the email
                    Mail::to($recipient->email)->send(new TenderFinalStatusNotificationMail($emailData));
                    Log::info("Email notification sent to {$recipient->email} for tender ID {$tender->id}");
                } catch (\Exception $e) {
                    Log::error("Failed to send email for tender ID {$tender->id} to {$recipient->email}: " . $e->getMessage());
                }
            }
        } else {
            Log::warning('No recipients found for email notification.');
        }
    }
    
    

    public function getAllTendersDetails()
    {
        // Define roles and their associated project main scopes
        $roles = [
            'Project Management' => ['PMC', 'PMC & CS', 'PMC, CS & Design'],
            'Fit-out' => ['FO', 'FOM', 'DNP'],
            'Coordinator Fit-out' => ['FO', 'FOM', 'DNP'],
            'Facility Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'],
            'Coordinator Facility' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'FM'],
            'Asset Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'],
            'Coordinator Asset' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM']
        ];

        // Get the current authenticated user
        $user = auth()->user();
        $userRole = $user->role;
        $userBranch = $user->branch;

        // Define the query to retrieve tenders based on role and branch
        $query = Tender::query();

        if (array_key_exists($userRole, $roles)) {
            // Get the project main scopes associated with the user's role
            $mainScopes = $roles[$userRole];
            $query->whereIn('selectedOption', $mainScopes);
        }

        if ($userBranch !== 'KSA & UAE') {
            $query->where('branch', $userBranch);
        }

        // Select relevant columns and get the tenders with user names
        $tenders = $query->select('id', 'tenderTitle', 'created_by', 'updated_by', 'updated_at', 'status', 'no_response_days', 'branch','submissionDate','canceled_reason','not_submitted_reason')
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
                    'status' => $tender->status,
                    'no_response_days' => $tender->no_response_days,
                    'branch' => $tender->branch,
                    'submissionDate' => $tender->submissionDate,
                    'canceled_reason' => $tender->canceled_reason,
                    'not_submitted_reason' => $tender->not_submitted_reason,
                ];
            });

        return response()->json(['tenders' => $tenders], 200);
    }

    public function getQuarterTendersDetails(Request $request)
    {
        $quarter = $request->input('quarter', 1);  // Get the quarter from the request, default to 1
        $year = $request->input('year', date('Y'));  // Get the year from the request, default to the current year
        

        // If "All Quarters/Year" is selected, fetch data for the entire year
        if ($quarter == 0) {
            $startDate = Carbon::create($year, 1, 1)->startOfDay();
            $endDate = Carbon::create($year, 12, 31)->endOfDay();
        } else {
            // Calculate the start and end dates for the selected quarter and year using Carbon
            switch ($quarter) {
                case 1:
                    $startDate = Carbon::create($year, 1, 1)->startOfDay();
                    $endDate = Carbon::create($year, 3, 31)->endOfDay();
                    break;
                case 2:
                    $startDate = Carbon::create($year, 4, 1)->startOfDay();
                    $endDate = Carbon::create($year, 6, 30)->endOfDay();
                    break;
                case 3:
                    $startDate = Carbon::create($year, 7, 1)->startOfDay();
                    $endDate = Carbon::create($year, 9, 30)->endOfDay();
                    break;
                case 4:
                    $startDate = Carbon::create($year, 10, 1)->startOfDay();
                    $endDate = Carbon::create($year, 12, 31)->endOfDay();
                    break;
                default:
                    return response()->json(['error' => 'Invalid quarter specified'], 400);
            }
        }
        Log::info("Quarter: {$quarter}, Year: {$year}, StartDate: {$startDate}, EndDate: {$endDate}");
        // Define roles and their associated project main scopes
        $roles = [
            'Project Management' => ['PMC', 'PMC & CS', 'PMC, CS & Design'],
            'Fit-out' => ['FO', 'FOM', 'DNP'],
            'Coordinator Fit-out' => ['FO', 'FOM', 'DNP'],
            'Facility Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'],
            'Coordinator Facility' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'FM'],
            'Asset Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'],
            'Coordinator Asset' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM'],
        ];
    
        // Get the current authenticated user
        $user = auth()->user();
        $userRole = $user->role;
        $userBranch = $user->branch;
    
        // Define the query to retrieve tenders based on role, branch, quarter, and year
        $query = Tender::query();
    
        // Apply filtering based on role, except for higher-level executives (CEO, Executive Director, President)
        if (!in_array($userRole, ['Business Development', 'Executives (CEO)', 'Executives Managing Director', 'President'])) {
            if (array_key_exists($userRole, $roles)) {
                // Get the project main scopes associated with the user's role
                $mainScopes = $roles[$userRole];
                $query->whereIn('selectedOption', $mainScopes);
            } else {
                return response()->json(['error' => 'Invalid user role specified'], 403);
            }
        }
    
        // Apply branch filtering if the user is not from 'KSA & UAE'
        if ($userBranch !== 'KSA & UAE') {
            $query->where('branch', $userBranch);
        }
    
        // Filter tenders within the selected quarter or year
        $query->whereBetween('created_at', [$startDate, $endDate]);
    
        // Select relevant columns and get the tenders with user names
        $tenders = $query->select('id', 'tenderTitle', 'created_by', 'updated_by', 'updated_at', 'status', 'no_response_days', 'branch')
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
                    'status' => $tender->status,
                    'no_response_days' => $tender->no_response_days,
                    'branch' => $tender->branch,
                ];
        
            }
          
        );
        
        return response()->json(['tenders' => $tenders], 200);
    }
    

    
    

    // protected function getTenderStatus($tender)
    // {
    //     if ($tender->gono === 1) {
    //         return 'true';
    //     } elseif ($tender->status === 0) {
    //         return 'false';
    //     } else {
    //         return 'pending';
    //     }
    // }

    // public function getTenderCounts()
    // {
    //     $roles = [
    //         'Project Management' => ['PMC', 'PMC & CS', 'PMC, CS & Design'],
    //         'Fit-out' => ['FO', 'FOM', 'DNP'],
    //         'Facility Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM'],
    //         'Asset Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM']
    //     ];

    //     $user = auth()->user();
    //     $userRole = $user->role;
    //     $userBranch = $user->branch;

    //     $counts = [
    //         'pending' => [],
    //         'in_progress' => [],
    //         'submitted' => [],
    //         'completed' => [],
    //         'no_response' => []
    //     ];

    //     if ($userBranch === 'KSA & UAE') {
    //         // If branch is "KSA & UAE", show all tenders
    //         $allMainScopes = array_merge(...array_values($roles));
    //         foreach ($allMainScopes as $mainScope) {
    //             $counts['pending'][$mainScope] = Tender::where('selectedOption', $mainScope)->where('status', 'pending')->count();
    //             $counts['in_progress'][$mainScope] = Tender::where('selectedOption', $mainScope)->where('status', 'in progress')->count();
    //             $counts['submitted'][$mainScope] = Tender::where('selectedOption', $mainScope)->where('status', 'submitted')->count();
    //             $counts['completed'][$mainScope] = Tender::where('selectedOption', $mainScope)->where('completed', true)->count();
    //             $counts['no_response'][$mainScope] = Tender::where('selectedOption', $mainScope)->where('no_response', true)->count();
    //         }
    //     } elseif (array_key_exists($userRole, $roles)) {
    //         $mainScopes = $roles[$userRole];

    //         foreach ($mainScopes as $mainScope) {
    //             $counts['pending'][$mainScope] = Tender::where('selectedOption', $mainScope)->where('status', 'pending')->where('branch', $userBranch)->count();
    //             $counts['in_progress'][$mainScope] = Tender::where('selectedOption', $mainScope)->where('status', 'in progress')->where('branch', $userBranch)->count();
    //             $counts['submitted'][$mainScope] = Tender::where('selectedOption', $mainScope)->where('status', 'submitted')->where('branch', $userBranch)->count();
    //             $counts['completed'][$mainScope] = Tender::where('selectedOption', $mainScope)->where('completed', true)->where('branch', $userBranch)->count();
    //             $counts['no_response'][$mainScope] = Tender::where('selectedOption', $mainScope)->where('no_response', true)->where('branch', $userBranch)->count();
    //         }
    //     } else {
    //         // If the user's role is not one of the specified roles, show tender status for all main scope values within the branch
    //         $allMainScopes = array_merge(...array_values($roles));

    //         foreach ($allMainScopes as $mainScope) {
    //             $counts['pending'][$mainScope] = Tender::where('selectedOption', $mainScope)->where('status', 'pending')->where('branch', $userBranch)->count();
    //             $counts['in_progress'][$mainScope] = Tender::where('selectedOption', $mainScope)->where('status', 'in progress')->where('branch', $userBranch)->count();
    //             $counts['submitted'][$mainScope] = Tender::where('selectedOption', $mainScope)->where('status', 'submitted')->where('branch', $userBranch)->count();
    //             $counts['completed'][$mainScope] = Tender::where('selectedOption', $mainScope)->where('completed', true)->where('branch', $userBranch)->count();
    //             $counts['no_response'][$mainScope] = Tender::where('selectedOption', $mainScope)->where('no_response', true)->where('branch', $userBranch)->count();
    //         }
    //     }

    //     // Add totals for each status
    //     $counts['pending']['total'] = array_sum($counts['pending']);
    //     $counts['in_progress']['total'] = array_sum($counts['in_progress']);
    //     $counts['submitted']['total'] = array_sum($counts['submitted']);
    //     $counts['completed']['total'] = array_sum($counts['completed']);
    //     $counts['no_response']['total'] = array_sum($counts['no_response']);

    //     return response()->json($counts);
    // }
    public function getTenderCounts(Request $request)
{
    // Get the quarter and year from the request; default to the first quarter and the current year if not provided
    $quarter = $request->input('quarter', 1); // Set quarter to 1 by default
    $year = $request->input('year', date('Y')); // Default to the current year if no year is provided

    // If "All Quarters/Year" is selected, we fetch data for the entire year
    if ($quarter == 0) {
        $startDate = Carbon::create($year, 1, 1)->startOfDay();
        $endDate = Carbon::create($year, 12, 31)->endOfDay();
    } else {
        // Determine the start and end dates for the selected quarter and year using Carbon
        switch ($quarter) {
            case 1:
                $startDate = Carbon::create($year, 1, 1)->startOfDay();
                $endDate = Carbon::create($year, 3, 31)->endOfDay();
                break;
            case 2:
                $startDate = Carbon::create($year, 4, 1)->startOfDay();
                $endDate = Carbon::create($year, 6, 30)->endOfDay();
                break;
            case 3:
                $startDate = Carbon::create($year, 7, 1)->startOfDay();
                $endDate = Carbon::create($year, 9, 30)->endOfDay();
                break;
            case 4:
                $startDate = Carbon::create($year, 10, 1)->startOfDay();
                $endDate = Carbon::create($year, 12, 31)->endOfDay();
                break;
            default:
                return response()->json(['error' => 'Invalid quarter specified'], 400);
        }
    }

    // Define roles and their associated project main scopes
    $roles = [
        'Project Management' => ['PMC', 'PMC & CS', 'PMC, CS & Design'],
        'Fit-out' => ['FO', 'FOM', 'DNP'],
        'Coordinator Fit-out' => ['FO', 'FOM', 'DNP'],
        'Facility Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'],
        'Coordinator Facility' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'FM'],
        'Asset Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'],
        'Coordinator Asset' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM'],
    ];

    // Initialize counts and total values for each status
    $counts = [
        'facility_and_asset_management_review' => 0,
        'facility_management_review' => 0,
        'asset_management_review' => 0,
        'fit_out_review' => 0,
        'ceo_review' => 0,
        'executives_md_review' => 0,
        'Project_Management' => 0,
        'president_review' => 0,
        'in_progress' => 0,
        'TRF_completed' => 0,
        'go' => 0,
        'no_go' => 0,
    ];

    $values = [
        'facility_and_asset_management_review' => 0,
        'facility_management_review' => 0,
        'asset_management_review' => 0,
        'fit_out_review' => 0,
        'ceo_review' => 0,
        'executives_md_review' => 0,
        'president_review' => 0,
        'in_progress' => 0,
        'TRF_completed' => 0,
        'Project_Management' => 0,
        'go_total_value' => 0,
        'no_go_total_value' => 0,
    ];

    // Get the current authenticated user
    $user = auth()->user();
    $userRole = $user->role;

    // Start by filtering tenders by the date range
    $query = Tender::query()->whereBetween('created_at', [$startDate, $endDate]);

    // Filter tenders by the user's role if the user is not a CEO, Executive Director, or President
    if (!in_array($userRole, ['Business Development','Executives (CEO)', 'Executives Managing Director', 'President'])) {
        // If the user is not a CEO, Executive Director, or President, filter by project type
        if (array_key_exists($userRole, $roles)) {
            $projectTypes = $roles[$userRole]; // Get the associated project types for the user's role
            $query->whereIn('selectedOption', $projectTypes);
        } else {
            return response()->json(['error' => 'Invalid user role specified'], 403);
        }
    }

    // Get all filtered tenders for the user role and quarter/year
    $tenders = $query->get();

    // Calculate counts and total values for each status from the filtered tenders
    $statuses = [
        'facility_and_asset_management_review' => 'TRF facility and asset management review',
        'facility_management_review' => 'TRF facility management review',
        'asset_management_review' => 'TRF asset management review',
        'fit_out_review' => 'TRF Fit-out review',
        'ceo_review' => 'TRF ceo review',
        'executives_md_review' => 'TRF Executive Managing Director review',
        'president_review' => 'TRF President review',
        'in_progress' => 'in progress',
        'TRF_completed' => 'TRF completed',
        'Project_Management' => 'TRF project management review',
    ];

    foreach ($statuses as $key => $status) {
        $statusTenders = $tenders->where('status', $status);
        $counts[$key] = $statusTenders->count();
        $values[$key] = $statusTenders->sum('tender_value');
    }

    // Sum counts and values for all "go" tenders
    $counts['go'] = array_sum(array_values(array_intersect_key($counts, array_flip([
        'facility_and_asset_management_review',
        'facility_management_review',
        'asset_management_review',
        'fit_out_review',
        'ceo_review',
        'executives_md_review',
        'president_review',
        'in_progress',
        'Project_Management',
        'TRF_completed'
    ]))));

    $values['go_total_value'] = array_sum(array_values(array_intersect_key($values, array_flip([
        'facility_and_asset_management_review',
        'facility_management_review',
        'asset_management_review',
        'fit_out_review',
        'ceo_review',
        'executives_md_review',
        'president_review',
        'in_progress',
        'Project_Management',
        'TRF_completed'
    ]))));

    // Count and sum values for "no go" tenders with status "canceled" or "rejected" within the selected quarter/year
    $noGoTenders = $tenders->whereIn('status', ['rejected','TRF rejected']);
    $counts['no_go'] = $noGoTenders->count();
    $values['no_go_total_value'] = $noGoTenders->sum('tender_value');

    return response()->json(['counts' => $counts, 'values' => $values]);
}

    
    


}
