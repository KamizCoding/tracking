<?php

namespace App\Services;

use App\Models\LinkDomain;
use App\Models\TrackingLink;
use Illuminate\Support\Facades\Request;

class TrackingUrlService
{
    /**
     * Generate the tracking URL for a tracking link.
     */
    public function generateTrackingUrl(TrackingLink $trackingLink): string
    {
        if ($trackingLink->linkDomain && $trackingLink->linkDomain->isVerified()) {
            return $this->generateCustomDomainUrl($trackingLink);
        }

        return $this->generateDefaultUrl($trackingLink);
    }

    /**
     * Generate URL using custom domain.
     */
    protected function generateCustomDomainUrl(TrackingLink $trackingLink): string
    {
        $domain = $trackingLink->linkDomain->domain;
        $identifier = $trackingLink->slug ?? $trackingLink->code;
        $prefix = $trackingLink->slug ? 'c/' : 's/';

        return "https://{$domain}/{$prefix}{$identifier}";
    }

    /**
     * Generate URL using default tracking domain.
     */
    protected function generateDefaultUrl(TrackingLink $trackingLink): string
    {
        $domain = config('tracking.domain');
        $identifier = $trackingLink->slug ?? $trackingLink->code;
        $prefix = $trackingLink->slug ? 'c/' : 's/';

        if (! $domain) {
            // Fallback to current request host (works on Render automatically)
            $baseUrl = Request::getSchemeAndHttpHost();

            return "{$baseUrl}/{$prefix}{$identifier}";
        }

        return "https://{$domain}/{$prefix}{$identifier}";
    }

    /**
     * Generate the destination URL with tracking code appended (for display).
     */
    public function generateDestinationUrlWithCode(TrackingLink $trackingLink): string
    {
        $destinationUrl = $trackingLink->destination_url;
        $separator = str_contains($destinationUrl, '?') ? '&' : '?';

        return $destinationUrl.$separator.'code='.$trackingLink->code;
    }

    /**
     * Check if a given hostname matches a custom domain.
     */
    public function matchesCustomDomain(string $hostname): ?LinkDomain
    {
        return LinkDomain::where('domain', $hostname)
            ->where('status', 'verified')
            ->where('verified', true)
            ->first();
    }
}
