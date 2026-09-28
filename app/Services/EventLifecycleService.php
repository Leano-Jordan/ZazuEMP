<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Event;
use App\Models\EventPreparationItem;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PurchaseOrder;

final class EventLifecycleService
{
    /**
     * Lock the business first, then the event, so all commercial mutation paths
     * share the same lock ordering and lifecycle boundary.
     */
    public function lock(int $businessId, int $eventId): Event
    {
        Business::query()
            ->whereKey($businessId)
            ->lockForUpdate()
            ->firstOrFail();

        return Event::query()
            ->where('business_id', $businessId)
            ->lockForUpdate()
            ->findOrFail($eventId);
    }

    public function assertOperational(Event $event): void
    {
        abort_if(
            $event->isClosed(),
            422,
            'Closed work cannot be changed by an operational workflow.'
        );
    }

    public function assertFinanciallyActive(Event $event): void
    {
        abort_if(
            $event->status === 'cancelled',
            422,
            'Cancelled work cannot receive new financial records.'
        );
    }

    public function assertTransitionAllowed(Event $event, string $newStatus): void
    {
        abort_unless(
            $event->canTransitionTo($newStatus),
            422,
            'That status change is not allowed for this work record.'
        );

        if ($newStatus === $event->status) {
            return;
        }

        if ($newStatus === 'completed') {
            abort_if(
                EventPreparationItem::query()
                    ->where('business_id', $event->business_id)
                    ->where('event_id', $event->id)
                    ->whereIn('status', ['open', 'blocked'])
                    ->exists(),
                422,
                'Work cannot be completed while preparation items are still open or blocked.'
            );

            abort_if(
                PurchaseOrder::query()
                    ->where('business_id', $event->business_id)
                    ->where('event_id', $event->id)
                    ->whereIn('status', ['draft', 'sent', 'ordered'])
                    ->exists(),
                422,
                'Work cannot be completed while a linked purchase order is still active.'
            );
        }

        if ($newStatus === 'cancelled') {
            abort_if(
                Event::query()
                    ->whereKey($event->id)
                    ->whereHas('quotes', fn ($query) => $query->whereIn('status', ['sent', 'accepted']))
                    ->exists(),
                422,
                'Work with an active or accepted quote cannot be cancelled. Resolve the quote first.'
            );

            abort_if(
                PurchaseOrder::query()
                    ->where('business_id', $event->business_id)
                    ->where('event_id', $event->id)
                    ->whereIn('status', ['draft', 'sent', 'ordered'])
                    ->exists(),
                422,
                'Work cannot be cancelled while a linked purchase order is still active. Cancel the purchase order first.'
            );

            abort_if(
                Invoice::query()
                    ->where('business_id', $event->business_id)
                    ->where('event_id', $event->id)
                    ->where('status', '!=', 'void')
                    ->exists(),
                422,
                'Work cannot be cancelled while a linked invoice remains financially active. Resolve or void the invoice first.'
            );

            abort_if(
                Payment::query()
                    ->where('business_id', $event->business_id)
                    ->where('event_id', $event->id)
                    ->exists(),
                422,
                'Work cannot be cancelled after a payment has been recorded. Reconcile the payment first.'
            );
        }
    }
}
