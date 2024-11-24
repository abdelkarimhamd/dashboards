<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Activity;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Display a listing of the activities.
     */
    public function index()
    {
        // Retrieve activities belonging to the authenticated user
        $activities = Activity::all();

        return response()->json($activities, 200);
    }

    /**
     * Store a newly created activity in storage.
     */
    public function store(Request $request)
    {
        // Step 1: Validate the incoming request
        $request->validate([
            'type'           => 'required|string|max:50', // e.g., Call, Email, Meeting
            'description'    => 'nullable|string',
            'outcome'        => 'nullable|string|max:255',
            'activity_date'  => 'nullable|date',
            'company_id'     => 'nullable|exists:CRM_companies,id',
            'lead_id'        => 'nullable|exists:CRM_leads,id',
            'deal_id'        => 'nullable|exists:CRM_deals,id',
            'email_from'        => 'nullable',
            'email_subject'        => 'nullable',
            'email_received_at'        => 'nullable',
            // 'user_id'      => 'required|exists:CRM_users,id', // Removed, we'll get user_id from auth
        ]);

        // Step 2: Create the activity with the authenticated user ID

        // Convert activity_date to MySQL format
        $activityData = $request->all();
        $activityData['activity_date'] = Carbon::parse($activityData['activity_date'])->format('Y-m-d H:i:s');

        // Add the authenticated user ID
        $activityData['user_id'] = auth()->id();

        // Create the activity
        $activity = Activity::create($activityData);

        // Return a success response
        return response()->json([
            'message'  => 'Activity created successfully',
            'activity' => $activity,
        ], 201);
    }

    /**
     * Display the specified activity.
     */
    public function show($id)
    {
        // Find the activity belonging to the authenticated user
        $activity = Activity::where('id', $id)
            // ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json($activity, 200);
    }

    /**
     * Update the specified activity in storage.
     */
    public function update(Request $request, $id)
    {
        // Step 1: Validate the incoming request
        $request->validate([
            'type'           => 'sometimes|required|string|max:50',
            'description'    => 'nullable|string',
            'outcome'        => 'nullable|string|max:255',
            'activity_date'  => 'required|date',
            'company_id'     => 'nullable|exists:CRM_companies,id',
            'lead_id'        => 'nullable|exists:CRM_leads,id',
            'email_from'        => 'nullable',
            'email_subject'        => 'nullable',
            'email_received_at'        => 'nullable',
            // 'user_id'      => 'nullable|exists:CRM_users,id', // Removed, we don't update user_id
        ]);

        // Step 2: Find the activity belonging to the authenticated user
        $activity = Activity::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Step 3: Update the activity without changing the user_id
        $activity->update($request->except('user_id'));

        // Step 4: Return a success response
        return response()->json([
            'message'  => 'Activity updated successfully',
            'activity' => $activity,
        ], 200);
    }

    /**
     * Remove the specified activity from storage (soft delete).
     */
    public function destroy($id)
    {
        // Find the activity belonging to the authenticated user
        $activity = Activity::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Step 1: Soft delete the activity
        $activity->delete();

        // Step 2: Return a success response
        return response()->json([
            'message' => 'Activity deleted successfully',
        ], 200);
    }
}
