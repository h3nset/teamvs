<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Services\ScoringService;
use Inertia\Inertia;

class ScoreboardController extends Controller
{
    public function __construct(
        private ScoringService $scoringService
    ) {}

    public function show(Tournament $tournament)
    {
        $tournament->load(['teams.pairs', 'matches.homePair.team', 'matches.awayPair.team', 'matches.scores']);
        
        $standings = $this->scoringService->getTournamentStandings($tournament);
        
        $activeMatches = $tournament->matches
            ->where('status', 'in_progress')
            ->values();
        
        $recentlyCompleted = $tournament->matches
            ->where('status', 'completed')
            ->sortByDesc('ended_at')
            ->take(5)
            ->values();
        
        $upcomingMatches = $tournament->matches
            ->where('status', 'scheduled')
            ->take(5)
            ->values();

        return Inertia::render('Scoreboard/Show', [
            'tournament' => $tournament,
            'standings' => $standings,
            'activeMatches' => $activeMatches,
            'recentlyCompleted' => $recentlyCompleted,
            'upcomingMatches' => $upcomingMatches,
        ]);
    }

    public function tv(Tournament $tournament)
    {
        // Same data as show, but rendered with TV-optimized layout
        $tournament->load(['teams.pairs', 'matches.homePair.team', 'matches.awayPair.team', 'matches.scores']);
        
        $standings = $this->scoringService->getTournamentStandings($tournament);
        
        $activeMatches = $tournament->matches
            ->where('status', 'in_progress')
            ->values();

        return Inertia::render('Scoreboard/TV', [
            'tournament' => $tournament,
            'standings' => $standings,
            'activeMatches' => $activeMatches,
        ]);
    }
}
