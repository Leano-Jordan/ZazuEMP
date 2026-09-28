<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Event;
use App\Models\EventRequirement;
use App\Models\Quote;
use App\Models\QuoteVersion;
use App\Models\TaxRate;
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
        ?TaxRate $taxRate,
        ?string $notes,
        string $depositPercent = '0.00'
    ): Quote {
        return DB::transaction(function () use ($event, $requirements, $unitPrices, $currency, $taxRate, $notes, $depositPercent): Quote {
            $businessId = (int) Event::query()->whereKey($event->id)->value('business_id');
            abort_unless($businessId > 0, 404);

            Business::query()
                ->whereKey($businessId)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedEvent = Event::query()
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->findOrFail($event->id);
            if ($lockedEvent->isClosed()) {
                throw ValidationException::withMessages(['event' => 'Closed work cannot receive new quotes.']);
            }
            $currency = strtoupper($currency);

            $quote = $event->quotes()->create([
                'reference' => 'QUO-' . Str::upper(Str::random(8)),
                'status' => 'draft',
                'currency' => $currency,
            ]);

            $version = $quote->versions()->create([
                'version' => 1,
                'status' => 'draft',
                'notes' => $notes,
                'deposit_percent' => $depositPercent,
                ...$this->taxSnapshot($taxRate),
            ]);

            $this->replaceItems($version, $requirements, $unitPrices, $currency);
            $this->recalculate($version);

            return $quote->fresh(['latestVersion']);
        });
    }

    public function createRevision(Quote $quote, Collection $requirements): QuoteVersion
    {
        return DB::transaction(function () use ($quote, $requirements): QuoteVersion {
            [$businessId, $eventId] = $this->resolveQuoteRevisionContext($quote);

            Business::query()
                ->whereKey($businessId)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedEvent = Event::query()
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->findOrFail($eventId);

            $lockedQuote = Quote::query()
                ->whereKey($quote->id)
                ->where('event_id', $lockedEvent->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedEvent->isClosed()) {
                throw ValidationException::withMessages([
                    'quote' => 'Closed work cannot receive new quote revisions.',
                ]);
            }

            $latest = $lockedQuote->versions()
                ->with('items')
                ->orderByDesc('version')
                ->firstOrFail();

            if ($latest->status === 'draft' && $latest->matchesRequirements($requirements)) {
                return $latest;
            }

            $version = $this->createRevisionVersion($lockedQuote, $latest, $requirements);
            $this->recalculate($version);

            return $version->fresh('items');
        });
    }

    /**
     * @return array{0:int,1:int}
     */
    private function resolveQuoteRevisionContext(Quote $quote): array
    {
        $eventId = (int) Quote::query()->whereKey($quote->id)->value('event_id');
        abort_unless($eventId > 0, 404);

        $businessId = (int) Event::query()->whereKey($eventId)->value('business_id');
        abort_unless($businessId > 0, 404);

        return [$businessId, $eventId];
    }

    private function createRevisionVersion(
        Quote $lockedQuote,
        QuoteVersion $latest,
        Collection $requirements
    ): QuoteVersion {
        $previousItems = $latest->items
            ->filter(fn (\App\Models\QuoteItem $item) => $item->event_requirement_id !== null)
            ->keyBy('event_requirement_id');

        $taxSnapshot = $this->revisionTaxSnapshot($latest, $lockedQuote->event->business_id);

        $latest->update(['status' => 'superseded']);

        $version = $lockedQuote->versions()->create([
            'version' => $latest->version + 1,
            'status' => 'draft',
            'subtotal' => '0.00',
            'tax_total' => '0.00',
            'total' => '0.00',
            'notes' => $latest->notes,
            'deposit_percent' => $latest->deposit_percent ?? '0.00',
            'deposit_amount' => $latest->deposit_amount ?? '0.00',
            ...$taxSnapshot,
        ]);

        $lockedQuote->update(['status' => 'draft']);

        $this->copyRevisionItems(
            $version,
            $requirements,
            $previousItems,
            $lockedQuote->currency
        );

        return $version;
    }

    /**
     * @return array<string,mixed>
     */
    private function revisionTaxSnapshot(QuoteVersion $latest, int $businessId): array
    {
        if ($latest->tax_rate_id) {
            $taxRate = TaxRate::query()
                ->whereKey($latest->tax_rate_id)
                ->where('business_id', $businessId)
                ->first();

            if ($taxRate) {
                return $this->taxSnapshot($taxRate);
            }
        }

        return [
            'tax_rate_id' => null,
            'tax_code' => $latest->tax_code,
            'tax_label' => $latest->tax_label ?: 'No tax',
            'tax_treatment' => $latest->tax_treatment ?: 'out_of_scope',
            'tax_rate' => $latest->tax_rate ?: '0.00',
            'tax_snapshot_at' => $latest->tax_snapshot_at ?: now(),
        ];
    }

    /**
     * @param \Illuminate\Support\Collection<int, \App\Models\QuoteItem> $previousItems
     */
    private function copyRevisionItems(
        QuoteVersion $version,
        Collection $requirements,
        Collection $previousItems,
        string $currency
    ): void {
        foreach ($requirements as $requirement) {
            $previousItem = $previousItems->get($requirement->id);
            $capabilityPrice = $requirement->capability?->default_price;
            $capabilityCurrency = strtoupper((string) ($requirement->capability?->currency ?? $currency));
            $unitPrice = $previousItem?->unit_price;

            if ($unitPrice === null && $capabilityPrice !== null && $capabilityCurrency === strtoupper($currency)) {
                $unitPrice = $capabilityPrice;
            }

            $unitPrice = $unitPrice ?? '0.00';
            $quantityHundredths = Money::toHundredths((string) $requirement->quantity);

            $version->items()->create([
                'event_requirement_id' => $requirement->id,
                'capability_id' => $requirement->capability_id,
                'description' => $requirement->description,
                'quantity' => number_format($quantityHundredths / 100, 2, '.', ''),
                'unit' => $requirement->unit,
                'unit_price' => $unitPrice,
                'line_total' => Money::fromCents(
                    Money::multiplyQuantityByPrice(
                        $quantityHundredths,
                        Money::toCents((string) $unitPrice)
                    )
                ),
                'pricing_basis' => $requirement->capability?->pricing_basis,
                'source_snapshot' => $this->snapshotForRequirement($requirement, $currency),
            ]);
        }
    }

    public function updateDraft(
        Quote $quote,
        QuoteVersion $version,
        Collection $requirements,
        array $unitPrices,
        ?TaxRate $taxRate,
        bool $replaceTax,
        ?string $notes,
        string $depositPercent = '0.00'
    ): QuoteVersion {
        return DB::transaction(function () use ($quote, $version, $requirements, $unitPrices, $taxRate, $replaceTax, $notes, $depositPercent): QuoteVersion {
            $lockedQuote = Quote::query()
                ->whereKey($quote->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedEvent = Event::query()
                ->whereKey($lockedQuote->event_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedEvent->isClosed()) {
                throw ValidationException::withMessages(['quote' => 'Closed work cannot receive quote revisions.']);
            }

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

            $taxSnapshot = $replaceTax
                ? $this->taxSnapshot($taxRate)
                : [
                    'tax_rate_id' => $lockedVersion->tax_rate_id,
                    'tax_code' => $lockedVersion->tax_code,
                    'tax_label' => $lockedVersion->tax_label ?: 'No tax',
                    'tax_treatment' => $lockedVersion->tax_treatment ?: 'out_of_scope',
                    'tax_rate' => $lockedVersion->tax_rate ?: '0.00',
                    'tax_snapshot_at' => $lockedVersion->tax_snapshot_at ?: now(),
                ];

            $lockedVersion->update([
                'notes' => $notes,
                'deposit_percent' => $depositPercent,
                ...$taxSnapshot,
            ]);

            $lockedQuote->versions()
                ->where('id', '<>', $lockedVersion->id)
                ->update(['status' => 'superseded']);

            $lockedQuote->update(['status' => 'draft']);

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
                'source_snapshot' => $this->snapshotForRequirement($requirement, $currency),
            ]);
        }
    }

    private function snapshotForRequirement(EventRequirement $requirement, string $currency): array
    {
        $quantityHundredths = Money::toHundredths((string) $requirement->quantity);

        return [
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
            'quote_currency' => strtoupper($currency),
        ];
    }

    private function taxSnapshot(?TaxRate $taxRate): array
    {
        if (!$taxRate) {
            return [
                'tax_rate_id' => null,
                'tax_code' => null,
                'tax_label' => 'No tax',
                'tax_treatment' => 'out_of_scope',
                'tax_rate' => '0.00',
                'tax_snapshot_at' => now(),
            ];
        }

        return [
            'tax_rate_id' => $taxRate->id,
            'tax_code' => $taxRate->code,
            'tax_label' => $taxRate->name,
            'tax_treatment' => $taxRate->treatment,
            'tax_rate' => $taxRate->rate,
            'tax_snapshot_at' => now(),
        ];
    }

    private function recalculate(QuoteVersion $version): void
    {
        $subtotalCents = $version->items()
            ->select('line_total')
            ->get()
            ->sum(fn ($item) => Money::toCents((string) $item->line_total));

        $taxCents = $this->calculateTaxCents($subtotalCents, (string) ($version->tax_rate ?? '0.00'));

        $totalCents = $subtotalCents + $taxCents;
        $depositPercent = Money::toCents((string) ($version->deposit_percent ?? '0.00'));
        $depositCents = intdiv(($totalCents * $depositPercent) + 5000, 10000);

        $version->update([
            'subtotal' => Money::fromCents($subtotalCents),
            'tax_total' => Money::fromCents($taxCents),
            'total' => Money::fromCents($totalCents),
            'deposit_amount' => Money::fromCents($depositCents),
        ]);
    }

    private function calculateTaxCents(int $subtotalCents, string $ratePercent): int
    {
        $ratePercent = trim($ratePercent);

        if ($ratePercent === '' || $ratePercent === '0' || $ratePercent === '0.00') {
            return 0;
        }

        [$whole, $fraction] = array_pad(explode('.', $ratePercent, 2), 2, '0');
        $basisPoints = ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');

        return intdiv(($subtotalCents * $basisPoints) + 5000, 10000);
    }
}
