<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pair extends Model
{
    use HasUuids;

    protected $fillable = [
        'team_id',
        'player1_name',
        'player2_name',
        'display_name',
        'sort_order',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function homeMatches(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'home_pair_id');
    }

    public function awayMatches(): HasMany
    {
        return $this->hasMany(GameMatch::class, 'away_pair_id');
    }

    public function getDisplayNameAttribute($value): string
    {
        return $value ?: "{$this->player1_name} & {$this->player2_name}";
    }

    public function getAllMatchesAttribute()
    {
        return $this->homeMatches->merge($this->awayMatches);
    }

    public function getWinsAttribute(): int
    {
        return $this->all_matches->filter(function ($match) {
            if ($match->status !== 'completed') return false;
            
            $homeTotal = $match->scores->sum('home_score');
            $awayTotal = $match->scores->sum('away_score');
            
            if ($match->home_pair_id === $this->id) {
                return $homeTotal > $awayTotal;
            }
            return $awayTotal > $homeTotal;
        })->count();
    }

    public function getTotalPointsAttribute(): int
    {
        $points = 0;
        
        foreach ($this->all_matches as $match) {
            if ($match->status !== 'completed') continue;
            
            if ($match->home_pair_id === $this->id) {
                $points += $match->scores->sum('home_score');
            } else {
                $points += $match->scores->sum('away_score');
            }
        }
        
        return $points;
    }
}
