<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Money;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'event_id',
        'quote_id',
        'number',
        'status',
        'currency',
        'subtotal',
        'tax_total',
        'total',
        'issued_at',
        'due_at',
        'notes',
        'tax_rate_id',
        'tax_code',
        'tax_label',
        'tax_treatment',
        'tax_rate',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'issued_at' => 'date',
            'due_at' => 'date',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class)->withTrashed();
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function taxRateRecord(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getPaidAmountAttribute(): float
    {
        $this->loadMissing('payments');

        return (float) Money::fromCents(
            $this->payments->sum(fn (Payment $payment) => Money::toCents((string) $payment->amount))
        );
    }

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->total - $this->paid_amount);
    }
}