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

        return ! $this->expires_at || $at <= $this->expires_at;
    }
}