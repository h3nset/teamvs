<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tournament extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'description',
        'pairs_per_team',
        'rounds',
        'max_matches_per_pair',
        'points_per_set',
        'status',
        'settings',
        'access_token',
        'is_locked',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_locked' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token',
    ];

    protected static function booted(): void
    {
        static::creating(function (Tournament $tournament) {
            if (empty($tournament->access_token)) {
                $tournament->access_token = Str::random(32);
            }
        });
    }

    /**
     * Generate a new access token for the tournament.
     */
    public function regenerateToken(): string
    {
        $this->access_token = Str::random(32);
        $this->save();
        return $this->access_token;
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class)->orderBy('sort_order');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(GameMatch::class)->orderBy('round_number')->orderBy('match_number');
    }

    public function syncQueues(): HasMany
    {
        return $this->hasMany(SyncQueue::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canGenerateSchedule(): bool
    {
        return $this->status === 'draft' && $this->teams()->count() >= 2;
    }
}
