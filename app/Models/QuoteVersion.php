<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class QuoteVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'version',
        'status',
        'subtotal',
        'tax_total',
        'total',
        'deposit_percent',
        'deposit_amount',
        'notes',
        'tax_rate_id',
        'tax_code',
        'tax_label',
        'tax_treatment',
        'tax_rate',
        'tax_snapshot_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'total' => 'decimal:2',
            'deposit_percent' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_snapshot_at' => 'datetime',
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function taxRateRecord(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function matchesRequirements(Collection $requirements): bool
    {
        $this->loadMissing('items');

        $items = $this->items
            ->filter(fn (QuoteItem $item) => $item->event_requirement_id !== null)
            ->keyBy('event_requirement_id');

        if ($items->count() !== $requirements->count()) {
            return false;
        }

        foreach ($requirements as $requirement) {
            $item = $items->get($requirement->id);

            if (!$item) {
                return false;
            }

            $snapshot = is_array($item->source_snapshot) ? $item->source_snapshot : [];

            $expected = [
                'description' => (string) $requirement->description,
                'category' => $requirement->category,
                'quantity' => (string) $requirement->quantity,
                'unit' => $requirement->unit,
                'notes' => $requirement->notes,
                'capability_id' => $requirement->capability_id ? (int) $requirement->capability_id : null,
            ];

            foreach ($expected as $key => $value) {
                if (($snapshot[$key] ?? null) !== $value) {
                    return false;
                }
            }
        }

        return true;
    }
}