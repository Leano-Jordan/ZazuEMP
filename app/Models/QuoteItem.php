<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_version_id',
        'event_requirement_id',
        'capability_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'line_total',
        'pricing_basis',
        'source_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'source_snapshot' => 'array',
        ];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(QuoteVersion::class, 'quote_version_id');
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(EventRequirement::class, 'event_requirement_id');
    }

    public function capability(): BelongsTo
    {
        return $this->belongsTo(BusinessCapability::class, 'capability_id');
    }
}