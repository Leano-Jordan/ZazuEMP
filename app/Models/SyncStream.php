<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncStream extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'stream',
        'next_sequence',
    ];

    protected function casts(): array
    {
        return ['next_sequence' => 'integer'];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
