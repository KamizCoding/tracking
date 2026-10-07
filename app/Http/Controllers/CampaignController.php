<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Services\AuditService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private AuditService $auditService,
    ) {}

    public function index(Request $request)
    {
        $query = Campaign::where('user_id', Auth::id())
            ->withCount('trackingLinks');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $campaigns = $query->latest()->paginate(10);

        return view('campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $this->authorize('create', Campaign::class);

        return view('campaigns.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Campaign::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $campaign = Campaign::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $this->auditService->log('create', 'Campaign', $campaign->id, [
            'name' => $campaign->name,
        ]);

        return redirect()->route('campaigns.index')
            ->with('success', 'Campaign created successfully!');
    }

    public function show(Campaign $campaign)
    {
        $this->authorize('view', $campaign);

        $campaign->loadCount('trackingLinks');
        $campaign->load('trackingLinks');

        return view('campaigns.show', compact('campaign'));
    }

    public function edit(Campaign $campaign)
    {
        $this->authorize('update', $campaign);

        return view('campaigns.edit', compact('campaign'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        $this->authorize('update', $campaign);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:active,disabled',
        ]);

        $campaign->update($validated);

        $this->auditService->log('update', 'Campaign', $campaign->id, [
            'changes' => $validated,
        ]);

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campaign updated successfully!');
    }

    public function destroy(Campaign $campaign)
    {
        $this->authorize('delete', $campaign);

        $campaign->delete();

        $this->auditService->log('delete', 'Campaign', $campaign->id, [
            'name' => $campaign->name,
        ]);

        return redirect()->route('campaigns.index')
            ->with('success', 'Campaign deleted successfully!');
    }

    public function enable(Campaign $campaign)
    {
        $this->authorize('update', $campaign);

        $campaign->update(['status' => 'active']);

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campaign enabled successfully!');
    }

    public function disable(Campaign $campaign)
    {
        $this->authorize('update', $campaign);

        $campaign->update(['status' => 'disabled']);

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Campaign disabled successfully!');
    }
}
