<?php

namespace App\Http\Controllers;

use App\Models\Click;
use App\Models\TrackingLink;
use App\Services\GeoLocationService;
use App\Services\PrivacyService;
use App\Services\TrackingUrlService;
use App\Services\VisitorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class TrackingController extends Controller
{
    public function redirect(?string $code, Request $request, VisitorService $visitorService, TrackingUrlService $trackingUrlService)
    {
        // Handle custom domain routing
        $hostname = $request->getHost();
        $customDomain = $trackingUrlService->matchesCustomDomain($hostname);

        if ($customDomain) {
            // Custom domain: code is in the path
            $code = $request->path();
            // Strip /track/ or /s/ prefix if present (for web server rewrites)
            $code = ltrim(str_replace(['track/', 's/'], '', $code), '/');

            if (! $code) {
                abort(404, 'Tracking code not found');
            }

            // Find tracking link by code assigned to this specific custom domain
            $trackingLink = TrackingLink::where('code', $code)
                ->where('link_domain_id', $customDomain->id)
                ->first();
        } else {
            // Default routing: code could be in path or parameter
            // Check if it's the legacy /track/{code} format
            if (str_starts_with($request->path(), '/track/')) {
                // Legacy format: extract code from /track/{code}
                $code = ltrim(str_replace('track/', '', $request->path()), '/');
            } elseif (str_starts_with($request->path(), '/s/')) {
                // New short format: extract code from /s/{code}
                $code = ltrim(str_replace('s/', '', $request->path()), '/');
            } elseif (! $code) {
                // Parameter format (shouldn't happen with current routes)
                abort(404, 'Tracking code not found');
            }

            if (! $code) {
                abort(404, 'Tracking code not found');
            }

            $trackingLink = TrackingLink::where('code', $code)->first();

            // If the link is assigned to a custom domain, it cannot be accessed
            // through the default domain (short link or legacy)
            if ($trackingLink && $trackingLink->link_domain_id) {
                abort(404);
            }
        }

        if (! $trackingLink) {
            abort(404);
        }

        if ($trackingLink->status !== 'active') {
            abort(403);
        }

        if ($trackingLink->expires_at && $trackingLink->expires_at->isPast()) {
            abort(410);
        }

        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $referrer = $request->header('referer');
        $clickedAt = now()->toDateTimeString();

        // Get or generate visitor ID
        $visitorId = $request->cookie('visitor_id') ?? $visitorService->generateVisitorId();

        // Generate unique click event ID for idempotency (UUID to prevent collisions)
        $clickEventId = (string) Str::uuid();

        // Create click record synchronously (immediately available for GPS update)
        $geoService = app(GeoLocationService::class);
        $locationData = $geoService->getLocation($ipAddress);
        $botData = $geoService->detectBot($userAgent ?? '');
        $deviceData = $geoService->detectDevice($userAgent ?? '');

        $privacyService = app(PrivacyService::class);
        $ipAddressAnonymized = $privacyService->anonymizeIp($ipAddress, $trackingLink->user_id);
        $userAgentStored = $privacyService->shouldStoreRawUserAgent($trackingLink->user_id)
            ? $userAgent
            : null;

        $referrerHost = null;
        if ($referrer) {
            $parsed = parse_url($referrer);
            $referrerHost = $parsed['host'] ?? null;
        }

        $click = Click::create([
            'tracking_link_id' => $trackingLink->id,
            'ip_address' => $ipAddressAnonymized,
            'user_agent' => $userAgentStored,
            'referrer' => $referrer,
            'referrer_host' => $referrerHost,
            'clicked_at' => $clickedAt,
            'click_event_id' => $clickEventId,
            'visitor_id' => $visitorId,
            'country' => $locationData['country'],
            'country_code' => $locationData['country_code'],
            'region' => $locationData['region'],
            'city' => $locationData['city'],
            'latitude' => $locationData['latitude'],
            'longitude' => $locationData['longitude'],
            'timezone' => $locationData['timezone'],
            'asn' => $locationData['asn'],
            'isp' => $locationData['isp'],
            'location_source' => 'ip',
            'device_type' => $deviceData['device_type'],
            'os' => $deviceData['os'],
            'os_version' => $deviceData['os_version'],
            'browser' => $deviceData['browser'],
            'browser_version' => $deviceData['browser_version'],
            'is_bot' => $botData['is_bot'],
            'bot_name' => $botData['bot_name'],
        ]);

        // Increment click count
        $trackingLink->increment('click_count');

        // Set visitor ID cookie (90 days) with security attributes
        Cookie::queue('visitor_id', $visitorId, 90 * 24 * 60, null, null, true, true, false, 'Lax');

        // Show redirect page that requests GPS location before redirecting
        return response()
            ->view('tracking.redirect', [
                'destinationUrl' => $trackingLink->destination_url,
                'clickEventId' => $clickEventId,
                'trackingCode' => $trackingLink->code,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');
    }
}
