<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Score;
use Illuminate\Support\Facades\DB;

class ScoringService
{
    /**
     * Record or update a score for a match set.
     */
    public function recordScore(
        GameMatch $match,
        int $setNumber,
        int $homeScore,
        int $awayScore,
        ?string $deviceId = null,
        bool $isTiebreak = false
    ): Score {
        return DB::transaction(function () use ($match, $setNumber, $homeScore, $awayScore, $deviceId, $isTiebreak) {
            // Find existing score or create new
            $score = Score::updateOrCreate(
                [
                    'match_id' => $match->id,
                    'set_number' => $setNumber,
                ],
                [
                    'home_score' => $homeScore,
                    'away_score' => $awayScore,
                    'is_tiebreak' => $isTiebreak,
                    'device_id' => $deviceId,
                    'recorded_at' => now(),
                ]
            );

            // Update match status if needed
            if ($match->status === 'scheduled') {
                $match->update([
                    'status' => 'in_progress',
                    'started_at' => now(),
                ]);
            }

            return $score;
        });
    }

    /**
     * Complete a match and calculate final results.
     */
    public function completeMatch(GameMatch $match): GameMatch
    {
        $match->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        return $match->fresh(['scores', 'homePair', 'awayPair']);
    }

    /**
     * Get match results summary.
     */
    public function getMatchSummary(GameMatch $match): array
    {
        $match->load('scores', 'homePair.team', 'awayPair.team');
        
        $homeTotalScore = $match->scores->sum('home_score');
        $awayTotalScore = $match->scores->sum('away_score');
        
        return [
            'match_id' => $match->id,
            'status' => $match->status,
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
            'sets' => $match->scores->map(fn($score) => [
                'set' => $score->set_number,
                'home' => $score->home_score,
                'away' => $score->away_score,
                'is_tiebreak' => $score->is_tiebreak,
            ]),
            'total' => [
                'home' => $homeTotalScore,
                'away' => $awayTotalScore,
            ],
            'winner' => $match->status === 'completed' 
                ? ($homeTotalScore > $awayTotalScore ? 'home' : 'away')
                : null,
        ];
    }

    /**
     * Get tournament standings.
     */
    public function getTournamentStandings($tournament): array
    {
        $tournament->load(['teams.pairs.homeMatches.scores', 'teams.pairs.awayMatches.scores']);
        
        $teamStandings = [];
        
        foreach ($tournament->teams as $team) {
            $teamData = [
                'id' => $team->id,
                'name' => $team->name,
                'color' => $team->color,
                'total_points' => 0,
                'total_wins' => 0,
                'matches_played' => 0,
                'pairs' => [],
            ];

            foreach ($team->pairs as $pair) {
                $pairStats = $this->calculatePairStats($pair);
                $teamData['pairs'][] = $pairStats;
                $teamData['total_points'] += $pairStats['points'];
                $teamData['total_wins'] += $pairStats['wins'];
                $teamData['matches_played'] += $pairStats['matches_played'];
            }

            $teamStandings[] = $teamData;
        }

        // Sort by total points descending
        usort($teamStandings, fn($a, $b) => $b['total_points'] <=> $a['total_points']);

        return $teamStandings;
    }

    /**
     * Calculate statistics for a single pair.
     */
    private function calculatePairStats($pair): array
    {
        $points = 0;
        $wins = 0;
        $losses = 0;
        $matchesPlayed = 0;

        $allMatches = $pair->homeMatches->merge($pair->awayMatches);

        foreach ($allMatches as $match) {
            if ($match->status !== 'completed') continue;
            
            $matchesPlayed++;
            $homeTotal = $match->scores->sum('home_score');
            $awayTotal = $match->scores->sum('away_score');

            if ($match->home_pair_id === $pair->id) {
                $points += $homeTotal;
                if ($homeTotal > $awayTotal) $wins++;
                else $losses++;
            } else {
                $points += $awayTotal;
                if ($awayTotal > $homeTotal) $wins++;
                else $losses++;
            }
        }

        return [
            'id' => $pair->id,
            'name' => $pair->display_name,
            'points' => $points,
            'wins' => $wins,
            'losses' => $losses,
            'matches_played' => $matchesPlayed,
        ];
    }
}
