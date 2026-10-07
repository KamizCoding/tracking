<?php

namespace App\Services;

use App\Models\BlockedDomain;
use Illuminate\Support\Facades\Log;

class UrlValidationService
{
    /**
     * Allowed URL schemes.
     */
    protected array $allowedSchemes = ['http', 'https'];

    /**
     * Validate and normalize a destination URL.
     */
    public function validateAndNormalize(string $url): array
    {
        $result = [
            'valid' => false,
            'normalized' => null,
            'error' => null,
        ];

        try {
            $parsed = parse_url($url);

            if (! $parsed) {
                $result['error'] = 'Invalid URL format';

                return $result;
            }

            // Check scheme
            if (! isset($parsed['scheme'])) {
                $result['error'] = 'URL must include a scheme (http:// or https://)';

                return $result;
            }

            $scheme = strtolower($parsed['scheme']);

            if (! in_array($scheme, $this->allowedSchemes)) {
                $result['error'] = 'Only http:// and https:// URLs are allowed';

                return $result;
            }

            // Check host
            if (! isset($parsed['host'])) {
                $result['error'] = 'URL must include a hostname';

                return $result;
            }

            $host = $this->normalizeHostname($parsed['host']);

            // Check blocked domains
            if ($this->isDomainBlocked($host)) {
                $result['error'] = 'This domain is blocked';

                return $result;
            }

            // Reconstruct normalized URL
            $normalized = $scheme.'://'.$host;

            if (isset($parsed['port']) && $parsed['port'] != $this->getDefaultPort($scheme)) {
                $normalized .= ':'.$parsed['port'];
            }

            if (isset($parsed['path'])) {
                $normalized .= $parsed['path'];
            }

            if (isset($parsed['query'])) {
                $normalized .= '?'.$parsed['query'];
            }

            if (isset($parsed['fragment'])) {
                $normalized .= '#'.$parsed['fragment'];
            }

            $result['valid'] = true;
            $result['normalized'] = $normalized;
        } catch (\Exception $e) {
            Log::error('URL validation error: '.$e->getMessage());
            $result['error'] = 'Invalid URL';
        }

        return $result;
    }

    /**
     * Normalize hostname.
     */
    protected function normalizeHostname(string $hostname): string
    {
        // Convert to lowercase
        $hostname = strtolower($hostname);

        // Remove trailing dot
        $hostname = rtrim($hostname, '.');

        // Remove whitespace
        $hostname = trim($hostname);

        // Remove default port if present in hostname
        $hostname = preg_replace('/:80$/', '', $hostname);
        $hostname = preg_replace('/:443$/', '', $hostname);

        // Handle IDN/punycode if needed
        if (function_exists('idn_to_ascii') && preg_match('/[^\x00-\x7F]/', $hostname)) {
            $hostname = idn_to_ascii($hostname);
        }

        return $hostname;
    }

    /**
     * Get default port for scheme.
     */
    protected function getDefaultPort(string $scheme): int
    {
        return match ($scheme) {
            'http' => 80,
            'https' => 443,
            default => 0,
        };
    }

    /**
     * Check if a domain or its parent domains are blocked.
     */
    protected function isDomainBlocked(string $hostname): bool
    {
        $blockedDomains = BlockedDomain::pluck('domain')->toArray();

        foreach ($blockedDomains as $blockedDomain) {
            $blockedDomain = $this->normalizeHostname($blockedDomain);

            // Exact match
            if ($hostname === $blockedDomain) {
                return true;
            }

            // Subdomain match (if blockedDomain is example.com, sub.example.com is also blocked)
            if (str_ends_with($hostname, '.'.$blockedDomain)) {
                return true;
            }
        }

        return false;
    }
}
