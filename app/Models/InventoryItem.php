<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = ['business_id','capability_id','sku','name','unit','reorder_level'];

    protected function casts(): array { return ['reorder_level'=>'decimal:2']; }

    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
    public function capability(): BelongsTo { return $this->belongsTo(BusinessCapability::class); }
    public function movements(): HasMany { return $this->hasMany(InventoryMovement::class); }

    public function getOnHandAttribute(): float
    {
        return (float) $this->movements->sum(fn (InventoryMovement $movement) =>
            in_array($movement->type, ['receipt','return','adjustment_in'], true)
                ? (float) $movement->quantity
                : -1 * (float) $movement->quantity
        );
    }
}
