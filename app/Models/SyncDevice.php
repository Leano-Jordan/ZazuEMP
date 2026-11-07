<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SyncDevice extends Model
{
    public const SYNC_TOKEN_IDLE_EXPIRY_DAYS = 180;

    use BelongsToBusiness, HasFactory;

    protected $attributes = [
        'status' => 'active',
    ];

    protected $fillable = [
        'business_id',
        'installation_id',
        'device_name',
        'device_type',
        'status',
        'last_seen_at',
        'token_expires_at',
        'revoked_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
            'token_expires_at' => 'datetime',
            'revoked_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(SyncMutation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')->whereNull('revoked_at');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->revoked_at === null;
    }

    public function refreshTokenActivity(): void
    {
        $lastSeenAt = now();

        $this->forceFill([
            'last_seen_at' => $lastSeenAt,
            'token_expires_at' => $lastSeenAt->copy()->addDays(self::SYNC_TOKEN_IDLE_EXPIRY_DAYS),
        ])->save();
    }
}
