<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrackingLinkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'destination_url' => $this->destination_url,
            'tracking_url' => $this->tracking_url,
            'status' => $this->status,
            'click_count' => $this->click_count,
            'expires_at' => $this->expires_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'campaign' => $this->when($this->campaign, fn () => [
                'id' => $this->campaign->id,
                'name' => $this->campaign->name,
            ]),
            'custom_domain' => $this->when($this->linkDomain, fn () => [
                'id' => $this->linkDomain->id,
                'domain' => $this->linkDomain->domain,
            ]),
        ];
    }
}
