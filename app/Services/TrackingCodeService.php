<?php

namespace App\Services;

use App\Models\TrackingLink;
use Illuminate\Support\Str;

class TrackingCodeService
{
    /**
     * Generate a unique tracking code.
     *
     * For 10-character random codes, the collision probability is extremely low
     * (1 in 62^10 ≈ 1 in 8.4×10^17). The database unique constraint provides
     * final protection against any rare collisions.
     *
     * For extremely high-scale generation scenarios (>10,000 codes/second),
     * consider using UUIDs or a centralized ID generation service.
     *
     * @throws \RuntimeException if unable to generate a unique code after max attempts
     */
    public function generateUniqueCode(): string
    {
        $maxAttempts = 10;
        $attempts = 0;

        do {
            $code = Str::random(config('tracking.code_length', 10));
            $attempts++;

            // Check if code exists (race condition possible but extremely unlikely)
            if (! TrackingLink::where('code', $code)->exists()) {
                return $code;
            }
        } while ($attempts < $maxAttempts);

        throw new \RuntimeException(
            'Failed to generate unique tracking code after '.$maxAttempts.' attempts'
        );
    }
}
