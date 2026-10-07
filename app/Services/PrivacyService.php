<?php

namespace App\Services;

use App\Models\PrivacySettings;
use Illuminate\Support\Facades\Auth;

class PrivacyService
{
    /**
     * Anonymize an IP address based on user's privacy settings.
     */
    public function anonymizeIp(string $ipAddress, ?int $userId = null): string
    {
        $settings = $this->getUserSettings($userId);

        if (! $settings->anonymize_ips) {
            return $ipAddress;
        }

        // Anonymize by keeping only first 3 octets (for IPv4)
        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ipAddress);
            if (count($parts) === 4) {
                return $parts[0].'.'.$parts[1].'.'.$parts[2].'.0';
            }
        }

        // For IPv6, use binary masking to truncate to /48 (keep first 3 hextets)
        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $binary = inet_pton($ipAddress);
            if ($binary !== false) {
                // Mask to /48 (48 bits = 6 bytes)
                $mask = str_repeat("\xff", 6).str_repeat("\x00", 10);
                $masked = $binary & $mask;

                return inet_ntop($masked);
            }
        }

        return $ipAddress;
    }

    /**
     * Check if raw user agent should be stored.
     */
    public function shouldStoreRawUserAgent(?int $userId = null): bool
    {
        $settings = $this->getUserSettings($userId);

        return $settings->store_raw_user_agents;
    }

    /**
     * Get user's privacy settings.
     */
    protected function getUserSettings(?int $userId): PrivacySettings
    {
        $userId = $userId ?? Auth::id();

        return PrivacySettings::firstOrCreate(
            ['user_id' => $userId],
            [
                'data_retention_days' => 90,
                'anonymize_ips' => true,
                'store_raw_user_agents' => true,
            ]
        );
    }

    /**
     * Get data retention days for a user.
     */
    public function getRetentionDays(?int $userId = null): int
    {
        $settings = $this->getUserSettings($userId);

        return $settings->data_retention_days;
    }
}
