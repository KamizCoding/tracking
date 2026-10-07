<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CampaignResource;
use App\Models\Campaign;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $query = Campaign::where('user_id', Auth::id());

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $campaigns = $query->latest()->paginate(15);

        return CampaignResource::collection($campaigns)
            ->additional([
                'meta' => [
                    'current_page' => $campaigns->currentPage(),
                    'per_page' => $campaigns->perPage(),
                    'total' => $campaigns->total(),
                    'last_page' => $campaigns->lastPage(),
                ],
            ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $campaign = Campaign::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'description' => $request->description,
            'status' => 'active',
        ]);

        return (new CampaignResource($campaign))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Campaign $campaign)
    {
        $this->authorize('view', $campaign);

        $campaign->loadCount('trackingLinks');

        return new CampaignResource($campaign);
    }

    public function update(Request $request, Campaign $campaign)
    {
        $this->authorize('update', $campaign);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'sometimes|in:active,disabled',
        ]);

        $campaign->update($request->only(['name', 'description', 'status']));

        return new CampaignResource($campaign);
    }

    public function destroy(Campaign $campaign)
    {
        $this->authorize('delete', $campaign);

        $campaign->delete();

        return response()->json(null, 204);
    }
}
