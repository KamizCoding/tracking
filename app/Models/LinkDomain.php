<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LinkDomain extends Model
{
    protected $fillable = [
        'user_id',
        'domain',
        'status',
        'verified',
        'verified_at',
        'verification_token',
        'last_verification_attempt',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'verified_at' => 'datetime',
        'last_verification_attempt' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trackingLinks(): HasMany
    {
        return $this->hasMany(TrackingLink::class);
    }

    /**
     * Generate a random verification token.
     */
    public function generateVerificationToken(): string
    {
        $this->verification_token = bin2hex(random_bytes(32));
        $this->save();

        return $this->verification_token;
    }

    /**
     * Get the expected TXT record value for DNS verification.
     */
    public function getExpectedDnsRecord(): string
    {
        return $this->verification_token;
    }

    /**
     * Check if the domain is verified.
     */
    public function isVerified(): bool
    {
        return $this->verified && $this->status === 'verified';
    }
}
