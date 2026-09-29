<?php

namespace App\Services\Search;

use App\Models\Business;
use App\Models\EventCost;
use Illuminate\Database\Eloquent\Builder;

class CostSearchHandler extends AbstractWorkspaceSearchHandler
{
    public function search(Business $business, string $term, string $status, ?string $from, ?string $to): array
    {
        $query = EventCost::query()
            ->where('business_id', $business->id)
            ->with('event');

        if ($term !== '') {
            $query->where(function (Builder $builder) use ($term, $business) {
                $builder
                    ->where('description', 'like', '%'.$term.'%')
                    ->orWhere('category', 'like', '%'.$term.'%')
                    ->orWhereHas('event', fn (Builder $event) => $event
                        ->where('business_id', $business->id)
                        ->where(function (Builder $nested) use ($term) {
                            $nested
                                ->where('name', 'like', '%'.$term.'%')
                                ->orWhere('reference', 'like', '%'.$term.'%');
                        }));
            });
        }

        $this->applyStatusAndDate($query, $status, $from, $to, 'created_at');

        return $query->latest()->limit(10)->get()->map(fn (EventCost $cost) => [
            'type' => 'cost',
            'type_label' => 'Cost',
            'title' => $cost->description,
            'meta' => trim(($cost->category ?: 'Cost').' · '.($cost->event?->name ?: 'Job')),
            'status' => $cost->status,
            'date' => $cost->created_at?->format('d M Y'),
            'href' => $cost->event ? route('work.costs.index', $cost->event) : route('finance.index'),
            'sort_date' => $cost->created_at?->toDateTimeString(),
        ])->all();
    }
}
