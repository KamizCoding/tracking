<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrivacySettings extends Model
{
    protected $fillable = [
        'user_id',
        'data_retention_days',
        'anonymize_ips',
        'store_raw_user_agents',
    ];

    protected $casts = [
        'anonymize_ips' => 'boolean',
        'store_raw_user_agents' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
