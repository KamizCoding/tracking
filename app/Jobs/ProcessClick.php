<?php

namespace App\Jobs;

use App\Models\Click;
use App\Models\TrackingLink;
use App\Services\GeoLocationService;
use App\Services\PrivacyService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessClick implements ShouldQueue
{
    use Queueable;

    protected $trackingLinkId;

    protected $ipAddress;

    protected $userAgent;

    protected $referrer;

    protected $clickedAt;

    protected $clickEventId;

    protected $visitorId;

    public $tries = 3;

    public $backoff = [5, 10, 30];

    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(int $trackingLinkId, string $ipAddress, ?string $userAgent, ?string $referrer, string $clickedAt, string $clickEventId, string $visitorId)
    {
        $this->trackingLinkId = $trackingLinkId;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->referrer = $referrer;
        $this->clickedAt = $clickedAt;
        $this->clickEventId = $clickEventId;
        $this->visitorId = $visitorId;
    }

    /**
     * Execute the job.
     */
    public function handle(GeoLocationService $geoService, PrivacyService $privacyService): void
    {
        // Idempotency check - prevent duplicate processing
        $existingClick = Click::where('click_event_id', $this->clickEventId)->first();
        if ($existingClick) {
            Log::info('Click already processed, skipping', ['click_event_id' => $this->clickEventId]);

            return;
        }

        $trackingLink = TrackingLink::find($this->trackingLinkId);

        if (! $trackingLink) {
            Log::warning('Tracking link not found for click processing', ['tracking_link_id' => $this->trackingLinkId]);

            return;
        }

        // Get GeoIP location data
        $locationData = $geoService->getLocation($this->ipAddress);

        // Detect bot
        $botData = $geoService->detectBot($this->userAgent ?? '');

        // Detect device information
        $deviceData = $geoService->detectDevice($this->userAgent ?? '');

        // Apply privacy settings
        $ipAddress = $privacyService->anonymizeIp($this->ipAddress, $trackingLink->user_id);
        $userAgent = $privacyService->shouldStoreRawUserAgent($trackingLink->user_id)
            ? $this->userAgent
            : null;

        // Extract referrer host
        // Privacy decision: Raw referrer URLs are always stored for analytics purposes.
        // Referrer data is considered less privacy-sensitive than IP addresses or user agents,
        // as it reveals only the referring website, not device identity or location.
        // The referrer_host field provides a privacy-friendly alternative when needed.
        $referrerHost = null;
        if ($this->referrer) {
            $parsed = parse_url($this->referrer);
            $referrerHost = $parsed['host'] ?? null;
        }

        // Create click record with all data within a transaction
        DB::transaction(function () use ($trackingLink, $locationData, $botData, $deviceData, $ipAddress, $userAgent, $referrerHost) {
            Click::create([
                'tracking_link_id' => $this->trackingLinkId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'referrer' => $this->referrer,
                'referrer_host' => $referrerHost,
                'clicked_at' => $this->clickedAt,
                'click_event_id' => $this->clickEventId,
                'visitor_id' => $this->visitorId,
                // GeoIP data
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
                // Device data
                'device_type' => $deviceData['device_type'],
                'os' => $deviceData['os'],
                'os_version' => $deviceData['os_version'],
                'browser' => $deviceData['browser'],
                'browser_version' => $deviceData['browser_version'],
                // Bot data
                'is_bot' => $botData['is_bot'],
                'bot_name' => $botData['bot_name'],
            ]);

            // Increment click count (authoritative counting mechanism)
            $trackingLink->increment('click_count');
        });
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Click processing job failed', [
            'tracking_link_id' => $this->trackingLinkId,
            'click_event_id' => $this->clickEventId,
            'error' => $exception->getMessage(),
        ]);
    }
}
