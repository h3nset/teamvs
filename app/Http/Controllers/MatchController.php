<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\Tournament;
use App\Rules\CrossTeamMatch;
use App\Services\ScoringService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MatchController extends Controller
{
    public function __construct(
        private ScoringService $scoringService
    ) {}

    public function index(Tournament $tournament)
    {
        $matches = $tournament->matches()
            ->with(['homePair.team', 'awayPair.team', 'scores'])
            ->orderBy('round_number')
            ->orderBy('match_number')
            ->get()
            ->groupBy('round_number');

        return Inertia::render('Matches/Index', [
            'tournament' => $tournament,
            'matchesByRound' => $matches,
        ]);
    }

    public function show(Tournament $tournament, GameMatch $match)
    {
        $match->load(['homePair.team', 'awayPair.team', 'scores']);
        
        $summary = $this->scoringService->getMatchSummary($match);

        return Inertia::render('Matches/Show', [
            'tournament' => $tournament,
            'match' => $match,
            'summary' => $summary,
        ]);
    }

    public function scoreInput(Tournament $tournament, GameMatch $match)
    {
        $match->load(['homePair.team', 'awayPair.team', 'scores']);

        return Inertia::render('Matches/ScoreInput', [
            'tournament' => $tournament,
            'match' => $match,
            'pointsPerSet' => $tournament->points_per_set,
        ]);
    }

    public function storeScore(Request $request, Tournament $tournament, GameMatch $match)
    {
        $validated = $request->validate([
            'set_number' => 'required|integer|min:1',
            'home_score' => 'required|integer|min:0',
            'away_score' => 'required|integer|min:0',
            'is_tiebreak' => 'boolean',
            'device_id' => 'nullable|string',
        ]);

        $this->scoringService->recordScore(
            $match,
            $validated['set_number'],
            $validated['home_score'],
            $validated['away_score'],
            $validated['device_id'] ?? null,
            $validated['is_tiebreak'] ?? false
        );

        return redirect()->back()->with('success', 'Score saved.');
    }

    public function complete(Tournament $tournament, GameMatch $match)
    {
        $this->scoringService->completeMatch($match);

        return redirect()->route('tournaments.matches.show', [$tournament, $match])
            ->with('success', 'Match completed.');
    }

    public function start(Tournament $tournament, GameMatch $match)
    {
        $match->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Match started.');
    }
}
