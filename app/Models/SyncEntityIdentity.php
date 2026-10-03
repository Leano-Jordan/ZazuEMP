<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SyncEntityIdentity extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'entity_type',
        'entity_uuid',
        'record_type',
        'record_id',
    ];

    protected function casts(): array
    {
        return [
            'record_id' => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function record(): MorphTo
    {
        return $this->morphTo('record', 'record_type', 'record_id');
    }
}
