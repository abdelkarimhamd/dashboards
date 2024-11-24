<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Interview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InterviewController extends Controller
{
    //
    public function index()
    {
        $interviews = Interview::all();
        return response()->json($interviews);
    }

    public function show($id)
    {
        $interviews = Interview::where('employee_id', $id)->get();
Log::info($interviews);
        if ($interviews->isEmpty()) {
            return response()->json(['error' => 'Interview not found'], 403);
        }

        return response()->json($interviews);
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:hr_employee,id',
            'interview_stage' => 'nullable|string',
            'scheduled_date' => 'required|date',
            'interviewer_by' => 'nullable|string',
            'feedback' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $interview = Interview::create($validatedData);

        return response()->json($interview, 201);
    }

    public function update(Request $request, $id)
    {
        $interview = Interview::find($id);

        if (!$interview) {
            return response()->json(['error' => 'Interview not found'], 404);
        }

        $validatedData = $request->validate([
            'employee_id' => 'exists:hr_employee,id',
            'interview_stage' => 'string',
            'scheduled_date' => 'date',
            'interviewer_by' => 'string',
            'feedback' => 'string',
            'rating' => 'integer|min:1|max:5',
        ]);

        $interview->update($validatedData);

        return response()->json($interview);
    }

    public function destroy($id)
    {
        $interview = Interview::find($id);

        if (!$interview) {
            return response()->json(['error' => 'Interview not found'], 404);
        }

        $interview->delete();

        return response()->json(['message' => 'Interview deleted successfully']);
    }
}
