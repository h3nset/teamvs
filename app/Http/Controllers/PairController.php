<?php

namespace App\Http\Controllers;

use App\Models\Pair;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Http\Request;

class PairController extends Controller
{
    public function store(Request $request, Tournament $tournament, Team $team)
    {
        $validated = $request->validate([
            'player1_name' => 'required|string|max:255',
            'player2_name' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:255',
        ]);

        $validated['team_id'] = $team->id;
        $validated['sort_order'] = $team->pairs()->count();

        Pair::create($validated);

        return redirect()->back()->with('success', 'Pair created successfully.');
    }

    public function update(Request $request, Tournament $tournament, Team $team, Pair $pair)
    {
        $validated = $request->validate([
            'player1_name' => 'required|string|max:255',
            'player2_name' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:255',
        ]);

        $pair->update($validated);

        return redirect()->back()->with('success', 'Pair updated successfully.');
    }

    public function destroy(Tournament $tournament, Team $team, Pair $pair)
    {
        $pair->delete();

        return redirect()->back()->with('success', 'Pair deleted successfully.');
    }

    public function reorder(Request $request, Tournament $tournament, Team $team)
    {
        $validated = $request->validate([
            'pairs' => 'required|array',
            'pairs.*.id' => 'required|uuid|exists:pairs,id',
            'pairs.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['pairs'] as $pairData) {
            Pair::where('id', $pairData['id'])->update(['sort_order' => $pairData['sort_order']]);
        }

        return redirect()->back()->with('success', 'Pairs reordered successfully.');
    }
}
