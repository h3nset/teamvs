<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncQueue extends Model
{
    use HasUuids;

    protected $fillable = [
        'tournament_id',
        'device_id',
        'entity_type',
        'entity_id',
        'payload',
        'status',
        'retry_count',
        'synced_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'synced_at' => 'datetime',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function markAsSynced(): void
    {
        $this->update([
            'status' => 'synced',
            'synced_at' => now(),
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update([
            'status' => 'failed',
            'retry_count' => $this->retry_count + 1,
        ]);
    }
}
