<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SyncEntityIdentity extends Model
{
    use BelongsToBusiness;

    // Existing rows and identities created outside the registry must not be
    // mistaken for a pre-upgrade revision-1 snapshot. The registry explicitly
    // starts genuinely new identities at revision 1.
    protected $attributes = [
        'revision' => 2,
    ];

    protected $fillable = [
        'business_id',
        'entity_type',
        'entity_uuid',
        'record_type',
        'record_id',
        'revision',
    ];

    protected function casts(): array
    {
        return [
            'record_id' => 'integer',
            'revision' => 'integer',
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
