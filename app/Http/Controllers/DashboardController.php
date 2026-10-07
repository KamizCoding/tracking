<?php

namespace App\Http\Controllers;

use App\Models\Click;
use App\Models\TrackingLink;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::id();

        $totalLinks = TrackingLink::where('user_id', $user)->count();
        $totalClicks = Click::whereHas('trackingLink', function ($query) use ($user) {
            $query->where('user_id', $user);
        })->where('is_bot', false)->count();

        $uniqueVisitors = Click::whereHas('trackingLink', function ($query) use ($user) {
            $query->where('user_id', $user);
        })->where('is_bot', false)->distinct('visitor_id')->count();

        $countries = Click::whereHas('trackingLink', function ($query) use ($user) {
            $query->where('user_id', $user);
        })->where('is_bot', false)->whereNotNull('country')->distinct('country')->count();

        $recentLinks = TrackingLink::where('user_id', $user)
            ->with('campaign')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalLinks',
            'totalClicks',
            'uniqueVisitors',
            'countries',
            'recentLinks'
        ));
    }
}
