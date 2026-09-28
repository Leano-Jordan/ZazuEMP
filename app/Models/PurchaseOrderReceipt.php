<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderReceipt extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = ['business_id', 'purchase_order_id', 'idempotency_key'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
