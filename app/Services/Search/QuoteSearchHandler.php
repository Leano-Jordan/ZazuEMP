<?php

namespace App\Services\Search;

use App\Models\Business;
use App\Models\Quote;
use Illuminate\Database\Eloquent\Builder;

class QuoteSearchHandler extends AbstractWorkspaceSearchHandler
{
    public function search(Business $business, string $term, string $status, ?string $from, ?string $to): array
    {
        $query = Quote::query()
            ->whereHas('event', fn (Builder $event) => $event->where('business_id', $business->id));

        if ($term !== '') {
            $query->where(function (Builder $builder) use ($term) {
                $builder
                    ->where('reference', 'like', '%'.$term.'%')
                    ->orWhere('status', 'like', '%'.$term.'%')
                    ->orWhereHas('event', fn (Builder $event) => $event
                        ->where('name', 'like', '%'.$term.'%')
                        ->orWhere('reference', 'like', '%'.$term.'%')
                        ->orWhere('customer_name', 'like', '%'.$term.'%'));
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query->with('event')->latest()->limit(10)->get()->map(fn (Quote $quote) => [
            'type' => 'quote',
            'type_label' => 'Quote',
            'title' => $quote->reference,
            'meta' => trim(($quote->event?->name ?: 'Job').' · '.($quote->currency ?: '')),
            'status' => $quote->status,
            'date' => $quote->created_at?->format('d M Y'),
            'href' => route('quotes.show', $quote),
            'sort_date' => $quote->created_at?->toDateTimeString(),
        ])->all();
    }
}
