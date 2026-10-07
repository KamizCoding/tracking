<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\GpsLocationController;
use App\Http\Controllers\Api\V1\TrackingLinkController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public GPS location update (no auth required)
Route::post('gps-location', [GpsLocationController::class, 'update'])
    ->middleware('throttle:60,1');

// API v1 routes with rate limiting
Route::prefix('v1')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        // Tracking links - higher rate for reads, lower for writes
        Route::get('tracking-links', [TrackingLinkController::class, 'index'])
            ->middleware('throttle:120,1')
            ->name('api.tracking-links.index');
        Route::post('tracking-links', [TrackingLinkController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('api.tracking-links.store');
        Route::get('tracking-links/{trackingLink}', [TrackingLinkController::class, 'show'])
            ->middleware('throttle:120,1')
            ->name('api.tracking-links.show');
        Route::put('tracking-links/{trackingLink}', [TrackingLinkController::class, 'update'])
            ->middleware('throttle:30,1')
            ->name('api.tracking-links.update');
        Route::delete('tracking-links/{trackingLink}', [TrackingLinkController::class, 'destroy'])
            ->middleware('throttle:10,1')
            ->name('api.tracking-links.destroy');

        // Campaigns
        Route::apiResource('campaigns', CampaignController::class)
            ->middleware('throttle:60,1')
            ->names([
                'index' => 'api.campaigns.index',
                'store' => 'api.campaigns.store',
                'show' => 'api.campaigns.show',
                'update' => 'api.campaigns.update',
                'destroy' => 'api.campaigns.destroy',
            ]);

        // Analytics endpoints - read-heavy
        Route::get('analytics/tracking-link/{trackingLink}', [AnalyticsController::class, 'trackingLink'])
            ->middleware('throttle:120,1');
        Route::get('analytics/campaign/{campaign}', [AnalyticsController::class, 'campaign'])
            ->middleware('throttle:120,1');
    });
