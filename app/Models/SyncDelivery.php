<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncDelivery extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $attributes = [
        'status' => 'pending',
        'attempts' => 0,
    ];

    protected $fillable = [
        'business_id',
        'sync_mutation_id',
        'destination_device_id',
        'stream',
        'delivery_sequence',
        'status',
        'attempts',
        'last_error',
        'delivered_at',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'delivered_at' => 'datetime',
            'applied_at' => 'datetime',
        ];
    }

    public function mutation(): BelongsTo
    {
        return $this->belongsTo(SyncMutation::class, 'sync_mutation_id');
    }

    public function destinationDevice(): BelongsTo
    {
        return $this->belongsTo(SyncDevice::class, 'destination_device_id');
    }
}
