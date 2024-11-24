<?php

namespace App\Http\Controllers\ProcurementControllers;

use App\Http\Controllers\Controller;
use App\Models\ProcurementModels\ProcurementNotes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class ProcurementNotesController extends Controller
{
   // Add a new note
   public function addNote(Request $request, $month)
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

       $note = new ProcurementNotes();  
       $note->month = $month;
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

       $note = ProcurementNotes::find($id);
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
       $note = ProcurementNotes::find($id);
       if (!$note) {
           return response()->json(['message' => 'Note not found'], 404);
       }

       $note->delete();
       return response()->json(['message' => 'Note deleted successfully']);
   }

   // Fetch notes based on the month and user's branch
   public function fetchNotes($month) {
       try {
           // Get the authenticated user and their branch
           $user = auth()->user();
           $userBranch = $user->branch;

           // Check if the branch is available
           if (!$userBranch) {
               return response()->json(['error' => 'User branch not found'], 400);
           }

           // Retrieve notes based on the month and branch
           $notes = ProcurementNotes::where('month', $month)
               ->where('branch', $userBranch)
               ->get();

           return response()->json($notes);
       } catch (\Exception $e) {
           return response()->json(['error' => 'Server error'], 500);
       }
   }
}
