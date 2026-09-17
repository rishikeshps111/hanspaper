<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class ReelWeightUsage
{
    public static function calculate(float $before, string $status, mixed $remaining): array
    {
        if (!in_array($status, ['bit', 'finished'], true) || $before <= 0) {
            throw ValidationException::withMessages(['reel_status_after_usage' => 'Choose Bit or Finished for a reel with available weight.']);
        }
        if ($status === 'finished') {
            $remaining = 0;
        } elseif ($remaining === null || $remaining === '') {
            throw ValidationException::withMessages(['remaining_weight_kg' => 'Enter the measured balance weight for the Bit reel.']);
        }
        if (!is_numeric($remaining) || !is_finite((float) $remaining) || (float) $remaining < 0 || (float) $remaining > $before) {
            throw ValidationException::withMessages(['remaining_weight_kg' => 'Remaining weight must be between zero and the available weight.']);
        }
        $remaining = round((float) $remaining, 3);
        if ($status === 'bit' && $remaining <= 0) {
            throw ValidationException::withMessages(['remaining_weight_kg' => 'A Bit reel must have a positive balance weight.']);
        }
        return [
            'weight_before_kg' => $before,
            'consumed_weight_kg' => round($before - $remaining, 3),
            'remaining_weight_kg' => $remaining,
            'wastage_weight_kg' => $status === 'finished' ? $remaining : 0,
        ];
    }
}
