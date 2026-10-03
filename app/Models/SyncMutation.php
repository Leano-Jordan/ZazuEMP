<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncMutation extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $attributes = [
        'status' => 'pending',
        'attempts' => 0,
    ];

    protected $fillable = [
        'business_id',
        'sync_device_id',
        'stream',
        'sequence',
        'mutation_id',
        'entity_type',
        'entity_id',
        'operation',
        'status',
        'payload',
        'attempts',
        'last_error',
        'occurred_at',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'occurred_at' => 'datetime',
            'applied_at' => 'datetime',
        ];
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending')->orderBy('occurred_at');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(SyncDevice::class, 'sync_device_id');
    }
}
