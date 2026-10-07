<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Click extends Model
{
    protected $fillable = [
        'tracking_link_id',
        'ip_address',
        'user_agent',
        'referrer',
        'referrer_host',
        'clicked_at',
        'click_event_id',
        'visitor_id',
        'country',
        'country_code',
        'region',
        'city',
        'latitude',
        'longitude',
        'timezone',
        'asn',
        'isp',
        'location_source',
        'device_type',
        'os',
        'os_version',
        'browser',
        'browser_version',
        'is_bot',
        'bot_name',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
        'latitude' => 'decimal:10',
        'longitude' => 'decimal:10',
        'is_bot' => 'boolean',
    ];

    public function trackingLink(): BelongsTo
    {
        return $this->belongsTo(TrackingLink::class);
    }
}
