<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\LinkDomain;
use App\Models\TrackingLink;
use App\Services\AuditService;
use App\Services\TrackingCodeService;
use App\Services\UrlValidationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackingLinkController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private UrlValidationService $urlValidationService,
        private AuditService $auditService,
        private TrackingCodeService $trackingCodeService,
    ) {}

    public function index(Request $request)
    {
        $query = TrackingLink::where('user_id', Auth::id())
            ->with('campaign');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('destination_url', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('campaign_id') && $request->campaign_id !== '') {
            $query->where('campaign_id', $request->campaign_id);
        }

        $links = $query->latest()->paginate(10);

        return view('tracking-links.index', compact('links'));
    }

    public function create()
    {
        $this->authorize('create', TrackingLink::class);

        $campaigns = Campaign::where('user_id', Auth::id())->get();
        $linkDomains = LinkDomain::where('user_id', Auth::id())
            ->where('status', 'verified')
            ->get();

        return view('tracking-links.create', compact('campaigns', 'linkDomains'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'destination_url' => 'required|string|max:2048',
            'slug' => 'nullable|string|max:2048|unique:tracking_links,slug',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'link_domain_id' => 'nullable|exists:link_domains,id',
            'expires_at' => 'nullable|date|after:now',
        ]);

        // Validate and normalize URL using centralized service
        $urlResult = $this->urlValidationService->validateAndNormalize($validated['destination_url']);

        if (! $urlResult['valid']) {
            return back()->withInput()->withErrors(['destination_url' => $urlResult['error']]);
        }

        // Validate campaign ownership
        if ($validated['campaign_id']) {
            $campaign = Campaign::where('id', $validated['campaign_id'])
                ->where('user_id', Auth::id())
                ->first();
            if (! $campaign) {
                return back()->withInput()->withErrors(['campaign_id' => 'Invalid campaign selection.']);
            }
        }

        // Validate custom domain ownership if link_domain_id is provided
        if (isset($validated['link_domain_id']) && $validated['link_domain_id']) {
            $linkDomain = LinkDomain::where('id', $validated['link_domain_id'])
                ->where('user_id', Auth::id())
                ->where('status', 'verified')
                ->first();
            if (! $linkDomain) {
                return back()->withInput()->withErrors(['link_domain_id' => 'Invalid custom domain selection.']);
            }
        }

        $code = $this->trackingCodeService->generateUniqueCode();

        // Use custom slug if provided, otherwise generate from destination URL path
        $slug = $validated['slug'] ?? null;
        if (! $slug) {
            $parsedUrl = parse_url($urlResult['normalized']);
            if (isset($parsedUrl['path']) && $parsedUrl['path'] !== '/') {
                $slug = ltrim($parsedUrl['path'], '/');
                // Include query string if present
                if (isset($parsedUrl['query']) && $parsedUrl['query']) {
                    $slug .= '?'.$parsedUrl['query'];
                }
            }
        }

        $trackingLink = TrackingLink::create([
            'user_id' => Auth::id(),
            'campaign_id' => $validated['campaign_id'] ?? null,
            'link_domain_id' => $validated['link_domain_id'] ?? null,
            'code' => $code,
            'slug' => $slug,
            'name' => $validated['name'],
            'destination_url' => $urlResult['normalized'],
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        $this->auditService->log('create', 'TrackingLink', $trackingLink->id, [
            'name' => $trackingLink->name,
            'code' => $trackingLink->code,
        ]);

        return redirect()->route('tracking-links.show', $trackingLink)
            ->with('success', 'Tracking link created successfully!');
    }

    public function show(TrackingLink $trackingLink)
    {
        $this->authorize('view', $trackingLink);

        $trackingLink->load('campaign');

        return view('tracking-links.show', compact('trackingLink'));
    }

    public function edit(TrackingLink $trackingLink)
    {
        $this->authorize('update', $trackingLink);

        $campaigns = Campaign::where('user_id', Auth::id())->get();
        $linkDomains = LinkDomain::where('user_id', Auth::id())
            ->where('status', 'verified')
            ->get();

        return view('tracking-links.edit', compact('trackingLink', 'campaigns', 'linkDomains'));
    }

    public function update(Request $request, TrackingLink $trackingLink)
    {
        $this->authorize('update', $trackingLink);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'destination_url' => 'sometimes|string|max:2048',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'link_domain_id' => 'nullable|exists:link_domains,id',
            'expires_at' => 'nullable|date|after:now',
            'status' => 'sometimes|in:active,disabled',
        ]);

        // Validate campaign ownership if campaign_id is provided
        if (isset($validated['campaign_id'])) {
            $campaign = Campaign::where('id', $validated['campaign_id'])
                ->where('user_id', Auth::id())
                ->first();
            if (! $campaign) {
                return back()->withInput()->withErrors(['campaign_id' => 'Invalid campaign selection.']);
            }
        }

        // Validate custom domain ownership if link_domain_id is provided
        if (isset($validated['link_domain_id']) && $validated['link_domain_id']) {
            $linkDomain = LinkDomain::where('id', $validated['link_domain_id'])
                ->where('user_id', Auth::id())
                ->where('status', 'verified')
                ->first();
            if (! $linkDomain) {
                return back()->withInput()->withErrors(['link_domain_id' => 'Invalid custom domain selection.']);
            }
        }

        // Validate and normalize URL if destination_url is provided
        if (isset($validated['destination_url'])) {
            $urlResult = $this->urlValidationService->validateAndNormalize($validated['destination_url']);

            if (! $urlResult['valid']) {
                return back()->withInput()->withErrors(['destination_url' => $urlResult['error']]);
            }

            $validated['destination_url'] = $urlResult['normalized'];
        }

        $trackingLink->update($validated);

        $this->auditService->log('update', 'TrackingLink', $trackingLink->id, [
            'changes' => $validated,
        ]);

        return redirect()->route('tracking-links.show', $trackingLink)
            ->with('success', 'Tracking link updated successfully!');
    }

    public function destroy(TrackingLink $trackingLink)
    {
        $this->authorize('delete', $trackingLink);

        $trackingLink->delete();

        $this->auditService->log('delete', 'TrackingLink', $trackingLink->id);

        return redirect()->route('tracking-links.index')
            ->with('success', 'Tracking link deleted successfully!');
    }

    public function enable(TrackingLink $trackingLink)
    {
        $this->authorize('update', $trackingLink);

        $trackingLink->update(['status' => 'active']);

        $this->auditService->log('enable', 'TrackingLink', $trackingLink->id);

        return redirect()->route('tracking-links.show', $trackingLink)
            ->with('success', 'Tracking link enabled successfully!');
    }

    public function disable(TrackingLink $trackingLink)
    {
        $this->authorize('update', $trackingLink);

        $trackingLink->update(['status' => 'disabled']);

        $this->auditService->log('disable', 'TrackingLink', $trackingLink->id);

        return redirect()->route('tracking-links.show', $trackingLink)
            ->with('success', 'Tracking link disabled successfully!');
    }
}
