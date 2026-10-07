<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrackingLinkResource;
use App\Models\Campaign;
use App\Models\LinkDomain;
use App\Models\TrackingLink;
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
        private TrackingCodeService $trackingCodeService,
    ) {}

    public function index(Request $request)
    {
        $query = TrackingLink::where('user_id', Auth::id());

        if ($request->has('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $links = $query->latest()->paginate(15);

        return TrackingLinkResource::collection($links)
            ->additional([
                'meta' => [
                    'current_page' => $links->currentPage(),
                    'per_page' => $links->perPage(),
                    'total' => $links->total(),
                    'last_page' => $links->lastPage(),
                ],
            ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'destination_url' => 'required|string|max:2048',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'link_domain_id' => 'nullable|exists:link_domains,id',
            'expires_at' => 'nullable|date|after:now',
        ]);

        // Validate and normalize URL using centralized service
        $urlResult = $this->urlValidationService->validateAndNormalize($request->destination_url);

        if (! $urlResult['valid']) {
            return response()->json(['error' => $urlResult['error']], 400);
        }

        // Validate campaign ownership
        if ($request->campaign_id) {
            $campaign = Campaign::where('id', $request->campaign_id)
                ->where('user_id', Auth::id())
                ->first();
            if (! $campaign) {
                return response()->json(['error' => 'Invalid campaign selection'], 400);
            }
        }

        // Validate custom domain ownership if link_domain_id is provided
        if (isset($request->link_domain_id) && $request->link_domain_id) {
            $linkDomain = LinkDomain::where('id', $request->link_domain_id)
                ->where('user_id', Auth::id())
                ->where('status', 'verified')
                ->first();
            if (! $linkDomain) {
                return response()->json(['error' => 'Invalid custom domain selection'], 400);
            }
        }

        $link = TrackingLink::create([
            'user_id' => Auth::id(),
            'campaign_id' => $request->campaign_id,
            'link_domain_id' => $request->link_domain_id,
            'name' => $request->name,
            'destination_url' => $urlResult['normalized'],
            'code' => $this->trackingCodeService->generateUniqueCode(),
            'status' => 'active',
            'expires_at' => $request->expires_at,
        ]);

        return (new TrackingLinkResource($link))
            ->response()
            ->setStatusCode(201);
    }

    public function show(TrackingLink $trackingLink)
    {
        $this->authorize('view', $trackingLink);

        return new TrackingLinkResource($trackingLink);
    }

    public function update(Request $request, TrackingLink $trackingLink)
    {
        $this->authorize('update', $trackingLink);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'destination_url' => 'sometimes|string|max:2048',
            'status' => 'sometimes|in:active,disabled',
            'link_domain_id' => 'nullable|exists:link_domains,id',
            'expires_at' => 'sometimes|nullable|date|after:now',
        ]);

        if ($request->has('destination_url')) {
            $urlResult = $this->urlValidationService->validateAndNormalize($request->destination_url);

            if (! $urlResult['valid']) {
                return response()->json(['error' => $urlResult['error']], 400);
            }

            $request->merge(['destination_url' => $urlResult['normalized']]);
        }

        // Validate custom domain ownership if link_domain_id is being changed
        if ($request->has('link_domain_id') && $request->link_domain_id) {
            $linkDomain = LinkDomain::where('id', $request->link_domain_id)
                ->where('user_id', Auth::id())
                ->where('status', 'verified')
                ->first();
            if (! $linkDomain) {
                return response()->json(['error' => 'Invalid custom domain selection'], 400);
            }
        }

        $trackingLink->update($request->only(['name', 'destination_url', 'status', 'link_domain_id', 'expires_at']));

        return new TrackingLinkResource($trackingLink);
    }

    public function destroy(TrackingLink $trackingLink)
    {
        $this->authorize('delete', $trackingLink);

        $trackingLink->delete();

        return response()->json(null, 204);
    }
}
