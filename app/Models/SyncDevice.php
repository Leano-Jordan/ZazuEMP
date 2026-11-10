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

    public function refreshTokenActivity(string $tokenHash): bool
    {
        $lastSeenAt = now();
        $tokenExpiresAt = $lastSeenAt->copy()->addDays(self::SYNC_TOKEN_IDLE_EXPIRY_DAYS);

        // A token can be revoked after the middleware's initial lookup. Fence the
        // activity write against the persisted credential state so a stale model
        // cannot extend the lifetime of a device that has already been revoked.
        static::query()
            ->whereKey($this->getKey())
            ->active()
            ->where('metadata->sync_token_hash', $tokenHash)
            ->whereNotNull('token_expires_at')
            ->where('token_expires_at', '>', $lastSeenAt)
            ->update([
                'last_seen_at' => $lastSeenAt,
                'token_expires_at' => $tokenExpiresAt,
            ]);

        // A zero-row update can also mean the timestamp values were unchanged
        // within the database's precision. Re-read the complete credential guard
        // before allowing the request through.
        $stillAuthorized = static::query()
            ->whereKey($this->getKey())
            ->active()
            ->where('metadata->sync_token_hash', $tokenHash)
            ->whereNotNull('token_expires_at')
            ->where('token_expires_at', '>', $lastSeenAt)
            ->exists();

        if (! $stillAuthorized) {
            return false;
        }

        $this->forceFill([
            'last_seen_at' => $lastSeenAt,
            'token_expires_at' => $tokenExpiresAt,
        ]);

        return true;
    }
}
