<?php

namespace App\Services;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Support\Facades\Log;

class GeoLocationService
{
    protected ?Reader $reader = null;

    public function __construct()
    {
        $databasePath = database_path('GeoLite2-City.mmdb');

        if (file_exists($databasePath) && filesize($databasePath) > 1000) {
            try {
                $this->reader = new Reader($databasePath);
            } catch (\Exception $e) {
                Log::warning('Failed to initialize GeoIP reader: '.$e->getMessage());
            }
        }
    }

    /**
     * Get location data for an IP address.
     *
     * Note: ASN and ISP data are not available in the GeoLite2-City database.
     * Use GeoLite2-ASN database for these fields if needed.
     */
    public function getLocation(string $ipAddress): array
    {
        if (! $this->reader) {
            return $this->getFallbackLocation();
        }

        try {
            $record = $this->reader->city($ipAddress);

            return [
                'country' => $record->country->name ?? null,
                'country_code' => $record->country->isoCode ?? null,
                'region' => $record->mostSpecificSubdivision->name ?? null,
                'city' => $record->city->name ?? null,
                'latitude' => $record->location->latitude ?? null,
                'longitude' => $record->location->longitude ?? null,
                'timezone' => $record->location->timeZone ?? null,
                // ASN and ISP are not available in City database
                'asn' => null,
                'isp' => null,
            ];
        } catch (AddressNotFoundException $e) {
            return $this->getFallbackLocation();
        } catch (\Exception $e) {
            Log::error('GeoIP lookup error: '.$e->getMessage());

            return $this->getFallbackLocation();
        }
    }

    /**
     * Get fallback location data when GeoIP is not available.
     */
    protected function getFallbackLocation(): array
    {
        return [
            'country' => null,
            'country_code' => null,
            'region' => null,
            'city' => null,
            'latitude' => null,
            'longitude' => null,
            'timezone' => null,
            'asn' => null,
            'isp' => null,
        ];
    }

    /**
     * Detect if the user agent is a bot.
     */
    public function detectBot(string $userAgent): array
    {
        $botPatterns = [
            'googlebot' => 'Googlebot',
            'bingbot' => 'Bingbot',
            'slurp' => 'Yahoo! Slurp',
            'duckduckbot' => 'DuckDuckBot',
            'baiduspider' => 'Baidu Spider',
            'yandexbot' => 'YandexBot',
            'facebookexternalhit' => 'Facebook Bot',
            'twitterbot' => 'Twitter Bot',
            'linkedinbot' => 'LinkedIn Bot',
            'whatsapp' => 'WhatsApp Bot',
            'telegrambot' => 'Telegram Bot',
            'applebot' => 'AppleBot',
            'semrushbot' => 'SEMrush Bot',
            'ahrefsbot' => 'Ahrefs Bot',
            'mj12bot' => 'MJ12bot',
            'dotbot' => 'DotBot',
        ];

        $userAgentLower = strtolower($userAgent);

        foreach ($botPatterns as $pattern => $botName) {
            if (str_contains($userAgentLower, $pattern)) {
                return [
                    'is_bot' => true,
                    'bot_name' => $botName,
                ];
            }
        }

        return [
            'is_bot' => false,
            'bot_name' => null,
        ];
    }

    /**
     * Detect device information from user agent.
     */
    public function detectDevice(string $userAgent): array
    {
        $userAgentLower = strtolower($userAgent);

        // Device type
        $deviceType = 'desktop';
        if (str_contains($userAgentLower, 'mobile') || str_contains($userAgentLower, 'android') || str_contains($userAgentLower, 'iphone')) {
            $deviceType = 'mobile';
        } elseif (str_contains($userAgentLower, 'tablet') || str_contains($userAgentLower, 'ipad')) {
            $deviceType = 'tablet';
        }

        // OS detection - check iOS BEFORE macOS to avoid misclassification
        $os = 'Unknown';
        $osVersion = null;

        if (str_contains($userAgentLower, 'iphone')) {
            $os = 'iOS';
            $deviceType = 'mobile';
            if (preg_match('/os ([\d_]+) like mac os x/', $userAgent, $matches)) {
                $osVersion = str_replace('_', '.', $matches[1]);
            }
        } elseif (str_contains($userAgentLower, 'ipad')) {
            $os = 'iPadOS';
            $deviceType = 'tablet';
            if (preg_match('/os ([\d_]+) like mac os x/', $userAgent, $matches)) {
                $osVersion = str_replace('_', '.', $matches[1]);
            }
        } elseif (str_contains($userAgentLower, 'android')) {
            $os = 'Android';
            if (preg_match('/android ([\d.]+)/', $userAgent, $matches)) {
                $osVersion = $matches[1];
            }
        } elseif (str_contains($userAgentLower, 'windows nt 10.0')) {
            $os = 'Windows';
            $osVersion = '10';
        } elseif (str_contains($userAgentLower, 'windows nt 6.3')) {
            $os = 'Windows';
            $osVersion = '8.1';
        } elseif (str_contains($userAgentLower, 'windows nt 6.2')) {
            $os = 'Windows';
            $osVersion = '8';
        } elseif (str_contains($userAgentLower, 'windows nt 6.1')) {
            $os = 'Windows';
            $osVersion = '7';
        } elseif (str_contains($userAgentLower, 'windows nt 6.0')) {
            $os = 'Windows';
            $osVersion = 'Vista';
        } elseif (str_contains($userAgentLower, 'windows nt 5.1')) {
            $os = 'Windows';
            $osVersion = 'XP';
        } elseif (str_contains($userAgentLower, 'windows')) {
            $os = 'Windows';
        } elseif (str_contains($userAgentLower, 'mac os x')) {
            $os = 'macOS';
            if (preg_match('/mac os x ([\d_]+)/', $userAgent, $matches)) {
                $osVersion = str_replace('_', '.', $matches[1]);
            }
        } elseif (str_contains($userAgentLower, 'linux')) {
            $os = 'Linux';
        }

        // Browser detection
        $browser = 'Unknown';
        $browserVersion = null;

        if (str_contains($userAgentLower, 'edg/')) {
            $browser = 'Edge';
            if (preg_match('/edg\/([\d.]+)/', $userAgent, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (str_contains($userAgentLower, 'chrome/') && ! str_contains($userAgentLower, 'edg/')) {
            $browser = 'Chrome';
            if (preg_match('/chrome\/([\d.]+)/', $userAgent, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (str_contains($userAgentLower, 'firefox/')) {
            $browser = 'Firefox';
            if (preg_match('/firefox\/([\d.]+)/', $userAgent, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (str_contains($userAgentLower, 'safari/') && ! str_contains($userAgentLower, 'chrome')) {
            $browser = 'Safari';
            if (preg_match('/version\/([\d.]+)/', $userAgent, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (str_contains($userAgentLower, 'opera/') || str_contains($userAgentLower, 'opr/')) {
            $browser = 'Opera';
            if (preg_match('/(opera|opr)\/([\d.]+)/', $userAgent, $matches)) {
                $browserVersion = $matches[2];
            }
        }

        return [
            'device_type' => $deviceType,
            'os' => $os,
            'os_version' => $osVersion,
            'browser' => $browser,
            'browser_version' => $browserVersion,
        ];
    }
}
