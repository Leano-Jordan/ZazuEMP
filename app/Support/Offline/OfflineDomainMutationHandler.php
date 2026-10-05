<?php

namespace App\Support\Offline;

use App\Models\BusinessCapability;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Event;
use App\Models\EventPreparationItem;
use App\Services\EventLifecycleService;
use App\Models\SyncEntityIdentity;
use App\Models\SyncMutation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class OfflineDomainMutationHandler implements SyncMutationHandler
{
    public function __construct(
        private readonly SyncEntityIdentityRegistry $identities,
        private readonly EventLifecycleService $lifecycle,
    )
    {
    }

    public function apply(SyncMutation $mutation): void
    {
        $record = match ($mutation->entity_type) {
            'customer' => $this->customer($mutation),
            'job' => $this->event($mutation),
            'service' => $this->capability($mutation),
            'preparation' => $this->preparation($mutation),
            default => throw ValidationException::withMessages([
                'entity_type' => 'This offline entity is not enabled for server application yet.',
            ]),
        };

        $this->identities->register($record, $mutation->entity_type, $this->entityUuid($mutation));
    }

    private function customer(SyncMutation $mutation): Customer
    {
        $payload = $this->recordPayload($mutation);
        $customer = $this->existing($mutation);

        if ($mutation->operation !== 'create' && ! $customer instanceof Customer) {
            throw ValidationException::withMessages(['entity_id' => 'The offline customer does not exist on the server.']);
        }

        $attributes = [
            'business_id' => $mutation->business_id,
            'name' => $this->requiredString($payload['name'] ?? null, 'name', 255),
            'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
        ];

        if ($customer instanceof Customer) {
            $customer->update($attributes);
        } else {
            $customer = Customer::create($attributes);
        }

        $phone = $this->nullableString($payload['phone'] ?? null, 64);
        $email = $this->nullableString($payload['email'] ?? null, 255);

        if ($phone !== null || $email !== null) {
            $contact = $customer->contacts()->where('is_primary', true)->first()
                ?? $customer->contacts()->first();

            if ($contact) {
                $contact->update(['phone' => $phone, 'email' => $email]);
            } else {
                CustomerContact::create([
                    'customer_id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $phone,
                    'email' => $email,
                    'label' => 'Primary',
                    'is_primary' => true,
                ]);
            }
        }

        return $customer->fresh();
    }

    private function event(SyncMutation $mutation): Event
    {
        $payload = $this->recordPayload($mutation);
        $event = $this->existing($mutation);

        if ($mutation->operation !== 'create' && ! $event instanceof Event) {
            throw ValidationException::withMessages(['entity_id' => 'The offline job does not exist on the server.']);
        }

        $status = $event?->status ?: 'draft';
        $attributes = [
            'business_id' => $mutation->business_id,
            'reference' => $event?->reference ?: 'OFFLINE-'.strtoupper(substr($this->entityUuid($mutation), 0, 8)),
            'name' => $this->requiredString($payload['name'] ?? null, 'name', 255),
            'customer_name' => $this->nullableString($payload['customer_name'] ?? null, 255),
            'event_date' => $payload['event_date'] ?? null,
            'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
            'status' => $status,
        ];

        if ($event instanceof Event) {
            $event->update($attributes);
            return $event->fresh();
        }

        return Event::create($attributes);
    }

    private function preparation(SyncMutation $mutation): EventPreparationItem
    {
        if (! in_array($mutation->operation, ['create', 'upsert', 'update'], true)) {
            throw ValidationException::withMessages(['operation' => 'This offline preparation operation is not supported.']);
        }

        $payload = $this->recordPayload($mutation);
        $eventUuid = $payload['event_local_id'] ?? null;

        if (! is_string($eventUuid) || ! Str::isUuid($eventUuid)) {
            throw ValidationException::withMessages(['payload' => 'A valid local job identity is required for offline preparation.']);
        }

        $eventIdentity = SyncEntityIdentity::query()
            ->where('business_id', $mutation->business_id)
            ->where('entity_type', 'job')
            ->where('entity_uuid', Str::lower($eventUuid))
            ->first();

        if (! $eventIdentity) {
            throw ValidationException::withMessages(['payload' => 'The offline preparation item references an unknown job.']);
        }

        $event = Event::query()
            ->where('business_id', $mutation->business_id)
            ->find($eventIdentity->record_id);

        if (! $event) {
            throw ValidationException::withMessages(['payload' => 'The offline preparation item references a job that no longer exists.']);
        }

        $lockedEvent = $this->lifecycle->lock($mutation->business_id, $event->id);
        $this->lifecycle->assertOperational($lockedEvent);

        $item = $this->existing($mutation);
        if ($mutation->operation === 'update' && ! $item instanceof EventPreparationItem) {
            throw ValidationException::withMessages(['entity_id' => 'The offline preparation item does not exist on the server.']);
        }

        $status = $this->preparationStatus($payload['status'] ?? 'open');
        $attributes = [
            'business_id' => $lockedEvent->business_id,
            'event_id' => $lockedEvent->id,
            'title' => $this->requiredString($payload['title'] ?? null, 'title', 255),
            'category' => $this->nullableString($payload['category'] ?? null, 100),
            'quantity' => $this->nullableQuantity($payload['quantity'] ?? null),
            'unit' => $this->nullableString($payload['unit'] ?? null, 50),
            'status' => $status,
            'due_date' => $this->nullableDate($payload['due_date'] ?? null),
            'completed_at' => $status === 'ready' ? now() : null,
            'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
        ];

        if ($item instanceof EventPreparationItem) {
            $item->update($attributes);
            return $item->fresh();
        }

        return EventPreparationItem::create($attributes);
    }

    private function preparationStatus(mixed $value): string
    {
        if (! is_string($value) || ! in_array($value, ['open', 'blocked', 'ready'], true)) {
            throw ValidationException::withMessages(['status' => 'Offline preparation status must be open, blocked, or ready.']);
        }

        return $value;
    }

    private function nullableQuantity(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value) || (float) $value < 0) {
            throw ValidationException::withMessages(['quantity' => 'Offline preparation quantity must be zero or greater.']);
        }

        return (float) $value;
    }

    private function nullableDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) || ! strtotime($value)) {
            throw ValidationException::withMessages(['due_date' => 'Offline preparation due date must be a valid date.']);
        }

        return $value;
    }

    private function capability(SyncMutation $mutation): BusinessCapability
    {
        $payload = $this->recordPayload($mutation);
        $capability = $this->existing($mutation);

        if ($mutation->operation !== 'create' && ! $capability instanceof BusinessCapability) {
            throw ValidationException::withMessages(['entity_id' => 'The offline service does not exist on the server.']);
        }

        $attributes = [
            'business_id' => $mutation->business_id,
            'name' => $this->requiredString($payload['name'] ?? null, 'name', 255),
            'description' => $this->nullableString($payload['description'] ?? null, 5000),
            'is_active' => (bool) ($payload['active'] ?? true),
            'currency' => 'ZAR',
        ];

        if ($capability instanceof BusinessCapability) {
            $capability->update($attributes);
            return $capability->fresh();
        }

        return BusinessCapability::create($attributes);
    }

    private function existing(SyncMutation $mutation): ?Model
    {
        return SyncEntityIdentity::query()
            ->where('business_id', $mutation->business_id)
            ->where('entity_type', $mutation->entity_type)
            ->where('entity_uuid', $this->entityUuid($mutation))
            ->first()?->record;
    }

    private function recordPayload(SyncMutation $mutation): array
    {
        $record = $mutation->payload['record'] ?? null;

        if (! is_array($record)) {
            throw ValidationException::withMessages(['payload' => 'A record payload is required for offline application.']);
        }

        return $record;
    }

    private function entityUuid(SyncMutation $mutation): string
    {
        $uuid = $mutation->payload['local_id'] ?? null;

        if (! is_string($uuid) || ! Str::isUuid($uuid)) {
            throw ValidationException::withMessages(['entity_id' => 'A valid local synchronization identity is required.']);
        }

        return Str::lower($uuid);
    }

    private function requiredString(mixed $value, string $field, int $max): string
    {
        if (! is_string($value) || trim($value) === '' || mb_strlen($value) > $max) {
            throw ValidationException::withMessages([$field => "A valid {$field} is required."]);
        }

        return trim($value);
    }

    private function nullableString(mixed $value, int $max): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_string($value) || mb_strlen($value) > $max) {
            throw ValidationException::withMessages(['payload' => 'An offline text field is invalid.']);
        }

        return trim($value);
    }
}
