<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Event;
use App\Models\EventCost;
use App\Models\Invoice;
use App\Support\Money;
use Illuminate\Support\Collection;

final class EventProfitabilityService
{
    /**
     * @return Collection<int, array{
     *     event_id: int,
     *     event_reference: string,
     *     event_name: string,
     *     event_date: ?string,
     *     currency: string,
     *     invoiced_cents: int,
     *     actual_cost_cents: int,
     *     profit_cents: int,
     *     margin_percentage: ?float
     * }>
     */
    public function forBusiness(Business $business): Collection
    {
        $totals = [];

        $invoiceTotals = Invoice::query()
            ->where('business_id', $business->id)
            ->whereNotNull('event_id')
            ->whereIn('status', ['issued', 'paid'])
            ->selectRaw('event_id, currency, SUM(total) as aggregate_total')
            ->groupBy('event_id', 'currency')
            ->get();

        foreach ($invoiceTotals as $row) {
            $key = $this->key((int) $row->event_id, (string) $row->currency);
            $totals[$key] = [
                'event_id' => (int) $row->event_id,
                'currency' => (string) $row->currency,
                'invoiced_cents' => Money::toCents((string) $row->aggregate_total),
                'actual_cost_cents' => 0,
            ];
        }

        $costTotals = EventCost::query()
            ->where('business_id', $business->id)
            ->whereNotNull('event_id')
            ->whereNotNull('actual_amount')
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw('event_id, currency, SUM(actual_amount) as aggregate_total')
            ->groupBy('event_id', 'currency')
            ->get();

        foreach ($costTotals as $row) {
            $key = $this->key((int) $row->event_id, (string) $row->currency);
            $totals[$key] ??= [
                'event_id' => (int) $row->event_id,
                'currency' => (string) $row->currency,
                'invoiced_cents' => 0,
                'actual_cost_cents' => 0,
            ];
            $totals[$key]['actual_cost_cents'] = Money::toCents((string) $row->aggregate_total);
        }

        if ($totals === []) {
            return collect();
        }

        $events = Event::query()
            ->where('business_id', $business->id)
            ->whereIn('id', array_unique(array_column($totals, 'event_id')))
            ->get()
            ->keyBy('id');

        return collect($totals)
            ->map(function (array $amounts) use ($events): ?array {
                $event = $events->get($amounts['event_id']);

                if (! $event) {
                    return null;
                }

                $profitCents = $amounts['invoiced_cents'] - $amounts['actual_cost_cents'];

                return [
                    ...$amounts,
                    'event_reference' => (string) $event->reference,
                    'event_name' => (string) $event->name,
                    'event_date' => $event->event_date?->toDateString(),
                    'profit_cents' => $profitCents,
                    'margin_percentage' => $amounts['invoiced_cents'] > 0
                        ? round(($profitCents / $amounts['invoiced_cents']) * 100, 2)
                        : null,
                ];
            })
            ->filter()
            ->sortByDesc(fn (array $row): string => $row['event_date'] ?? '')
            ->values();
    }

    private function key(int $eventId, string $currency): string
    {
        return $eventId.'|'.$currency;
    }
}
