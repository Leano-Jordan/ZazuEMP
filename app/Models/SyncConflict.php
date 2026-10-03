<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncConflict extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'business_id', 'sync_device_id', 'mutation_id', 'entity_type', 'entity_id',
        'conflict_type', 'status', 'local_payload', 'remote_payload',
        'resolution_note', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'local_payload' => 'array',
            'remote_payload' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
    public function device(): BelongsTo { return $this->belongsTo(SyncDevice::class, 'sync_device_id'); }
}
