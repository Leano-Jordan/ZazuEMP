<?php

namespace App\Support\Offline;

use App\Models\BusinessCapability;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Event;
use App\Models\SyncEntityIdentity;
use App\Models\SyncMutation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class OfflineDomainMutationHandler implements SyncMutationHandler
{
    public function __construct(private readonly SyncEntityIdentityRegistry $identities)
    {
    }

    public function apply(SyncMutation $mutation): void
    {
        $record = match ($mutation->entity_type) {
            'customer' => $this->customer($mutation),
            'job' => $this->event($mutation),
            'service' => $this->capability($mutation),
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
