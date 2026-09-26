<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'route_label',
        'provider',
        'origin',
        'destination',
        'distance_km',
        'travel_time_minutes',
        'fuel_price_per_litre',
        'vehicle_consumption_l_per_100km',
        'round_trip',
        'customer_rate_per_km',
        'total_distance_km',
        'fuel_litres',
        'fuel_cost',
        'customer_charge',
        'notes',
        'calculation_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:2',
            'fuel_price_per_litre' => 'decimal:2',
            'vehicle_consumption_l_per_100km' => 'decimal:2',
            'round_trip' => 'boolean',
            'customer_rate_per_km' => 'decimal:2',
            'total_distance_km' => 'decimal:2',
            'fuel_litres' => 'decimal:3',
            'fuel_cost' => 'decimal:2',
            'customer_charge' => 'decimal:2',
            'calculation_snapshot' => 'array',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}