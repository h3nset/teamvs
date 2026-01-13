<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Pair;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Support\Collection;

class StatisticsService
{
    /**
     * Get comprehensive tournament statistics.
     */
    public function getTournamentStats(Tournament $tournament): array
    {
        $matches = $tournament->matches()->with('scores')->get();
        $completedMatches = $matches->where('status', 'completed');
        
        $totalPoints = $completedMatches->sum(function ($match) {
            return $match->scores->sum('home_score') + $match->scores->sum('away_score');
        });
        
        $matchCount = $completedMatches->count();
        
        return [
            'total_matches' => $matches->count(),
            'completed_matches' => $matchCount,
            'in_progress_matches' => $matches->where('status', 'in_progress')->count(),
            'scheduled_matches' => $matches->where('status', 'scheduled')->count(),
            'total_points_scored' => $totalPoints,
            'average_points_per_match' => $matchCount > 0 ? round($totalPoints / $matchCount, 1) : 0,
            'completion_rate' => $matches->count() > 0 
                ? round(($matchCount / $matches->count()) * 100, 1) 
                : 0,
        ];
    }

    /**
     * Get match highlights (highest scoring, closest, etc.).
     */
    public function getMatchHighlights(Tournament $tournament): array
    {
        $matches = $tournament->matches()
            ->where('status', 'completed')
            ->with(['scores', 'homePair.team', 'awayPair.team'])
            ->get();
        
        if ($matches->isEmpty()) {
            return [
                'highest_scoring' => null,
                'closest_match' => null,
                'biggest_blowout' => null,
            ];
        }

        // Calculate totals for each match
        $matchStats = $matches->map(function ($match) {
            $homeTotal = $match->scores->sum('home_score');
            $awayTotal = $match->scores->sum('away_score');
            
            return [
                'match' => $match,
                'total_points' => $homeTotal + $awayTotal,
                'point_diff' => abs($homeTotal - $awayTotal),
                'home_total' => $homeTotal,
                'away_total' => $awayTotal,
            ];
        });

        $highestScoring = $matchStats->sortByDesc('total_points')->first();
        $closestMatch = $matchStats->sortBy('point_diff')->first();
        $biggestBlowout = $matchStats->sortByDesc('point_diff')->first();

        return [
            'highest_scoring' => $highestScoring ? $this->formatMatchHighlight($highestScoring) : null,
            'closest_match' => $closestMatch ? $this->formatMatchHighlight($closestMatch) : null,
            'biggest_blowout' => $biggestBlowout ? $this->formatMatchHighlight($biggestBlowout) : null,
        ];
    }

    /**
     * Format a match highlight for display.
     */
    private function formatMatchHighlight(array $stats): array
    {
        $match = $stats['match'];
        
        return [
            'id' => $match->id,
            'round_number' => $match->round_number,
            'match_number' => $match->match_number,
            'home_pair' => [
                'id' => $match->homePair->id,
                'name' => $match->homePair->display_name,
                'team' => $match->homePair->team->name,
                'team_color' => $match->homePair->team->color,
            ],
            'away_pair' => [
                'id' => $match->awayPair->id,
                'name' => $match->awayPair->display_name,
                'team' => $match->awayPair->team->name,
                'team_color' => $match->awayPair->team->color,
            ],
            'home_score' => $stats['home_total'],
            'away_score' => $stats['away_total'],
            'total_points' => $stats['total_points'],
            'point_diff' => $stats['point_diff'],
        ];
    }

    /**
     * Get team statistics.
     */
    public function getTeamStats(Team $team): array
    {
        $team->load('pairs.homeMatches.scores', 'pairs.awayMatches.scores');
        
        $stats = [
            'total_points' => 0,
            'points_against' => 0,
            'wins' => 0,
            'losses' => 0,
            'matches_played' => 0,
        ];
        
        foreach ($team->pairs as $pair) {
            $pairStats = $this->getPairStats($pair);
            $stats['total_points'] += $pairStats['points_for'];
            $stats['points_against'] += $pairStats['points_against'];
            $stats['wins'] += $pairStats['wins'];
            $stats['losses'] += $pairStats['losses'];
            $stats['matches_played'] += $pairStats['matches_played'];
        }
        
        $stats['point_diff'] = $stats['total_points'] - $stats['points_against'];
        $stats['win_rate'] = $stats['matches_played'] > 0 
            ? round(($stats['wins'] / $stats['matches_played']) * 100, 1) 
            : 0;
        
        return $stats;
    }

    /**
     * Get detailed pair statistics.
     */
    public function getPairStats(Pair $pair): array
    {
        $homeMatches = $pair->homeMatches()->where('status', 'completed')->with('scores')->get();
        $awayMatches = $pair->awayMatches()->where('status', 'completed')->with('scores')->get();
        
        $stats = [
            'matches_played' => 0,
            'wins' => 0,
            'losses' => 0,
            'points_for' => 0,
            'points_against' => 0,
            'close_wins' => 0,  // Won by ≤3 points
        ];
        
        // Process home matches
        foreach ($homeMatches as $match) {
            $homeTotal = $match->scores->sum('home_score');
            $awayTotal = $match->scores->sum('away_score');
            
            $stats['matches_played']++;
            $stats['points_for'] += $homeTotal;
            $stats['points_against'] += $awayTotal;
            
            if ($homeTotal > $awayTotal) {
                $stats['wins']++;
                if (($homeTotal - $awayTotal) <= 3) {
                    $stats['close_wins']++;
                }
            } else {
                $stats['losses']++;
            }
        }
        
        // Process away matches
        foreach ($awayMatches as $match) {
            $homeTotal = $match->scores->sum('home_score');
            $awayTotal = $match->scores->sum('away_score');
            
            $stats['matches_played']++;
            $stats['points_for'] += $awayTotal;
            $stats['points_against'] += $homeTotal;
            
            if ($awayTotal > $homeTotal) {
                $stats['wins']++;
                if (($awayTotal - $homeTotal) <= 3) {
                    $stats['close_wins']++;
                }
            } else {
                $stats['losses']++;
            }
        }
        
        $stats['point_diff'] = $stats['points_for'] - $stats['points_against'];
        $stats['win_rate'] = $stats['matches_played'] > 0 
            ? round(($stats['wins'] / $stats['matches_played']) * 100, 1) 
            : 0;
        
        return $stats;
    }

    /**
     * Get leaderboard for a tournament (all pairs or filtered by team).
     */
    public function getLeaderboard(Tournament $tournament, ?Team $team = null): Collection
    {
        $query = Pair::query()
            ->whereHas('team', function ($q) use ($tournament) {
                $q->where('tournament_id', $tournament->id);
            })
            ->with('team');
        
        if ($team) {
            $query->where('team_id', $team->id);
        }
        
        $pairs = $query->get();
        
        return $pairs->map(function ($pair) {
            $stats = $this->getPairStats($pair);
            
            return [
                'id' => $pair->id,
                'name' => $pair->display_name,
                'player1' => $pair->player1_name,
                'player2' => $pair->player2_name,
                'team_id' => $pair->team_id,
                'team_name' => $pair->team->name,
                'team_color' => $pair->team->color,
                ...$stats,
            ];
        })->sortByDesc('points_for')->values();
    }
}
