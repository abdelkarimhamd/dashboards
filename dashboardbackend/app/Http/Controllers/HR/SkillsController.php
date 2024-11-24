<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Skills;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SkillsController extends Controller
{
    //

    public function index()
    {
        return Skills::all();
    }

    // Get a single skill by ID
    public function show($id)
    {
        $skill = Skills::find($id);
        if (!$skill) {
            return response()->json(['error' => 'Skill not found'], 404);
        }
        return response()->json($skill);
    }

    // Create a new skill
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'employee_id' => 'required|exists:hr_employee,id',
            'skill_name' => 'required|string|max:255',
            'proficiency_level' => 'required|string|max:200',
        ]);
        Log::info("skill");
        Log::info($validatedData);
        $skill = Skills::create($validatedData);
        return response()->json($skill, 201);
    }

    // Update an existing skill
    public function update(Request $request, $id)
    {
        $skill = Skills::find($id);
        if (!$skill) {
            return response()->json(['error' => 'Skill not found'], 404);
        }

        $validatedData = $request->validate([
            'skill_name' => 'string|max:255',
            'proficiency_level' => 'string|max:50',
        ]);

        $skill->update($validatedData);
        return response()->json($skill);
    }

    // Delete a skill
    public function destroy($id)
    {
        $skill = Skills::find($id);
        if (!$skill) {
            return response()->json(['error' => 'Skill not found'], 404);
        }

        $skill->delete();
        return response()->json(['message' => 'Skill deleted successfully']);
    }
}
