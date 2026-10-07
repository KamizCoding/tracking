<?php

namespace App\Http\Controllers;

use App\Models\Click;
use App\Models\TrackingLink;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(
        private AuditService $auditService
    ) {}

    public function index()
    {
        $totalUsers = User::count();
        $totalLinks = TrackingLink::count();
        $totalClicks = Click::count();
        $recentUsers = User::latest()->take(5)->get();

        return view('admin.dashboard', compact('totalUsers', 'totalLinks', 'totalClicks', 'recentUsers'));
    }

    public function users()
    {
        $users = User::latest()->paginate(15);

        return view('admin.users', compact('users'));
    }

    public function updateUserRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:user,admin,super_admin',
        ]);

        $oldRole = $user->role;
        $user->update(['role' => $request->role]);

        $this->auditService->log('update_role', 'User', $user->id, [
            'old_role' => $oldRole,
            'new_role' => $request->role,
        ]);

        return back()->with('success', 'User role updated successfully');
    }

    public function trackingLinks()
    {
        $links = TrackingLink::with('user')->latest()->paginate(15);

        return view('admin.tracking-links', compact('links'));
    }
}
