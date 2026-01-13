<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Tournament;
use App\Services\StatisticsService;
use Inertia\Inertia;

class LeaderboardController extends Controller
{
    public function __construct(
        private StatisticsService $statisticsService
    ) {}

    public function show(Tournament $tournament)
    {
        $tournament->load('teams');
        
        $leaderboard = $this->statisticsService->getLeaderboard($tournament);
        $tournamentStats = $this->statisticsService->getTournamentStats($tournament);
        
        return Inertia::render('Leaderboard/Show', [
            'tournament' => $tournament,
            'teams' => $tournament->teams,
            'leaderboard' => $leaderboard,
            'stats' => $tournamentStats,
        ]);
    }

    public function teamLeaderboard(Tournament $tournament, Team $team)
    {
        $leaderboard = $this->statisticsService->getLeaderboard($tournament, $team);
        $teamStats = $this->statisticsService->getTeamStats($team);
        
        return Inertia::render('Leaderboard/Team', [
            'tournament' => $tournament,
            'team' => $team,
            'leaderboard' => $leaderboard,
            'stats' => $teamStats,
        ]);
    }
}
