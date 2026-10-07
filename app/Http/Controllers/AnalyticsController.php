<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Click;
use App\Models\TrackingLink;
use Carbon\CarbonPeriod;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AnalyticsController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $days = $request->get('days', 30);

            // Validate days parameter
            $validDays = [7, 30, 90, 180, 365];
            if (! in_array($days, $validDays)) {
                $days = 30;
            }

            // Use user's timezone (fallback to UTC if not set)
            $timezone = $user->timezone ?? 'UTC';
            $startDate = now()->setTimezone($timezone)->subDays($days)->startOfDay();
            $endDate = now()->setTimezone($timezone)->endOfDay();

            // Get click data over time (excluding bots)
            $clicksOverTimeRaw = Click::whereHas('trackingLink', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->whereBetween('clicked_at', [$startDate, $endDate])
                ->where('is_bot', false)
                ->selectRaw('DATE(clicked_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            // Fill missing dates with zero
            $clicksOverTime = collect();
            $period = CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $clicksOverTime->push([
                    'date' => $dateStr,
                    'count' => $clicksOverTimeRaw->get($dateStr)?->count ?? 0,
                ]);
            }

            // Get clicks by country (excluding bots)
            $clicksByCountry = Click::whereHas('trackingLink', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->whereBetween('clicked_at', [$startDate, $endDate])
                ->whereNotNull('country')
                ->where('is_bot', false)
                ->selectRaw('country, COUNT(*) as count')
                ->groupBy('country')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            // Get clicks by region (excluding bots)
            $clicksByRegion = Click::whereHas('trackingLink', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->whereBetween('clicked_at', [$startDate, $endDate])
                ->whereNotNull('region')
                ->where('is_bot', false)
                ->selectRaw('region, COUNT(*) as count')
                ->groupBy('region')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            // Get clicks by city (excluding bots)
            $clicksByCity = Click::whereHas('trackingLink', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->whereBetween('clicked_at', [$startDate, $endDate])
                ->whereNotNull('city')
                ->where('is_bot', false)
                ->selectRaw('city, COUNT(*) as count')
                ->groupBy('city')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            // Get clicks by device type (excluding bots)
            $clicksByDevice = Click::whereHas('trackingLink', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->whereBetween('clicked_at', [$startDate, $endDate])
                ->where('is_bot', false)
                ->selectRaw('device_type, COUNT(*) as count')
                ->groupBy('device_type')
                ->get();

            // Get clicks by browser (excluding bots)
            $clicksByBrowser = Click::whereHas('trackingLink', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->whereBetween('clicked_at', [$startDate, $endDate])
                ->where('is_bot', false)
                ->selectRaw('browser, COUNT(*) as count')
                ->groupBy('browser')
                ->orderByDesc('count')
                ->limit(5)
                ->get();

            // Get clicks by OS (excluding bots)
            $clicksByOS = Click::whereHas('trackingLink', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->whereBetween('clicked_at', [$startDate, $endDate])
                ->where('is_bot', false)
                ->selectRaw('os, COUNT(*) as count')
                ->groupBy('os')
                ->orderByDesc('count')
                ->limit(5)
                ->get();

            // Get top referrers (excluding bots)
            $topReferrers = Click::whereHas('trackingLink', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->whereBetween('clicked_at', [$startDate, $endDate])
                ->where('is_bot', false)
                ->whereNotNull('referrer_host')
                ->selectRaw('referrer_host, COUNT(*) as count')
                ->groupBy('referrer_host')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            // Count direct traffic (no referrer)
            $directTraffic = Click::whereHas('trackingLink', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->whereBetween('clicked_at', [$startDate, $endDate])
                ->where('is_bot', false)
                ->whereNull('referrer')
                ->count();

            return view('analytics.index', compact(
                'clicksOverTime',
                'clicksByCountry',
                'clicksByRegion',
                'clicksByCity',
                'clicksByDevice',
                'clicksByBrowser',
                'clicksByOS',
                'topReferrers',
                'directTraffic',
                'days'
            ));
        } catch (\Exception $e) {
            Log::error('Analytics index error: '.$e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }

    public function trackingLink(TrackingLink $trackingLink, Request $request)
    {
        try {
            $this->authorize('view', $trackingLink);

            $user = Auth::user();
            $days = $request->get('days', 30);
            $selectedClickId = $request->get('click_id', null);

            // Validate days parameter
            $validDays = [7, 30, 90, 180, 365];
            if (! in_array($days, $validDays)) {
                $days = 30;
            }

            // Get all clicks for the dropdown (excluding bots)
            $allClicks = $trackingLink->clicks()
                ->where('is_bot', false)
                ->orderBy('clicked_at', 'desc')
                ->get(['id', 'clicked_at', 'ip_address', 'country', 'city', 'device_type']);

            // Use user's timezone (fallback to UTC if not set)
            $timezone = $user->timezone ?? 'UTC';
            $startDate = now()->setTimezone($timezone)->subDays($days)->startOfDay();
            $endDate = now()->setTimezone($timezone)->endOfDay();

            // If a specific click is selected, filter to only that click
            if ($selectedClickId) {
                $clickQuery = $trackingLink->clicks()->where('id', $selectedClickId);
            } else {
                $clickQuery = $trackingLink->clicks()->whereBetween('clicked_at', [$startDate, $endDate]);
            }

            $clickQuery = $clickQuery->where('is_bot', false);

            // Get click data over time for this link (excluding bots)
            if ($selectedClickId) {
                // Single click - show 1 on the click date
                $selectedClick = $trackingLink->clicks()->find($selectedClickId);
                $clickDate = $selectedClick ? $selectedClick->clicked_at->format('Y-m-d') : now()->format('Y-m-d');
                $clicksOverTime = [['date' => $clickDate, 'count' => 1]];
            } else {
                $clicksOverTimeRaw = $clickQuery
                    ->selectRaw('DATE(clicked_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->keyBy('date');

                // Fill missing dates with zero
                $clicksOverTime = collect();
                $period = CarbonPeriod::create($startDate, $endDate);
                foreach ($period as $date) {
                    $dateStr = $date->format('Y-m-d');
                    $clicksOverTime->push([
                        'date' => $dateStr,
                        'count' => $clicksOverTimeRaw->get($dateStr)?->count ?? 0,
                    ]);
                }
            }

            // Get geographic data (excluding bots)
            $clicksByCountry = (clone $clickQuery)
                ->whereNotNull('country')
                ->selectRaw('country, COUNT(*) as count')
                ->groupBy('country')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            // Get clicks by region (excluding bots)
            $clicksByRegion = (clone $clickQuery)
                ->whereNotNull('region')
                ->selectRaw('region, COUNT(*) as count')
                ->groupBy('region')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            // Get clicks by city (excluding bots)
            $clicksByCity = (clone $clickQuery)
                ->whereNotNull('city')
                ->selectRaw('city, COUNT(*) as count')
                ->groupBy('city')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            // Get clicks by device type (excluding bots)
            $clicksByDevice = (clone $clickQuery)
                ->selectRaw('device_type, COUNT(*) as count')
                ->groupBy('device_type')
                ->get();

            // Get clicks by browser (excluding bots)
            $clicksByBrowser = (clone $clickQuery)
                ->selectRaw('browser, COUNT(*) as count')
                ->groupBy('browser')
                ->orderByDesc('count')
                ->limit(5)
                ->get();

            // Get clicks by OS (excluding bots)
            $clicksByOS = (clone $clickQuery)
                ->selectRaw('os, COUNT(*) as count')
                ->groupBy('os')
                ->orderByDesc('count')
                ->limit(5)
                ->get();

            // Get top referrers (excluding bots)
            $topReferrers = (clone $clickQuery)
                ->whereNotNull('referrer_host')
                ->selectRaw('referrer_host, COUNT(*) as count')
                ->groupBy('referrer_host')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            // Count direct traffic (no referrer)
            $directTraffic = (clone $clickQuery)
                ->whereNull('referrer')
                ->count();

            // Get location data for map (excluding bots)
            $clickLocations = (clone $clickQuery)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->select('latitude', 'longitude', 'country', 'city')
                ->get();

            return view('analytics.tracking-link', compact(
                'trackingLink',
                'clicksOverTime',
                'clicksByCountry',
                'clicksByRegion',
                'clicksByCity',
                'clicksByDevice',
                'clicksByBrowser',
                'clicksByOS',
                'topReferrers',
                'directTraffic',
                'clickLocations',
                'days',
                'allClicks',
                'selectedClickId'
            ));
        } catch (\Exception $e) {
            Log::error('Analytics error for tracking link '.$trackingLink->id.': '.$e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }

    public function campaign(Campaign $campaign, Request $request)
    {
        $this->authorize('view', $campaign);

        $user = Auth::user();
        $days = $request->get('days', 30);

        // Validate days parameter
        $validDays = [7, 30, 90, 180, 365];
        if (! in_array($days, $validDays)) {
            $days = 30;
        }

        // Use user's timezone (fallback to UTC if not set)
        $timezone = $user->timezone ?? 'UTC';
        $startDate = now()->setTimezone($timezone)->subDays($days)->startOfDay();
        $endDate = now()->setTimezone($timezone)->endOfDay();

        // Get all tracking links for this campaign
        $trackingLinkIds = $campaign->trackingLinks()->pluck('id');

        // Get click data over time for campaign (excluding bots)
        $clicksOverTimeRaw = Click::whereIn('tracking_link_id', $trackingLinkIds)
            ->whereBetween('clicked_at', [$startDate, $endDate])
            ->where('is_bot', false)
            ->selectRaw('DATE(clicked_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Fill missing dates with zero
        $clicksOverTime = collect();
        $period = CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $clicksOverTime->push([
                'date' => $dateStr,
                'count' => $clicksOverTimeRaw->get($dateStr)?->count ?? 0,
            ]);
        }

        // Get clicks by country (excluding bots)
        $clicksByCountry = Click::whereIn('tracking_link_id', $trackingLinkIds)
            ->whereBetween('clicked_at', [$startDate, $endDate])
            ->whereNotNull('country')
            ->where('is_bot', false)
            ->selectRaw('country, COUNT(*) as count')
            ->groupBy('country')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Get clicks by device type (excluding bots)
        $clicksByDevice = Click::whereIn('tracking_link_id', $trackingLinkIds)
            ->whereBetween('clicked_at', [$startDate, $endDate])
            ->where('is_bot', false)
            ->selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->get();

        // Get top referrers (excluding bots)
        $topReferrers = Click::whereIn('tracking_link_id', $trackingLinkIds)
            ->whereBetween('clicked_at', [$startDate, $endDate])
            ->where('is_bot', false)
            ->whereNotNull('referrer_host')
            ->selectRaw('referrer_host, COUNT(*) as count')
            ->groupBy('referrer_host')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Count direct traffic (no referrer)
        $directTraffic = Click::whereIn('tracking_link_id', $trackingLinkIds)
            ->whereBetween('clicked_at', [$startDate, $endDate])
            ->where('is_bot', false)
            ->whereNull('referrer')
            ->count();

        // Get per-link breakdown
        $linkBreakdown = TrackingLink::whereIn('id', $trackingLinkIds)
            ->withCount(['clicks' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('clicked_at', [$startDate, $endDate])
                    ->where('is_bot', false);
            }])
            ->get();

        return view('analytics.campaign', compact(
            'campaign',
            'clicksOverTime',
            'clicksByCountry',
            'clicksByDevice',
            'topReferrers',
            'directTraffic',
            'linkBreakdown',
            'days'
        ));
    }
}
