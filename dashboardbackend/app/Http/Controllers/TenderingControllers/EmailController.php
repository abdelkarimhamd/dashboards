<?php

namespace App\Http\Controllers\TenderingControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TenderingUser;
use App\Mail\TenderNotificationMail;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public function sendEmail(Request $request)
    {
        $data = $request->all();

        // Validate the request data
        $request->validate([
            'tenderTitle' => 'required|string',
            'selectedOption' => 'required|string',
            'location' => 'required|string',
            'sourceOption' => 'required|string',
            'rfpDocument' => 'required|file|mimes:pdf,doc,docx',
            'link' => 'required|url'
        ]);

        // Define the roles and their corresponding project main scopes
        $roles = [
            'Project Management' => ['PMC', 'PMC & CS', 'PMC, CS & Design'],
            'Fit-out' => ['FO', 'FOM', 'DNP'],
            'Facility Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design'],
            'Asset Management' => ['FMMA', 'AFM', 'FMC', 'TFM', 'AM', 'PMC', 'PMC & CS', 'PMC, CS & Design']
        ];

        // Find the roles associated with the selected project main scope
        $selectedOption = $data['selectedOption'];
        $matchingRoles = [];

        foreach ($roles as $role => $scopes) {
            if (in_array($selectedOption, $scopes)) {
                $matchingRoles[] = $role;
            }
        }

        // Retrieve users based on the matching roles
        $users = TenderingUser::whereIn('role', $matchingRoles)->get();

        // Send email to each user
        foreach ($users as $user) {
            Mail::to($user->email)->send(new TenderNotificationMail($data, $user->name));
        }

        // For debugging purposes, send to a test email address
        // Mail::to('doniaahmad09@gmail.com')->send(new TenderNotificationMail($data, 'Test User'));

        return response()->json(['message' => 'Emails sent successfully']);
    }
}
