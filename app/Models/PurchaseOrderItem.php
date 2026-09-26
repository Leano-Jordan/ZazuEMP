<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['business_id','purchase_order_id','capability_id','description','quantity','unit','unit_price','line_total'];

    protected function casts(): array
    {
        return ['quantity'=>'decimal:2','unit_price'=>'decimal:2','line_total'=>'decimal:2'];
    }

    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function capability(): BelongsTo { return $this->belongsTo(BusinessCapability::class); }
}
