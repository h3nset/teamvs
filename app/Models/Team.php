<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasUuids;

    protected $fillable = [
        'tournament_id',
        'name',
        'description',
        'color',
        'sort_order',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function pairs(): HasMany
    {
        return $this->hasMany(Pair::class)->orderBy('sort_order');
    }

    public function getTotalPointsAttribute(): int
    {
        return $this->pairs->sum(function ($pair) {
            return $pair->total_points;
        });
    }

    public function getTotalWinsAttribute(): int
    {
        return $this->pairs->sum(function ($pair) {
            return $pair->wins;
        });
    }
}
