<?php

namespace App\Http\Controllers\TenderingControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TenderNote;
use Illuminate\Support\Facades\Auth;
class TenderNotesController extends Controller
{ /**
    * Display a listing of notes for a specific tender.
    */
    public function index($tenderId)
    {
        // Eager load the related user data for created_by and updated_by
        $notes = TenderNote::where('tender_id', $tenderId)
            ->with(['creator:id,name', 'updater:id,name'])  // Only fetch id and name for efficiency
            ->get();

        return response()->json($notes);
    }

   /**
    * Store a newly created note in storage.
    */
   public function store(Request $request, $tenderId)
   {
       $request->validate([
           'note' => 'required|string',
       ]);

       $note = new TenderNote();
       $note->tender_id = $tenderId;
       $note->note = $request->input('note');
       $note->created_by = Auth::id();
       $note->updated_by = Auth::id();
       $note->save();

       return response()->json($note, 201);
   }

   /**
    * Update the specified note in storage.
    */
   public function update(Request $request, $id)
   {
       $request->validate([
           'note' => 'required|string',
       ]);

       $note = TenderNote::findOrFail($id);
       $note->note = $request->input('note');
       $note->updated_by = Auth::id();
       $note->save();

       return response()->json($note);
   }

   /**
    * Remove the specified note from storage.
    */
   public function destroy($id)
   {
       $note = TenderNote::findOrFail($id);
       $note->delete();

       return response()->json(['message' => 'Note deleted successfully']);
   }
}
