<?php

namespace App\Http\Controllers\FitOutControllers;

use App\Http\Controllers\Controller;
use App\Models\FitOutKeyIssues;
use App\Models\FitOutProject;
use Illuminate\Http\Request;

class FitOutKeyIssuesController extends Controller
{
    
    public function index($projectId)
    {
        $header = FitOutProject::where('id', $projectId)->first();

        if (!$header) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $notes = FitOutKeyIssues::where('projectId', $projectId)->get();
        return response()->json($notes);
    }

    // Store a new note
    public function store(Request $request, $projectId)
    {
        $request->validate(['note' => 'required|string|max:255']);

        $header = FitOutProject::find($projectId);
        if (!$header) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $note = new FitOutKeyIssues();
        $note->note = $request->note;
        $note->projectId = $projectId; 
        $note->save();

        return response()->json($note, 201);
    }

    // Update an existing note
    public function update(Request $request, $id)
    {
        $request->validate(['note' => 'required|string|max:255']);

        $note = FitOutKeyIssues::find($id);
        if (!$note) {
            return response()->json(['message' => 'Note not found'], 404);
        }

        $note->note = $request->note;
        $note->save();

        return response()->json($note);
    }

    // Delete a note
    public function destroy($id)
    {
        $note = FitOutKeyIssues::find($id);
        if (!$note) {
            return response()->json(['message' => 'Note not found'], 404);
        }

        $note->delete();
        return response()->json(['message' => 'Note deleted successfully']);
    }
}





