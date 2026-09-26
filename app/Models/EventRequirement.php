<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'capability_id',
        'description',
        'category',
        'quantity',
        'unit',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function capability(): BelongsTo
    {
        return $this->belongsTo(BusinessCapability::class, 'capability_id');
    }
}
