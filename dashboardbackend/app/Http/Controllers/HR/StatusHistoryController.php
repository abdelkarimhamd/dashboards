<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\StatusHistory;
use Illuminate\Http\Request;

class StatusHistoryController extends Controller
{
    //
    public function index()
    {
        $statusHistories = StatusHistory::all();
        return response()->json($statusHistories);
    }

    public function show($id)
    {
        $statusHistory = StatusHistory::find($id);

        if (!$statusHistory) {
            return response()->json(['error' => 'Status history not found'], 404);
        }

        return response()->json($statusHistory);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:hr_employee,id',
            'old_statusenum' => 'nullable|string',
            'new_status' => 'required|string',
            'change_date' => 'required|date',
            'changed_by' => 'required|exists:hr_user,id',
        ]);

        $statusHistory = StatusHistory::create($validatedData);

        return response()->json($statusHistory, 201);
    }

    public function update(Request $request, $id)
    {
        $statusHistory = StatusHistory::find($id);

        if (!$statusHistory) {
            return response()->json(['error' => 'Status history not found'], 404);
        }

        $validatedData = $request->validate([
            'employee_id' => 'exists:hr_employee,id',
            'old_statusenum' => 'nullable|string',
            'new_status' => 'string',
            'change_date' => 'date',
            'changed_by' => 'exists:hr_user,id',
        ]);

        $statusHistory->update($validatedData);

        return response()->json($statusHistory);
    }

    public function destroy($id)
    {
        $statusHistory = StatusHistory::find($id);

        if (!$statusHistory) {
            return response()->json(['error' => 'Status history not found'], 404);
        }

        $statusHistory->delete();

        return response()->json(['message' => 'Status history deleted successfully']);
    }
}
