<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = ['business_id','event_id','quote_id','number','status','currency','subtotal','tax_total','total','issued_at','due_at','notes'];

    protected function casts(): array { return ['subtotal'=>'decimal:2','tax_total'=>'decimal:2','total'=>'decimal:2','issued_at'=>'date','due_at'=>'date']; }

    public function event(): BelongsTo { return $this->belongsTo(Event::class)->withTrashed(); }
    public function quote(): BelongsTo { return $this->belongsTo(Quote::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->payments->sum(fn (Payment $payment) => (float) $payment->amount);
    }

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->total - $this->paid_amount);
    }
}
