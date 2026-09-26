<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = ['business_id','inventory_item_id','event_id','purchase_order_id','type','quantity','unit_cost','movement_date','reference','notes'];

    protected function casts(): array
    {
        return ['quantity'=>'decimal:2','unit_cost'=>'decimal:2','movement_date'=>'date'];
    }

    public function item(): BelongsTo { return $this->belongsTo(InventoryItem::class, 'inventory_item_id'); }
    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
}
