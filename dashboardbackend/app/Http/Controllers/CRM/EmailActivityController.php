<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Activity;
use Illuminate\Http\Request;

class EmailActivityController extends Controller
{
    /**
     * Fetch email activities with optional filtering.
     */
    public function index(Request $request)
    {
        // Apply filters if needed (e.g., by date range, company, user)
        $query = Activity::where('type', 'email');

        // Example filter: filter by email received date
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('email_received_at', [$request->start_date, $request->end_date]);
        }

        // Get paginated email activities
        $activities = $query->paginate(10);

        return response()->json($activities);
    }
}
