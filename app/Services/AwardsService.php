<?php

namespace App\Services;

use App\Models\Pair;
use App\Models\Tournament;

class AwardsService
{
    public function __construct(
        private StatisticsService $statisticsService
    ) {}

    /**
     * Calculate all awards for a completed tournament.
     */
    public function calculateAwards(Tournament $tournament): array
    {
        $leaderboard = $this->statisticsService->getLeaderboard($tournament);
        
        return [
            'mvp_pair' => $this->getMVPPair($leaderboard),
            'top_performer' => $this->getTopPerformer($leaderboard),
            'ironman' => $this->getIronman($leaderboard),
            'clutch_pair' => $this->getClutchPair($leaderboard),
            'winning_team' => $this->getWinningTeam($tournament),
        ];
    }

    /**
     * Get MVP pair (highest points with win rate bonus).
     */
    public function getMVPPair($leaderboard): ?array
    {
        if ($leaderboard->isEmpty()) {
            return null;
        }

        return $leaderboard->map(function ($pair) {
            // MVP Score = Points × (1 + win_rate/200)
            $winRateBonus = 1 + ($pair['win_rate'] / 200);
            $mvpScore = $pair['points_for'] * $winRateBonus;
            
            return [
                ...$pair,
                'mvp_score' => round($mvpScore, 1),
            ];
        })->sortByDesc('mvp_score')->first();
    }

    /**
     * Get top performer (highest win rate, min 3 matches).
     */
    public function getTopPerformer($leaderboard): ?array
    {
        $eligible = $leaderboard->filter(fn($p) => $p['matches_played'] >= 3);
        
        if ($eligible->isEmpty()) {
            return null;
        }

        return $eligible->sortByDesc('win_rate')->first();
    }

    /**
     * Get ironman (most matches played).
     */
    public function getIronman($leaderboard): ?array
    {
        if ($leaderboard->isEmpty()) {
            return null;
        }

        return $leaderboard->sortByDesc('matches_played')->first();
    }

    /**
     * Get clutch pair (most close wins).
     */
    public function getClutchPair($leaderboard): ?array
    {
        $withCloseWins = $leaderboard->filter(fn($p) => $p['close_wins'] > 0);
        
        if ($withCloseWins->isEmpty()) {
            return null;
        }

        return $withCloseWins->sortByDesc('close_wins')->first();
    }

    /**
     * Get the winning team of a tournament.
     */
    public function getWinningTeam(Tournament $tournament): ?array
    {
        $tournament->load('teams.pairs');
        
        $teamStats = $tournament->teams->map(function ($team) {
            $stats = $this->statisticsService->getTeamStats($team);
            
            return [
                'id' => $team->id,
                'name' => $team->name,
                'color' => $team->color,
                ...$stats,
            ];
        });

        if ($teamStats->isEmpty()) {
            return null;
        }

        return $teamStats->sortByDesc('total_points')->first();
    }
}
