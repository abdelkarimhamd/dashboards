<?php

namespace App\Http\Controllers\PLDashboardControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Plnote;
use Illuminate\Support\Facades\Validator;

class InsertYearNotes extends Controller
{
    // Add a new note
    public function addNote(Request $request, $year)
    {
                            // Get the authenticated user and their branch
                            $user = auth()->user();
                            $userBranch = $user->branch;
                    
                            // Check if the branch is available
                            if (!$userBranch) {
                                return response()->json(['error' => 'User branch not found'], 400);
                            }
        $validator = Validator::make($request->all(), [
            'note' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $note = new PlNote();  
        $note->year = $year;
        $note->branch = $userBranch;
        $note->notes = $request->input('note');
        $note->save();

        return response()->json(['message' => 'Note created successfully', 'note' => $note], 201);
    }

    // Update an existing note
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'note' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $note = PlNote::find($id);
        if (!$note) {
            return response()->json(['message' => 'Note not found'], 404);
        }

        $note->notes = $request->input('note');
        $note->save();

        return response()->json(['message' => 'Note updated successfully', 'note' => $note]);
    }

    // Delete a note
    public function destroy($id)
    {
        $note = PlNote::find($id);
        if (!$note) {
            return response()->json(['message' => 'Note not found'], 404);
        }

        $note->delete();
        return response()->json(['message' => 'Note deleted successfully']);
    }

    public function fetchNotes($year) {
        try {
             // Get the authenticated user and their branch
             $user = auth()->user();
             $userBranch = $user->branch;
     
             // Check if the branch is available
             if (!$userBranch) {
                 return response()->json(['error' => 'User branch not found'], 400);
             }

            $notes = Plnote::where('year', $year)->where('branch', $userBranch)->get();
            return response()->json($notes);
        } catch (\Exception $e) {
            
            return response()->json(['error' => 'Server error'], 500);
        }
    }
}