<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Deal;
use App\Models\CRM\Attachment; // Import the Attachment model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File; // For file handling

class DealController extends Controller
{
    /**
     * Display a listing of the deals.
     */
    public function index()
    {
        // Eager load 'company', 'contacts', 'activities', and 'attachments' relationships
        $deals = Deal::with(['company', 'contacts', 'activities', 'createdBy', 'attachments'])->get();

        return response()->json($deals, 200);
    }

    /**
     * Display the specified deal.
     */
    public function show($id)
    {
        // Eager load 'company', 'company.parent', 'company.children', 'contacts', 'activities', and 'attachments'
        $deal = Deal::with([
            'company.parent',
            'company.children',
            'contacts',
            'activities',
            'tasks',
            'attachments.uploadedBy', // Include the uploader's information
        ])->findOrFail($id);

        // Return the deal with related data
        return response()->json($deal, 200);
    }

    /**
     * Store a newly created deal in storage.
     */
    public function uploadAttachments(Request $request, $id)
    {
        // Validate the incoming request
        $request->validate([
            'attachments.*' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,eml,msg,xlsx|max:200480', // 20MB max per file
        ], [
            'attachments.*.required' => 'Please select a file to upload.',
            'attachments.*.file'     => 'Each attachment must be a valid file.',
            'attachments.*.mimes'    => 'Attachments must be of type: jpg, jpeg, png, pdf, doc, docx,eml,xls,msg,xlsx.',
            'attachments.*.max'      => 'Each attachment may not be greater than 200MB.',
        ]);

        // Find the deal
        $deal = Deal::findOrFail($id);

        // Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Define the path where the file will be stored in the public directory
                $destinationPath = public_path('attachments');
                // Ensure the directory exists
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true);
                }
                // Generate a unique file name
                $fileName = time() . '_' . $file->getClientOriginalName();
                // Move the file to the public/attachments directory
                $file->move($destinationPath, $fileName);
                // Save the relative path to the database
                $relativePath = 'attachments/' . $fileName;

                // Create an attachment record
                $deal->attachments()->create([
                    'file_name'   => $file->getClientOriginalName(),
                    'file_path'   => $relativePath,
                    'file_type'   => $file->getClientMimeType(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        // Return a success response with the updated attachments
        return response()->json([
            'message'     => 'Attachments uploaded successfully',
            'attachments' => $deal->attachments()->with('uploadedBy')->get(),
        ], 201);
    }

    /**
     * Delete an attachment from a deal.
     */
    public function deleteAttachment($dealId, $attachmentId)
    {
        $deal = Deal::findOrFail($dealId);

        $attachment = Attachment::where('deal_id', $deal->id)->findOrFail($attachmentId);

        // Delete the file from the public directory
        $filePath = public_path($attachment->file_path);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        // Delete the attachment record
        $attachment->delete();

        // Return a success response
        return response()->json([
            'message' => 'Attachment deleted successfully',
        ], 200);
    }

    public function store(Request $request)
    {
        // Step 1: Validate the incoming request with custom error messages
        $request->validate([
            'title'          => 'required|string|max:255',
            'amount'         => 'required|numeric',
            'stage'          => 'required|string|max:50',
            'close_date'     => 'nullable|date',
            'company_id'     => 'required|exists:CRM_companies,id',
            'status'         => 'sometimes',
            'deal_stage_id'  => 'nullable|exists:CRM_deal_stages,id',
            'user_id'        => 'nullable|exists:CRM_users,id',
            'contact_ids'    => 'nullable|array',
            'contact_ids.*'  => 'exists:CRM_contacts,id',
            'department'     => 'nullable|string|max:255',
            'attachments.*'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,eml,msg,xlsx|max:200480', // 20MB max per file
        ], [
            // Custom error messages...
            'attachments.*.file'    => 'Each attachment must be a valid file.',
            'attachments.*.mimes'   => 'Attachments must be of type: jpg, jpeg, png, pdf,eml,doc, docx, xls,eml, msg,xlsx.',
            'attachments.*.max'     => 'Each attachment may not be greater than 200MB.',
        ]);

        // Step 2: Create the deal with the authenticated user's ID
        $dealData = $request->except('contact_ids', 'attachments');
        $dealData['created_by'] = Auth::id();
        $deal = Deal::create($dealData);

        // Step 3: Attach contacts to the deal
        if ($request->has('contact_ids')) {
            $deal->contacts()->attach($request->input('contact_ids'));
        }

        // Step 4: Handle attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                // Define the path where the file will be stored in the public directory
                $destinationPath = public_path('attachments');
                // Ensure the directory exists
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true);
                }
                // Generate a unique file name
                $fileName = time() . '_' . $file->getClientOriginalName();
                // Move the file to the public/attachments directory
                $file->move($destinationPath, $fileName);
                // Save the relative path to the database
                $relativePath = 'attachments/' . $fileName;

                // Create an attachment record
                $deal->attachments()->create([
                    'file_name'   => $file->getClientOriginalName(),
                    'file_path'   => $relativePath,
                    'file_type'   => $file->getClientMimeType(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        // Step 5: Return a success response with the deal and its relations
        return response()->json([
            'message' => 'Deal created successfully',
            'deal'    => $deal->load(['company', 'contacts', 'attachments']),
        ], 201);
    }

    /**
     * Update the specified deal in storage.
     */
    public function update(Request $request, $id)
    {
        // Step 1: Validate the incoming request with custom error messages
        $request->validate([
            'title'          => 'sometimes|required|string|max:255',
            'amount'         => 'sometimes|required|numeric',
            'stage'          => 'sometimes|required|string|max:200',
            'close_date'     => 'nullable|date',
            'company_id'     => 'nullable|exists:CRM_companies,id',
            'status'         => 'nullable|string|max:200',
            'deal_stage_id'  => 'nullable|exists:CRM_deal_stages,id',
            'user_id'        => 'nullable|exists:CRM_users,id',
            'contact_ids'    => 'nullable|array',
            'contact_ids.*'  => 'exists:CRM_contacts,id',
            'department'     => 'nullable|string|max:255',
            'attachments.*'  => 'nullable', // 20MB max per file
            'delete_attachment_ids' => 'nullable|array',
            'delete_attachment_ids.*' => 'exists:crm_attachments,id',
        ], [
            // Custom error messages...
            'attachments.*.file'    => 'Each attachment must be a valid file.',
            'attachments.*.mimes'   => 'Attachments must be of type: jpg, jpeg, png, pdf, doc, docx, xls, xlsx.',
            'attachments.*.max'     => 'Each attachment may not be greater than 200MB.',
            'delete_attachment_ids.*.exists' => 'One of the selected attachments to delete does not exist.',
        ]);

        // Step 2: Find the deal and update its details
        $deal = Deal::findOrFail($id);
        $deal->update($request->except('contact_ids', 'attachments', 'delete_attachment_ids'));

        // Step 3: Sync contacts to the deal
        if ($request->has('contact_ids')) {
            $deal->contacts()->sync($request->input('contact_ids'));
        }

        // Step 4: Handle new attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
            // Define the path where the file will be stored in the public directory
                $destinationPath = public_path('attachments');
                // Ensure the directory exists
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true);
                }
                // Generate a unique file name
                $fileName = time() . '_' . $file->getClientOriginalName();
                // Move the file to the public/attachments directory
                $file->move($destinationPath, $fileName);
                // Save the relative path to the database
                $relativePath = 'attachments/' . $fileName;

                // Create an attachment record
                $deal->attachments()->create([
                    'file_name'   => $file->getClientOriginalName(),
                    'file_path'   => $relativePath,
                    'file_type'   => $file->getClientMimeType(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        // Step 5: Handle attachment deletions
        if ($request->has('delete_attachment_ids')) {
            foreach ($request->input('delete_attachment_ids') as $attachmentId) {
                $attachment = Attachment::where('deal_id', $deal->id)->find($attachmentId);
                if ($attachment) {
                    // Delete the file from the public directo frontry
                    $filePath = public_path($attachment->file_path);
                    if (File::exists($filePath)) {
                        File::delete($filePath);
                    }

                    // Delete the attachment record
                    $attachment->delete();
                }
            }
        }

        // Step 6: Return a success response with the deal and its relations
        return response()->json([
            'message' => 'Deal updated successfully',
            'deal'    => $deal->load(['company', 'contacts', 'attachments']),
        ], 200);
    }

    /**
     * Remove the specified deal from storage (soft delete).
     */
    public function destroy($id)
    {
        $deal = Deal::findOrFail($id);

        // Step 1: Soft delete the deal
        $deal->delete();

        // Step 2: Return a success response
        return response()->json([
            'message' => 'Deal deleted successfully',
        ], 200);
    }
}
