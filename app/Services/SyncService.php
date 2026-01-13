<?php

namespace App\Services;

use App\Models\SyncQueue;
use App\Models\GameMatch;
use App\Models\Score;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncService
{
    /**
     * Process incoming sync data from a client device.
     */
    public function processSyncBatch(string $deviceId, array $items): array
    {
        $results = [];

        foreach ($items as $item) {
            try {
                $result = $this->processSyncItem($deviceId, $item);
                $results[] = [
                    'entity_type' => $item['entity_type'],
                    'entity_id' => $item['entity_id'],
                    'status' => $result['status'],
                    'server_data' => $result['server_data'] ?? null,
                ];
            } catch (\Exception $e) {
                Log::error('Sync error', [
                    'device_id' => $deviceId,
                    'item' => $item,
                    'error' => $e->getMessage(),
                ]);
                
                $results[] = [
                    'entity_type' => $item['entity_type'],
                    'entity_id' => $item['entity_id'],
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Process a single sync item.
     */
    private function processSyncItem(string $deviceId, array $item): array
    {
        $entityType = $item['entity_type'];
        $entityId = $item['entity_id'];
        $payload = $item['payload'];
        $clientTimestamp = $item['timestamp'] ?? null;

        switch ($entityType) {
            case 'score':
                return $this->syncScore($deviceId, $entityId, $payload, $clientTimestamp);
            
            case 'match_status':
                return $this->syncMatchStatus($deviceId, $entityId, $payload, $clientTimestamp);
            
            default:
                throw new \InvalidArgumentException("Unknown entity type: {$entityType}");
        }
    }

    /**
     * Sync a score record.
     */
    private function syncScore(string $deviceId, string $entityId, array $payload, ?string $clientTimestamp): array
    {
        return DB::transaction(function () use ($deviceId, $entityId, $payload, $clientTimestamp) {
            $score = Score::find($entityId);
            
            if ($score) {
                // Check for conflict
                $serverUpdateTime = $score->updated_at->timestamp;
                $clientUpdateTime = $clientTimestamp ? strtotime($clientTimestamp) : 0;

                if ($serverUpdateTime > $clientUpdateTime && $score->device_id !== $deviceId) {
                    // Conflict: server has newer data from different device
                    $this->logConflict('score', $entityId, $deviceId, $payload, $score->toArray());
                    
                    return [
                        'status' => 'conflict',
                        'server_data' => $score->toArray(),
                    ];
                }

                // Update existing score
                $score->update([
                    'home_score' => $payload['home_score'],
                    'away_score' => $payload['away_score'],
                    'is_tiebreak' => $payload['is_tiebreak'] ?? false,
                    'device_id' => $deviceId,
                    'recorded_at' => $payload['recorded_at'] ?? now(),
                ]);
            } else {
                // Create new score
                $score = Score::create([
                    'id' => $entityId,
                    'match_id' => $payload['match_id'],
                    'set_number' => $payload['set_number'],
                    'home_score' => $payload['home_score'],
                    'away_score' => $payload['away_score'],
                    'is_tiebreak' => $payload['is_tiebreak'] ?? false,
                    'device_id' => $deviceId,
                    'recorded_at' => $payload['recorded_at'] ?? now(),
                ]);
            }

            return [
                'status' => 'synced',
                'server_data' => $score->fresh()->toArray(),
            ];
        });
    }

    /**
     * Sync match status update.
     */
    private function syncMatchStatus(string $deviceId, string $entityId, array $payload, ?string $clientTimestamp): array
    {
        return DB::transaction(function () use ($deviceId, $entityId, $payload, $clientTimestamp) {
            $match = GameMatch::findOrFail($entityId);
            
            $serverUpdateTime = $match->updated_at->timestamp;
            $clientUpdateTime = $clientTimestamp ? strtotime($clientTimestamp) : 0;

            if ($serverUpdateTime > $clientUpdateTime) {
                // Server has newer data
                return [
                    'status' => 'conflict',
                    'server_data' => $match->load('scores')->toArray(),
                ];
            }

            $match->update([
                'status' => $payload['status'],
                'started_at' => $payload['started_at'] ?? $match->started_at,
                'ended_at' => $payload['ended_at'] ?? $match->ended_at,
            ]);

            return [
                'status' => 'synced',
                'server_data' => $match->fresh()->toArray(),
            ];
        });
    }

    /**
     * Log a sync conflict for admin review.
     */
    private function logConflict(string $entityType, string $entityId, string $deviceId, array $clientData, array $serverData): void
    {
        SyncQueue::create([
            'device_id' => $deviceId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'payload' => [
                'client_data' => $clientData,
                'server_data' => $serverData,
            ],
            'status' => 'conflict',
        ]);
    }

    /**
     * Get pending sync items for conflict resolution.
     */
    public function getConflicts(?string $tournamentId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = SyncQueue::where('status', 'conflict');
        
        if ($tournamentId) {
            $query->where('tournament_id', $tournamentId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Resolve a conflict by accepting either client or server version.
     */
    public function resolveConflict(string $conflictId, string $resolution): bool
    {
        $conflict = SyncQueue::findOrFail($conflictId);
        
        if ($resolution === 'accept_client') {
            // Apply client data
            $this->applyClientData(
                $conflict->entity_type,
                $conflict->entity_id,
                $conflict->payload['client_data']
            );
        }
        // If 'accept_server', we just mark as resolved (server data is already current)

        $conflict->update(['status' => 'synced', 'synced_at' => now()]);
        
        return true;
    }

    /**
     * Apply client data to overwrite server data.
     */
    private function applyClientData(string $entityType, string $entityId, array $data): void
    {
        switch ($entityType) {
            case 'score':
                Score::where('id', $entityId)->update([
                    'home_score' => $data['home_score'],
                    'away_score' => $data['away_score'],
                    'is_tiebreak' => $data['is_tiebreak'] ?? false,
                ]);
                break;
                
            case 'match_status':
                GameMatch::where('id', $entityId)->update([
                    'status' => $data['status'],
                ]);
                break;
        }
    }
}
