<?php

namespace App\Services\Search;

use App\Models\Business;
use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Builder;

class PurchaseOrderSearchHandler extends AbstractWorkspaceSearchHandler
{
    public function search(Business $business, string $term, string $status, ?string $from, ?string $to): array
    {
        $query = PurchaseOrder::query()
            ->where('business_id', $business->id)
            ->with(['supplier', 'event']);

        if ($term !== '') {
            $query->where(function (Builder $builder) use ($term, $business) {
                $builder
                    ->where('reference', 'like', '%'.$term.'%')
                    ->orWhere('status', 'like', '%'.$term.'%')
                    ->orWhereHas('supplier', fn (Builder $supplier) => $supplier->where('name', 'like', '%'.$term.'%'))
                    ->orWhereHas('event', fn (Builder $event) => $event
                        ->where('business_id', $business->id)
                        ->where(function (Builder $nested) use ($term) {
                            $nested
                                ->where('reference', 'like', '%'.$term.'%')
                                ->orWhere('name', 'like', '%'.$term.'%');
                        }));
            });
        }

        $this->applyStatusAndDate($query, $status, $from, $to, 'ordered_at');

        return $query->latest()->limit(10)->get()->map(fn (PurchaseOrder $order) => [
            'type' => 'purchase_order',
            'type_label' => 'Purchase order',
            'title' => $order->reference,
            'meta' => trim(($order->supplier?->name ?: 'Supplier pending').' · '.($order->event?->name ?: 'Unassigned')),
            'status' => $order->status,
            'date' => $order->ordered_at?->format('d M Y'),
            'href' => route('purchasing.show', $order),
            'sort_date' => ($order->ordered_at ?: $order->created_at)?->toDateString(),
        ])->all();
    }
}
