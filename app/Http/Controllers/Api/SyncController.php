<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Services\ScoringService;
use App\Services\SyncService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SyncController extends Controller
{
    public function __construct(
        private SyncService $syncService,
        private ScoringService $scoringService
    ) {}

    /**
     * Get full tournament data for offline caching.
     */
    public function getTournamentData(Tournament $tournament): JsonResponse
    {
        $tournament->load([
            'teams.pairs',
            'matches.homePair.team',
            'matches.awayPair.team',
            'matches.scores',
        ]);

        return response()->json([
            'tournament' => $tournament,
            'standings' => $this->scoringService->getTournamentStandings($tournament),
            'synced_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Process sync queue from client.
     */
    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'items' => 'required|array',
            'items.*.entity_type' => 'required|string|in:score,match_status',
            'items.*.entity_id' => 'required|uuid',
            'items.*.payload' => 'required|array',
            'items.*.timestamp' => 'nullable|string',
        ]);

        $results = $this->syncService->processSyncBatch(
            $validated['device_id'],
            $validated['items']
        );

        return response()->json([
            'results' => $results,
            'synced_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get matches that have been updated since a given timestamp.
     */
    public function getUpdates(Request $request, Tournament $tournament): JsonResponse
    {
        $since = $request->query('since');
        
        $query = $tournament->matches()->with(['homePair.team', 'awayPair.team', 'scores']);
        
        if ($since) {
            $query->where('updated_at', '>', $since);
        }

        $matches = $query->get();

        return response()->json([
            'matches' => $matches,
            'standings' => $this->scoringService->getTournamentStandings($tournament),
            'synced_at' => now()->toIso8601String(),
        ]);
    }
}
