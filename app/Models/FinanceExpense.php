<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceExpense extends Model
{
    use HasFactory;

    protected $table = 'finance_expenses';
    protected $fillable = ['business_id','event_id','supplier_id','purchase_order_id','description','amount','currency','expense_date','status','reference','notes'];

    protected function casts(): array { return ['amount'=>'decimal:2','expense_date'=>'date']; }

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function event(): BelongsTo { return $this->belongsTo(Event::class)->withTrashed(); }
}
