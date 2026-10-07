<?php

namespace App\Models;

use App\Services\TrackingUrlService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrackingLink extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'campaign_id',
        'link_domain_id',
        'code',
        'name',
        'destination_url',
        'status',
        'click_count',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }

    public function linkDomain(): BelongsTo
    {
        return $this->belongsTo(LinkDomain::class);
    }

    public function getTrackingUrlAttribute(): string
    {
        $service = app(TrackingUrlService::class);

        return $service->generateTrackingUrl($this);
    }

    public function getQrCodeAttribute(): string
    {
        $builder = new Builder(
            writer: new PngWriter(),
            data: $this->tracking_url,
            size: 300,
            margin: 10,
        );

        $result = $builder->build();

        return 'data:image/png;base64,'.base64_encode($result->getString());
    }
}
