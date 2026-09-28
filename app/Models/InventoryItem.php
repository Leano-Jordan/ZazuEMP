<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = ['business_id','capability_id','sku','name','unit','reorder_level'];

    protected function casts(): array
    {
        return ['reorder_level' => 'decimal:2'];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function capability(): BelongsTo
    {
        return $this->belongsTo(BusinessCapability::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function getOnHandHundredthsAttribute(): int
    {
        if ($this->relationLoaded('movements')) {
            return $this->movements->sum(
                fn (InventoryMovement $movement) => $this->signedMovementHundredths($movement)
            );
        }

        $incoming = $this->movements()
            ->whereIn('type', ['receipt', 'return', 'adjustment_in'])
            ->sum('quantity');

        $outgoing = $this->movements()
            ->whereNotIn('type', ['receipt', 'return', 'adjustment_in'])
            ->sum('quantity');

        return Money::toHundredths((string) $incoming)
            - Money::toHundredths((string) $outgoing);
    }

    private function signedMovementHundredths(InventoryMovement $movement): int
    {
        $quantity = Money::toHundredths((string) $movement->quantity);

        return in_array($movement->type, ['receipt', 'return', 'adjustment_in'], true)
            ? $quantity
            : -$quantity;
    }

    public function getOnHandAttribute(): float
    {
        return $this->on_hand_hundredths / 100;
    }
}