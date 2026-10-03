<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncCursor extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = ['business_id', 'sync_device_id', 'stream', 'last_acknowledged_sequence', 'last_synced_at'];

    protected function casts(): array
    {
        return ['last_synced_at' => 'datetime'];
    }

    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
    public function device(): BelongsTo { return $this->belongsTo(SyncDevice::class, 'sync_device_id'); }
}
