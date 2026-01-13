<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeamController extends Controller
{
    public function index(Tournament $tournament)
    {
        $teams = $tournament->teams()->with('pairs')->get();

        return Inertia::render('Teams/Index', [
            'tournament' => $tournament,
            'teams' => $teams,
        ]);
    }

    public function store(Request $request, Tournament $tournament)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $validated['tournament_id'] = $tournament->id;
        $validated['sort_order'] = $tournament->teams()->count();

        $team = Team::create($validated);

        return redirect()->back()->with('success', 'Team created successfully.');
    }

    public function update(Request $request, Tournament $tournament, Team $team)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $team->update($validated);

        return redirect()->back()->with('success', 'Team updated successfully.');
    }

    public function destroy(Tournament $tournament, Team $team)
    {
        $team->delete();

        return redirect()->back()->with('success', 'Team deleted successfully.');
    }

    public function reorder(Request $request, Tournament $tournament)
    {
        $validated = $request->validate([
            'teams' => 'required|array',
            'teams.*.id' => 'required|uuid|exists:teams,id',
            'teams.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['teams'] as $teamData) {
            Team::where('id', $teamData['id'])->update(['sort_order' => $teamData['sort_order']]);
        }

        return redirect()->back()->with('success', 'Teams reordered successfully.');
    }
}
