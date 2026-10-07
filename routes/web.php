<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\BlockedDomainController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LinkDomainController;
use App\Http\Controllers\PrivacyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\TrackingLinkController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Privacy settings
    Route::prefix('privacy')->name('privacy.')->group(function () {
        Route::get('/', [PrivacyController::class, 'index'])->name('index');
        Route::put('/', [PrivacyController::class, 'update'])->name('update');
    });

    // Campaigns management
    Route::prefix('campaigns')->name('campaigns.')->group(function () {
        Route::get('/', [CampaignController::class, 'index'])->name('index');
        Route::get('/create', [CampaignController::class, 'create'])->name('create');
        Route::post('/', [CampaignController::class, 'store'])->name('store');
        Route::get('/{campaign}', [CampaignController::class, 'show'])->name('show');
        Route::get('/{campaign}/edit', [CampaignController::class, 'edit'])->name('edit');
        Route::put('/{campaign}', [CampaignController::class, 'update'])->name('update');
        Route::delete('/{campaign}', [CampaignController::class, 'destroy'])->name('destroy');
        Route::post('/{campaign}/enable', [CampaignController::class, 'enable'])->name('enable');
        Route::post('/{campaign}/disable', [CampaignController::class, 'disable'])->name('disable');
    });

    // Tracking links management
    Route::prefix('tracking-links')->name('tracking-links.')->group(function () {
        Route::get('/', [TrackingLinkController::class, 'index'])->name('index');
        Route::get('/create', [TrackingLinkController::class, 'create'])->name('create');
        Route::post('/', [TrackingLinkController::class, 'store'])->name('store');
        Route::get('/{trackingLink}', [TrackingLinkController::class, 'show'])->name('show');
        Route::get('/{trackingLink}/edit', [TrackingLinkController::class, 'edit'])->name('edit');
        Route::put('/{trackingLink}', [TrackingLinkController::class, 'update'])->name('update');
        Route::delete('/{trackingLink}', [TrackingLinkController::class, 'destroy'])->name('destroy');
        Route::post('/{trackingLink}/enable', [TrackingLinkController::class, 'enable'])->name('enable');
        Route::post('/{trackingLink}/disable', [TrackingLinkController::class, 'disable'])->name('disable');
    });

    // Analytics
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/tracking-link/{trackingLink}', [AnalyticsController::class, 'trackingLink'])->name('tracking-link');
        Route::get('/campaign/{campaign}', [AnalyticsController::class, 'campaign'])->name('campaign');
    });

    // Custom domains
    Route::prefix('link-domains')->name('link-domains.')->group(function () {
        Route::get('/', [LinkDomainController::class, 'index'])->name('index');
        Route::get('/create', [LinkDomainController::class, 'create'])->name('create');
        Route::post('/', [LinkDomainController::class, 'store'])->name('store');
        Route::post('/{linkDomain}/verify', [LinkDomainController::class, 'verify'])->name('verify');
        Route::delete('/{linkDomain}', [LinkDomainController::class, 'destroy'])->name('destroy');
    });

    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users/{user}/role', [AdminController::class, 'updateUserRole'])
            ->middleware('super_admin')
            ->name('users.update-role');
        Route::get('/tracking-links', [AdminController::class, 'trackingLinks'])->name('tracking-links');

        // Blocked domains management
        Route::prefix('blocked-domains')->name('blocked-domains.')->group(function () {
            Route::get('/', [BlockedDomainController::class, 'index'])->name('index');
            Route::post('/', [BlockedDomainController::class, 'store'])->name('store');
            Route::delete('/{blockedDomain}', [BlockedDomainController::class, 'destroy'])->name('destroy');
        });
    });
});

require __DIR__.'/auth.php';

// Tracking redirect routes (must be at the end to avoid conflicts)
// Custom slugs (looks like destination path): http://domain.com/c/products/wallet.html
Route::get('/c/{slug}', [TrackingController::class, 'redirect'])
    ->where('slug', '.*')
    ->middleware('throttle:60,1')
    ->name('tracking.redirect.slug');

// Short links (less obvious tracking): http://domain.com/s/ABC123
Route::get('/s/{code}', [TrackingController::class, 'redirect'])
    ->where('code', '[a-zA-Z0-9]+')
    ->middleware('throttle:60,1')
    ->name('tracking.redirect');

// Legacy tracking route (still supported): /track/ABC123
Route::get('/track/{code}', [TrackingController::class, 'redirect'])
    ->where('code', '[a-zA-Z0-9]+')
    ->middleware('throttle:60,1')
    ->name('tracking.redirect.legacy');
