<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Score extends Model
{
    use HasUuids;

    protected $fillable = [
        'match_id',
        'set_number',
        'home_score',
        'away_score',
        'is_tiebreak',
        'device_id',
        'recorded_at',
    ];

    protected $casts = [
        'is_tiebreak' => 'boolean',
        'recorded_at' => 'datetime',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }

    public function getWinnerSideAttribute(): ?string
    {
        if ($this->home_score === $this->away_score) return null;
        return $this->home_score > $this->away_score ? 'home' : 'away';
    }
}
