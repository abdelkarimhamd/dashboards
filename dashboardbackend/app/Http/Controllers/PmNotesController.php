<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PmNote;
use App\Models\Header;

class PmNotesController extends Controller
{
    public function show(Request $request, $id)
    {
        $header = Header::find($id);
        if (!$header) {
            return response()->json(['message' => 'Project not found'], 404);
        }
        $note = PmNote::where('projectId', $id)->first();

        if (!$note) {
            return response()->json(['note' => ''], 200);
        }

        return response()->json(['note' => $note->note], 200);
    }

    public function storeOrUpdate(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|string',
        ]);

        $header = Header::find($id);
        if (!$header) {
            return response()->json(['message' => 'Project not found'], 404);
        }

        $note = PmNote::updateOrCreate(
            ['projectId' => $id],
            ['note' => $request->note]
        );

        return response()->json(['message' => 'Note saved successfully'], 200);
    }
}
