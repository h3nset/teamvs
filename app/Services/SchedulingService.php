<?php

namespace App\Services;

use App\Models\GameMatch;
use App\Models\Pair;
use App\Models\Tournament;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SchedulingService
{
    /**
     * Generate a complete match schedule for a tournament.
     * 
     * Algorithm: Cross-Team Round Robin
     * - Each match pairs one pair from Team X with one pair from Team Y
     * - No intra-team matches allowed
     * - Each pair plays at most maxMatchesPerPair matches
     * - Each pair plays at most once per round
     * - Deterministic output: same input always produces same schedule
     */
    public function generateSchedule(Tournament $tournament): Collection
    {
        // Validate tournament state
        if (!$tournament->canGenerateSchedule()) {
            throw new \InvalidArgumentException(
                'Tournament must be in draft status with at least 2 teams to generate schedule.'
            );
        }

        // Get all pairs grouped by team
        $pairsByTeam = $this->getPairsGroupedByTeam($tournament);
        
        if ($pairsByTeam->count() < 2) {
            throw new \InvalidArgumentException('At least 2 teams with pairs are required.');
        }

        // Generate all possible cross-team pairings
        $allPairings = $this->generateAllCrossTeamPairings($pairsByTeam);

        // Build the round-robin schedule
        return $this->buildRoundRobinSchedule(
            $tournament,
            $allPairings,
            $tournament->rounds,
            $tournament->max_matches_per_pair
        );
    }

    /**
     * Get all pairs organized by team ID.
     */
    private function getPairsGroupedByTeam(Tournament $tournament): Collection
    {
        return $tournament->teams()
            ->with('pairs')
            ->get()
            ->mapWithKeys(function ($team) {
                return [$team->id => $team->pairs->sortBy('sort_order')->values()];
            })
            ->filter(fn($pairs) => $pairs->isNotEmpty());
    }

    /**
     * Generate all possible cross-team match pairings.
     * Pairings are sorted deterministically for consistent output.
     */
    private function generateAllCrossTeamPairings(Collection $pairsByTeam): array
    {
        $pairings = [];
        $teamIds = $pairsByTeam->keys()->sort()->values()->toArray();
        $teamCount = count($teamIds);

        // Iterate through all team combinations (i < j to avoid duplicates)
        for ($i = 0; $i < $teamCount; $i++) {
            for ($j = $i + 1; $j < $teamCount; $j++) {
                $team1Id = $teamIds[$i];
                $team2Id = $teamIds[$j];
                
                $team1Pairs = $pairsByTeam[$team1Id];
                $team2Pairs = $pairsByTeam[$team2Id];

                // Generate all pair combinations between these two teams
                foreach ($team1Pairs as $pair1) {
                    foreach ($team2Pairs as $pair2) {
                        $pairings[] = [
                            'home' => $pair1,
                            'away' => $pair2,
                            'sort_key' => "{$team1Id}_{$pair1->id}_{$team2Id}_{$pair2->id}",
                        ];
                    }
                }
            }
        }

        // Sort deterministically for consistent schedule generation
        usort($pairings, fn($a, $b) => strcmp($a['sort_key'], $b['sort_key']));

        return $pairings;
    }

    /**
     * Build the round-robin schedule respecting all constraints.
     */
    private function buildRoundRobinSchedule(
        Tournament $tournament,
        array $allPairings,
        int $maxRounds,
        int $maxMatchesPerPair
    ): Collection {
        $schedule = collect();
        $pairMatchCount = []; // Track matches per pair
        $usedPairings = [];   // Track used pairings to prevent rematches
        $matchNumber = 0;

        for ($roundNum = 1; $roundNum <= $maxRounds; $roundNum++) {
            $roundMatches = [];
            $pairsInRound = []; // A pair can only play once per round

            foreach ($allPairings as $pairing) {
                $homeId = $pairing['home']->id;
                $awayId = $pairing['away']->id;
                $pairingKey = "{$homeId}_{$awayId}";

                // Skip if this pairing was already used
                if (isset($usedPairings[$pairingKey])) {
                    continue;
                }

                // Skip if either pair is already playing this round
                if (isset($pairsInRound[$homeId]) || isset($pairsInRound[$awayId])) {
                    continue;
                }

                // Skip if either pair has reached max matches
                $homeCount = $pairMatchCount[$homeId] ?? 0;
                $awayCount = $pairMatchCount[$awayId] ?? 0;
                
                if ($homeCount >= $maxMatchesPerPair || $awayCount >= $maxMatchesPerPair) {
                    continue;
                }

                // Valid match found - add to schedule
                $matchNumber++;
                $roundMatches[] = [
                    'home_pair' => $pairing['home'],
                    'away_pair' => $pairing['away'],
                    'round_number' => $roundNum,
                    'match_number' => $matchNumber,
                ];

                // Update tracking
                $pairsInRound[$homeId] = true;
                $pairsInRound[$awayId] = true;
                $usedPairings[$pairingKey] = true;
                $pairMatchCount[$homeId] = $homeCount + 1;
                $pairMatchCount[$awayId] = $awayCount + 1;
            }

            if (!empty($roundMatches)) {
                $schedule->push([
                    'round' => $roundNum,
                    'matches' => $roundMatches,
                ]);
            }
        }

        return $schedule;
    }

    /**
     * Save the generated schedule to the database.
     */
    public function saveSchedule(Tournament $tournament, Collection $schedule): void
    {
        DB::transaction(function () use ($tournament, $schedule) {
            // Clear existing matches if regenerating
            $tournament->matches()->delete();

            foreach ($schedule as $round) {
                foreach ($round['matches'] as $matchData) {
                    GameMatch::create([
                        'tournament_id' => $tournament->id,
                        'home_pair_id' => $matchData['home_pair']->id,
                        'away_pair_id' => $matchData['away_pair']->id,
                        'round_number' => $matchData['round_number'],
                        'match_number' => $matchData['match_number'],
                        'status' => 'scheduled',
                    ]);
                }
            }
        });
    }

    /**
     * Generate and save schedule in one operation.
     */
    public function generateAndSaveSchedule(Tournament $tournament): Collection
    {
        $schedule = $this->generateSchedule($tournament);
        $this->saveSchedule($tournament, $schedule);
        return $schedule;
    }

    /**
     * Get schedule statistics for validation/display.
     */
    public function getScheduleStats(Tournament $tournament): array
    {
        $matches = $tournament->matches()->with(['homePair.team', 'awayPair.team'])->get();
        
        $pairMatchCounts = [];
        $teamMatchCounts = [];
        
        foreach ($matches as $match) {
            // Count per pair
            $pairMatchCounts[$match->home_pair_id] = 
                ($pairMatchCounts[$match->home_pair_id] ?? 0) + 1;
            $pairMatchCounts[$match->away_pair_id] = 
                ($pairMatchCounts[$match->away_pair_id] ?? 0) + 1;
            
            // Count per team
            $homeTeamId = $match->homePair->team_id;
            $awayTeamId = $match->awayPair->team_id;
            $teamMatchCounts[$homeTeamId] = ($teamMatchCounts[$homeTeamId] ?? 0) + 1;
            $teamMatchCounts[$awayTeamId] = ($teamMatchCounts[$awayTeamId] ?? 0) + 1;
        }

        return [
            'total_matches' => $matches->count(),
            'total_rounds' => $matches->max('round_number'),
            'matches_per_pair' => $pairMatchCounts,
            'matches_per_team' => $teamMatchCounts,
            'avg_matches_per_pair' => count($pairMatchCounts) > 0 
                ? array_sum($pairMatchCounts) / count($pairMatchCounts) 
                : 0,
        ];
    }
}
