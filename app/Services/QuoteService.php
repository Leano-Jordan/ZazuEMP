<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Quote;
use App\Models\QuoteVersion;
use App\Support\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class QuoteService
{
    public function createFromRequirements(
        Event $event,
        Collection $requirements,
        array $unitPrices,
        string $currency,
        ?string $notes
    ): Quote {
        return DB::transaction(function () use ($event, $requirements, $unitPrices, $currency, $notes): Quote {
            $quote = $event->quotes()->create([
                'reference' => 'QUO-' . Str::upper(Str::random(8)),
                'status' => 'draft',
                'currency' => strtoupper($currency),
            ]);

            $version = $quote->versions()->create([
                'version' => 1,
                'status' => 'draft',
                'notes' => $notes,
            ]);

            $this->replaceItems($version, $requirements, $unitPrices, strtoupper($currency));
            $this->recalculate($version);

            return $quote->fresh(['latestVersion']);
        });
    }

    public function createRevision(Quote $quote, Collection $requirements): QuoteVersion
    {
        return DB::transaction(function () use ($quote, $requirements): QuoteVersion {
            $lockedQuote = Quote::query()
                ->whereKey($quote->id)
                ->lockForUpdate()
                ->firstOrFail();

            $latest = $lockedQuote->versions()
                ->with('items')
                ->orderByDesc('version')
                ->firstOrFail();

            if ($latest->status === 'draft') {
                return $latest;
            }

            $previousItems = $latest->items->keyBy('event_requirement_id');
            $version = $lockedQuote->versions()->create([
                'version' => $latest->version + 1,
                'status' => 'draft',
                'subtotal' => '0.00',
                'tax_total' => '0.00',
                'total' => '0.00',
                'notes' => $latest->notes,
            ]);

            $prices = [];

            foreach ($requirements as $requirement) {
                $previousItem = $previousItems->get($requirement->id);
                $savedPrice = $previousItem?->unit_price;

                if (
                    $savedPrice === null &&
                    $requirement->capability?->default_price !== null &&
                    $requirement->capability->currency === $lockedQuote->currency
                ) {
                    $savedPrice = $requirement->capability->default_price;
                }

                $prices[$requirement->id] = $savedPrice ?? '0.00';
            }

            $this->replaceItems($version, $requirements, $prices, $lockedQuote->currency);
            $this->recalculate($version);

            return $version->fresh('items');
        });
    }

    public function updateDraft(
        Quote $quote,
        QuoteVersion $version,
        Collection $requirements,
        array $unitPrices,
        ?string $notes
    ): QuoteVersion {
        return DB::transaction(function () use ($quote, $version, $requirements, $unitPrices, $notes): QuoteVersion {
            $lockedQuote = Quote::query()
                ->whereKey($quote->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedVersion = $lockedQuote->versions()
                ->whereKey($version->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedVersion->status !== 'draft') {
                throw ValidationException::withMessages([
                    'version' => 'Only draft quote revisions can be edited.',
                ]);
            }

            $this->replaceItems($lockedVersion, $requirements, $unitPrices, $lockedQuote->currency);
            $lockedVersion->update(['notes' => $notes]);

            $lockedQuote->versions()
                ->where('id', '<>', $lockedVersion->id)
                ->update(['status' => 'superseded']);

            $this->recalculate($lockedVersion);

            return $lockedVersion->fresh('items');
        });
    }

    private function replaceItems(
        QuoteVersion $version,
        Collection $requirements,
        array $unitPrices,
        string $currency
    ): void {
        $version->items()->delete();

        foreach ($requirements as $requirement) {
            $unitPrice = $unitPrices[$requirement->id] ?? '0.00';
            $quantityHundredths = Money::toHundredths((string) $requirement->quantity);
            $unitPriceCents = Money::toCents((string) $unitPrice);
            $lineTotalCents = Money::multiplyQuantityByPrice($quantityHundredths, $unitPriceCents);

            $version->items()->create([
                'event_requirement_id' => $requirement->id,
                'capability_id' => $requirement->capability_id,
                'description' => $requirement->description,
                'quantity' => number_format($quantityHundredths / 100, 2, '.', ''),
                'unit' => $requirement->unit,
                'unit_price' => Money::fromCents($unitPriceCents),
                'line_total' => Money::fromCents($lineTotalCents),
                'pricing_basis' => $requirement->capability?->pricing_basis,
                'source_snapshot' => [
                    'requirement_id' => $requirement->id,
                    'description' => $requirement->description,
                    'category' => $requirement->category,
                    'quantity' => number_format($quantityHundredths / 100, 2, '.', ''),
                    'unit' => $requirement->unit,
                    'notes' => $requirement->notes,
                    'capability_id' => $requirement->capability_id,
                    'capability_name' => $requirement->capability?->name,
                    'pricing_basis' => $requirement->capability?->pricing_basis,
                    'capability_currency' => $requirement->capability?->currency,
                    'capability_default_price' => $requirement->capability?->default_price,
                    'quote_currency' => $currency,
                ],
            ]);
        }
    }

    private function recalculate(QuoteVersion $version): void
    {
        $subtotalCents = $version->items()
            ->select('line_total')
            ->get()
            ->sum(fn ($item) => Money::toCents((string) $item->line_total));

        $version->update([
            'subtotal' => Money::fromCents($subtotalCents),
            'tax_total' => '0.00',
            'total' => Money::fromCents($subtotalCents),
        ]);
    }
}
