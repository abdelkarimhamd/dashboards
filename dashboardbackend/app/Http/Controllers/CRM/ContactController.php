<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Contact;
use App\Models\CRM\DealContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    /**
     * Display a listing of the contacts.
     */
    public function index()
    {
        try {
            // Eager load 'company' relationship
            $contacts = Contact::with(['company','createdBy'])->get();

            return response()->json($contacts, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching contacts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            // Eager load 'company' relationship
            $contact = Contact::with(['company','tasks'])->findOrFail($id);

            return response()->json($contact, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving contact',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Store a newly created contact in storage.
     */



         public function store(Request $request)
         {
            Log::info('Creating new contact with data:',$request->all());

             // Step 1: Validate the incoming request
             $validatedData = $request->validate([
                 'first_name'   => 'required|string|max:100',
                 'last_name'    => 'required|string|max:100',
                 'email'        => 'nullable|email|max:255|unique:CRM_contacts,email',
                 'phone'        => 'nullable|string|max:50',
                 'job_title'    => 'nullable|string|max:100',
                 'company_id'   => 'nullable|exists:CRM_companies,id', // Ensure the company exists
                 'deal_id'      => 'nullable|exists:crm_deals,id',    // Ensure the deal exists
             ]);

             // Log the incoming request data for debugging (optional)
             Log::info('Creating new contact with data:', $validatedData);

             // Step 2: Add 'created_by' to the data array
             $data = $validatedData;
             $data['created_by'] = auth()->user()->id;// Assuming you're using Laravel's built-in authentication

             // Step 3: Initiate a database transaction
             DB::beginTransaction();

             try {
                 // Step 4: Create the contact with the 'created_by' field
                 $contact = Contact::create($data);

                 // Initialize $dealContact to null
                 $dealContact = null;

                 // Step 5: If 'deal_id' is provided, associate the contact with the deal
                 if (isset($validatedData['deal_id'])) {
                     $dealContact = DealContact::create([
                         'deal_id'    => $validatedData['deal_id'],
                         'contact_id' => $contact->id,
                         // Include any additional fields here if necessary
                     ]);
                 }

                 // Step 6: Commit the transaction since both operations succeeded
                 DB::commit();

                 // Step 7: Prepare the success response
                 $response = [
                     'message' => 'Contact created successfully.',
                     'contact' => $contact,
                 ];

                 // Include 'deal_contact' in the response only if it's created
                 if ($dealContact) {
                     $response['deal_contact'] = $dealContact;
                 }

                 // Step 8: Return the success response with HTTP status 201 (Created)
                 return response()->json($response, 201);

             } catch (\Exception $e) {
                 // Step 9: An error occurred; rollback the transaction
                 DB::rollBack();

                 // Log the error for debugging purposes
                 Log::error('Error creating contact and associating with deal:', [
                     'error' => $e->getMessage(),
                     'data'  => $validatedData,
                 ]);

                 // Step 10: Return an error response with HTTP status 500 (Internal Server Error)
                 return response()->json([
                     'message' => 'An error occurred while creating the contact.',
                     // Optionally, you can exclude the 'error' field to prevent exposing sensitive info
                     // 'error'   => $e->getMessage(),
                 ], 500);
             }
         }




    /**



     * Update the specified contact in storage.
     */
    public function update(Request $request, $id)
    {
        // Step 1: Validate the incoming request
        $request->validate([
            'first_name' => 'sometimes|required|string|max:100',
            'last_name' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|nullable|email|max:255|unique:CRM_contacts,email,' . $id,
            'phone' => 'nullable|string|max:50',
            'job_title' => 'nullable|string|max:1000',
            'company_id' => 'nullable|exists:CRM_companies,id',
        ]);

        // Step 2: Find the contact
        $contact = Contact::findOrFail($id);

        // Step 3: Prepare data for update
        $data = $request->all();
        $data['updated_by'] = auth()->user()->id;

        // Step 4: Update the contact with the new data
        $contact->update($data);

        // Step 5: Return a success response
        return response()->json([
            'message' => 'Contact updated successfully',
            'contact' => $contact,
        ], 200);
    }

    /**
     * Remove the specified contact from storage (soft delete).
     */
    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);

        // Step 1: Soft delete the contact
        $contact->delete();

        // Step 2: Return a success response
        return response()->json([
            'message' => 'Contact deleted successfully',
        ], 200);
    }
}
