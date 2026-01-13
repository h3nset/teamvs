<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameMatch extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'matches';

    protected $fillable = [
        'tournament_id',
        'home_pair_id',
        'away_pair_id',
        'round_number',
        'match_number',
        'court_number',
        'status',
        'scheduled_at',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function homePair(): BelongsTo
    {
        return $this->belongsTo(Pair::class, 'home_pair_id');
    }

    public function awayPair(): BelongsTo
    {
        return $this->belongsTo(Pair::class, 'away_pair_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class, 'match_id');
    }

    public function getHomeTeamAttribute()
    {
        return $this->homePair?->team;
    }

    public function getAwayTeamAttribute()
    {
        return $this->awayPair?->team;
    }

    public function getHomeTotalScoreAttribute(): int
    {
        return $this->scores->sum('home_score');
    }

    public function getAwayTotalScoreAttribute(): int
    {
        return $this->scores->sum('away_score');
    }

    public function getWinnerAttribute(): ?Pair
    {
        if ($this->status !== 'completed') return null;
        
        return $this->home_total_score > $this->away_total_score 
            ? $this->homePair 
            : $this->awayPair;
    }

    public function isCrossTeam(): bool
    {
        return $this->homePair?->team_id !== $this->awayPair?->team_id;
    }
}
