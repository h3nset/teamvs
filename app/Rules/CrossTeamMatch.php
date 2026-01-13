<?php

namespace App\Rules;

use App\Models\Pair;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CrossTeamMatch implements ValidationRule
{
    public function __construct(
        private ?string $homePairId = null,
        private ?string $awayPairId = null
    ) {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $homePairId = $this->homePairId ?? request()->input('home_pair_id');
        $awayPairId = $this->awayPairId ?? request()->input('away_pair_id');

        if (!$homePairId || !$awayPairId) {
            return; // Let required validation handle missing values
        }

        $homePair = Pair::find($homePairId);
        $awayPair = Pair::find($awayPairId);

        if (!$homePair || !$awayPair) {
            $fail('One or both pairs do not exist.');
            return;
        }

        if ($homePair->team_id === $awayPair->team_id) {
            $fail('Pairs from the same team cannot play against each other.');
        }
    }
}
