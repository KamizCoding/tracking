<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClickResource;
use App\Models\Campaign;
use App\Models\TrackingLink;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    use AuthorizesRequests;

    public function trackingLink(TrackingLink $trackingLink, Request $request)
    {
        $this->authorize('view', $trackingLink);

        $days = $request->get('days', 30);
        $validDays = [7, 30, 90, 180, 365];
        if (! in_array($days, $validDays)) {
            $days = 30;
        }

        $timezone = Auth::user()->getTimezone();
        $startDate = now()->setTimezone($timezone)->subDays($days)->startOfDay();
        $endDate = now()->setTimezone($timezone)->endOfDay();

        $clicks = $trackingLink->clicks()
            ->whereBetween('clicked_at', [$startDate, $endDate])
            ->where('is_bot', false)
            ->latest()
            ->paginate(50);

        return ClickResource::collection($clicks)
            ->additional([
                'meta' => [
                    'total_clicks' => $trackingLink->clicks()
                        ->whereBetween('clicked_at', [$startDate, $endDate])
                        ->where('is_bot', false)
                        ->count(),
                    'unique_visitors' => $trackingLink->clicks()
                        ->whereBetween('clicked_at', [$startDate, $endDate])
                        ->where('is_bot', false)
                        ->distinct('visitor_id')
                        ->count(),
                    'period_start' => $startDate->format('Y-m-d H:i:s'),
                    'period_end' => $endDate->format('Y-m-d H:i:s'),
                ],
            ]);
    }

    public function campaign(Campaign $campaign, Request $request)
    {
        $this->authorize('view', $campaign);

        $days = $request->get('days', 30);
        $validDays = [7, 30, 90, 180, 365];
        if (! in_array($days, $validDays)) {
            $days = 30;
        }

        $timezone = Auth::user()->getTimezone();
        $startDate = now()->setTimezone($timezone)->subDays($days)->startOfDay();
        $endDate = now()->setTimezone($timezone)->endOfDay();

        $trackingLinkIds = $campaign->trackingLinks()->pluck('id');

        $clicks = TrackingLink::whereIn('id', $trackingLinkIds)
            ->withCount(['clicks' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('clicked_at', [$startDate, $endDate])
                    ->where('is_bot', false);
            }])
            ->get();

        $totalClicks = $clicks->sum('clicks_count');

        return response()->json([
            'data' => [
                'links' => $clicks->map(fn ($link) => [
                    'id' => $link->id,
                    'name' => $link->name,
                    'code' => $link->code,
                    'clicks_count' => $link->clicks_count ?? 0,
                ]),
                'total_clicks' => $totalClicks,
                'period_start' => $startDate->format('Y-m-d H:i:s'),
                'period_end' => $endDate->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
