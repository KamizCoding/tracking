<?php

namespace App\Services;

use Illuminate\Support\Str;

class VisitorService
{
    /**
     * Generate a privacy-conscious visitor identifier.
     *
     * This creates a non-invasive visitor ID based on a session cookie
     * rather than device fingerprinting.
     */
    public function generateVisitorId(): string
    {
        return Str::random(32);
    }

    /**
     * Check if two clicks should be considered the same visitor.
     *
     * Rules:
     * - Same visitor ID in cookie = same visitor
     * - Different visitor ID = different visitor
     * - Expired/deleted cookie = new visitor
     * - Different browser = potentially different visitor
     */
    public function isSameVisitor(?string $currentVisitorId, ?string $previousVisitorId): bool
    {
        if (! $currentVisitorId || ! $previousVisitorId) {
            return false;
        }

        return $currentVisitorId === $previousVisitorId;
    }
}
