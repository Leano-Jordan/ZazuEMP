<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    public const STATUS_TRANSITIONS = [
        'draft' => ['sent', 'cancelled'],
        'sent' => ['ordered', 'cancelled'],
        'ordered' => ['received', 'cancelled'],
        'received' => [],
        'cancelled' => [],
    ];

    use BelongsToBusiness, HasFactory;

    protected $fillable = ['business_id','supplier_id','idempotency_key','last_receipt_idempotency_key','reference','status','currency','total_amount','ordered_at','expected_at','notes'];

    protected function casts(): array
    {
        return ['total_amount' => 'decimal:2', 'ordered_at' => 'date', 'expected_at' => 'date'];
    }

    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function items(): HasMany { return $this->hasMany(PurchaseOrderItem::class); }
    public function receipts(): HasMany { return $this->hasMany(PurchaseOrderReceipt::class); }
}
