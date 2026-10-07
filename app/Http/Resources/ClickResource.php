<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClickResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tracking_link_id' => $this->tracking_link_id,
            'clicked_at' => $this->clicked_at->format('Y-m-d H:i:s'),
            'country' => $this->country,
            'country_code' => $this->country_code,
            'region' => $this->region,
            'city' => $this->city,
            'device_type' => $this->device_type,
            'browser' => $this->browser,
            'os' => $this->os,
            'is_bot' => $this->is_bot,
            'referrer_host' => $this->referrer_host,
        ];
    }
}
