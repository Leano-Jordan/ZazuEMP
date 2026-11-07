<?php

namespace App\Services\Search;

use App\Models\Business;
use App\Models\EventAttachment;
use Illuminate\Database\Eloquent\Builder;

class DocumentSearchHandler extends AbstractWorkspaceSearchHandler
{
    public function search(Business $business, string $term, string $status, ?string $from, ?string $to): array
    {
        if ($status !== '') {
            return [];
        }

        $query = EventAttachment::query()
            ->where('business_id', $business->id)
            ->whereHas('event', fn (Builder $event) => $event->where('business_id', $business->id))
            ->with(['event:id,business_id,name,reference']);

        if ($term !== '') {
            $like = '%'.$term.'%';

            $query->where(function (Builder $builder) use ($like): void {
                $builder->where('original_name', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('mime_type', 'like', $like)
                    ->orWhereHas('event', function (Builder $event) use ($like): void {
                        $event->where('name', 'like', $like)
                            ->orWhere('reference', 'like', $like)
                            ->orWhere('customer_name', 'like', $like)
                            ->orWhere('event_type', 'like', $like);
                    });
            });
        }

        $this->applyStatusAndDate($query, '', $from, $to, 'created_at', false);

        return $query->latest('created_at')->limit(10)->get()->map(
            fn (EventAttachment $attachment): array => [
                'type' => 'document',
                'type_label' => 'Document',
                'title' => $attachment->original_name,
                'meta' => $attachment->event->name.' · '.$attachment->event->reference,
                'status' => '',
                'date' => $attachment->created_at?->format('d M Y'),
                'href' => route('work.show', $attachment->event).'#attachments',
                'sort_date' => $attachment->created_at?->toDateTimeString(),
            ]
        )->all();
    }
}
