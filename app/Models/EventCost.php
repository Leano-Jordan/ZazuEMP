<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'event_id',
        'category',
        'description',
        'currency',
        'projected_amount',
        'actual_amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'projected_amount' => 'decimal:2',
            'actual_amount' => 'decimal:2',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
