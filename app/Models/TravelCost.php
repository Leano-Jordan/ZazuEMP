<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class TravelCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'route_label',
        'currency',
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

    /**
     * Calculate travel values using integer decimal arithmetic.
     *
     * Distances and consumption are represented in hundredths, litres in thousandths,
     * and monetary values in cents to avoid binary floating-point drift.
     *
     * @return array{
     *     distance_km:string,
     *     total_distance_km:string,
     *     fuel_litres:string,
     *     fuel_cost:string,
     *     customer_charge:string
     * }
     */
    public static function calculate(
        string $distanceKm,
        string $fuelPricePerLitre,
        string $consumptionLPer100Km,
        bool $roundTrip,
        string $customerRatePerKm,
    ): array {
        $distanceHundredths = Money::toHundredths($distanceKm);
        $fuelPriceCents = Money::toCents($fuelPricePerLitre);
        $consumptionHundredths = Money::toHundredths($consumptionLPer100Km);
        $customerRateCents = Money::toCents($customerRatePerKm);

        if ($distanceHundredths <= 0 || $fuelPriceCents <= 0 || $consumptionHundredths <= 0 || $customerRateCents < 0) {
            throw new InvalidArgumentException('Travel calculation inputs must be positive, except the customer rate which may be zero.');
        }

        $totalDistanceHundredths = $distanceHundredths * ($roundTrip ? 2 : 1);

        if ($totalDistanceHundredths > intdiv(PHP_INT_MAX, max(1, $consumptionHundredths))) {
            throw new InvalidArgumentException('Travel calculation exceeds the supported calculation range.');
        }

        // Litres to three decimal places:
        // (distance / 100) * (consumption / 100) / 100, rounded half-up.
        $fuelLitresThousandths = intdiv(
            ($totalDistanceHundredths * $consumptionHundredths) + 500,
            1000
        );

        // Money::fromCents() gives the final two-decimal monetary precision.
        $fuelCostCents = intdiv(
            ($fuelLitresThousandths * $fuelPriceCents) + 500,
            1000
        );

        $customerChargeCents = intdiv(
            ($totalDistanceHundredths * $customerRateCents) + 50,
            100
        );

        return [
            'distance_km' => Money::fromCents($distanceHundredths),
            'total_distance_km' => Money::fromCents($totalDistanceHundredths),
            'fuel_litres' => number_format($fuelLitresThousandths / 1000, 3, '.', ''),
            'fuel_cost' => Money::fromCents($fuelCostCents),
            'customer_charge' => Money::fromCents($customerChargeCents),
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}