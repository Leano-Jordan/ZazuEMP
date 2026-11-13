<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessLicense extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'business_id',
        'license_id',
        'plan',
        'status',
        'starts_at',
        'expires_at',
        'features',
        'metadata',
        'activated_at',
        'last_local_check_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'features' => 'array',
            'metadata' => 'array',
            'activated_at' => 'datetime',
            'last_local_check_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function isActive(?\DateTimeInterface $at = null): bool
    {
        $at ??= now();

        if ($this->status !== 'active') {
            return false;
        }

        if ($this->starts_at && $at < $this->starts_at) {
            return false;
        }

        return ! $this->expires_at || $at < $this->expires_at;
    }

    /**
     * Resolve the workspace's effective commercial tier.
     *
     * An absent, expired, not-yet-active, revoked or unknown entitlement always
     * falls back to Basic. This is a tier-resolution boundary only; it does not
     * block access to existing customer data or enforce unapproved feature limits.
     */
    public function effectiveTier(?\DateTimeInterface $at = null): string
    {
        if (! $this->isActive($at)) {
            return 'basic';
        }

        return match ($this->plan) {
            'basic' => 'basic',
            'intermediate' => 'intermediate',
            'advanced', 'solo' => 'advanced',
            default => 'basic',
        };
    }

    public function statusLabel(?\DateTimeInterface $at = null): string
    {
        $at ??= now();

        if ($this->status === 'revoked') {
            return 'Revoked';
        }

        if ($this->isActive($at)) {
            return 'Active';
        }

        if ($this->starts_at && $at < $this->starts_at) {
            return 'Not yet active';
        }

        return 'Expired';
    }
}