<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Position;
use App\Models\HR\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PositionController extends Controller
{
    //
    public function index()
    {
        $positions = Position::all(); // Fetch all positions
        return response()->json($positions);
    }

    // Get a single position by ID
    public function show($id)
    {
        $position = Position::find($id);

        if (!$position) {
            return response()->json(['error' => 'Position not found'], 404);
        }

        return response()->json($position);
    }

    // Store a new position
    public function store(Request $request)
    {
        Log::info('store function called ');
        $validatedData = $request->validate([
            'position_name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        $position = Position::create($validatedData); // Create a new position

        return response()->json($position, 201); // Return the created position
    }

    // Update an existing position
    public function update(Request $request, $id)
    {
        $position = Position::find($id);

        if (!$position) {
            return response()->json(['error' => 'Position not found'], 404);
        }

        $validatedData = $request->validate([
            'position_name' => 'string|max:255',
            'description' => 'string|max:255',
        ]);

        $position->update($validatedData); // Update the position

        return response()->json($position); // Return the updated position
    }

    // Delete a position
    public function destroy($id)
    {
        $position = Position::find($id);

        if (!$position) {
            return response()->json(['error' => 'Position not found'], 404);
        }

        $position->delete(); // Delete the position

    }
}
