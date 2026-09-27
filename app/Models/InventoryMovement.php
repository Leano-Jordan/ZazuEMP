<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = ['business_id','inventory_item_id','idempotency_key','event_id','purchase_order_id','purchase_order_item_id','type','quantity','unit_cost','movement_date','reference','notes'];

    protected function casts(): array
    {
        return ['quantity'=>'decimal:2','unit_cost'=>'decimal:2','movement_date'=>'date'];
    }

    public function item(): BelongsTo { return $this->belongsTo(InventoryItem::class, 'inventory_item_id'); }
    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function purchaseOrderItem(): BelongsTo { return $this->belongsTo(PurchaseOrderItem::class); }
}
