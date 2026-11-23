<?php

namespace App\Support\Offline;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Models\FinanceExpense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Event;
use App\Models\EventCost;
use App\Models\EventPreparationItem;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Asset;
use App\Models\AssetAllocation;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SyncEntityIdentity;
use App\Models\SyncMutation;
use App\Services\EventLifecycleService;
use App\Services\EventScheduleValidator;
use App\Services\FinanceTransactionService;
use App\Services\InvoiceNumberGenerator;
use App\Services\PurchaseOrderReceivingService;
use App\Support\Audit;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class OfflineDomainMutationHandler implements SyncMutationHandler
{
    public function __construct(
        private readonly SyncEntityIdentityRegistry $identities,
        private readonly EventLifecycleService $lifecycle,
        private readonly EventScheduleValidator $scheduleValidator,
        private readonly PurchaseOrderReceivingService $receivingService,
        private readonly FinanceTransactionService $finance,
        private readonly InvoiceNumberGenerator $invoiceNumbers,
    ) {
    }

    public function apply(SyncMutation $mutation): void
    {
        $record = match ($mutation->entity_type) {
            'customer' => $this->customer($mutation),
            'job' => $this->event($mutation),
            'service' => $this->capability($mutation),
            'supplier' => $this->supplier($mutation),
            'preparation' => $this->preparation($mutation),
            'purchase_order' => $this->purchaseOrder($mutation),
            'quote' => $this->quote($mutation),
            'quote_acceptance' => $this->quoteAcceptance($mutation),
            'invoice' => $this->invoice($mutation),
            'payment' => $this->payment($mutation),
            'expense' => $this->expense($mutation),
            'purchase_receipt' => $this->purchaseReceipt($mutation),
            'inventory_item' => $this->inventoryItem($mutation),
            'inventory_movement' => $this->inventoryMovement($mutation),
            'event_cost' => $this->eventCost($mutation),
            'asset' => $this->asset($mutation),
            'asset_allocation' => $this->assetAllocation($mutation),
            default => throw ValidationException::withMessages([
                'entity_type' => 'This offline entity is not enabled for server application yet.',
            ]),
        };

        if ($record instanceof Model) {
            $this->identities->register($record, $mutation->entity_type, $this->entityUuid($mutation));
        }
    }

    private function customer(SyncMutation $mutation): Customer
    {
        if (! in_array($mutation->operation, ['create', 'upsert', 'update'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline customers only support create/upsert/update.']);
        }

        $payload = $this->recordPayload($mutation);
        $customer = $this->existing($mutation);

        if ($mutation->operation !== 'create' && ! $customer instanceof Customer) {
            throw ValidationException::withMessages(['entity_id' => 'The offline customer does not exist on the server.']);
        }

        $attributes = $this->customerAttributes(
            $mutation->business_id,
            $payload,
            $customer instanceof Customer ? $customer : null,
        );

        if ($customer instanceof Customer) {
            $customer->update($attributes);
        } else {
            $customer = Customer::create($attributes);
        }

        $this->syncPrimaryContact($customer, $payload);
        $this->syncAdditionalContact($customer, $payload, 'day');
        $this->syncAdditionalContact($customer, $payload, 'night');

        return $customer->fresh();
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function customerAttributes(int $businessId, array $payload, ?Customer $customer): array
    {
        $attributes = [
            'business_id' => $businessId,
            'name' => $this->requiredString($payload['name'] ?? null, 'name', 255),
        ];

        $optionalFields = [
            'legal_name' => 255,
            'registration_number' => 100,
            'tax_number' => 100,
            'vat_number' => 100,
            'billing_address' => 1000,
            'notes' => 5000,
        ];

        foreach ($optionalFields as $field => $maxLength) {
            if ($customer && ! array_key_exists($field, $payload)) {
                continue;
            }

            $attributes[$field] = $this->nullableString($payload[$field] ?? null, $maxLength);
        }

        return $attributes;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function syncPrimaryContact(Customer $customer, array $payload): void
    {
        $contactName = $this->nullableString($payload['contact_name'] ?? null, 255);
        $phone = $this->nullableString($payload['phone'] ?? null, 64);
        $email = $this->nullableString($payload['email'] ?? null, 255);

        if ($contactName === null && $phone === null && $email === null) {
            return;
        }

        $contact = $customer->contacts()->where('is_primary', true)->first()
            ?? $customer->contacts()->first();

        if ($contact) {
            $contact->update([
                'name' => $contactName ?? $contact->name ?? $customer->name,
                'phone' => array_key_exists('phone', $payload) ? $phone : $contact->phone,
                'email' => array_key_exists('email', $payload) ? $email : $contact->email,
            ]);
            return;
        }

        CustomerContact::create([
            'customer_id' => $customer->id,
            'name' => $contactName ?? $customer->name,
            'phone' => $phone,
            'email' => $email,
            'label' => 'Primary',
            'is_primary' => true,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function syncAdditionalContact(Customer $customer, array $payload, string $period): void
    {
        $nameKey = $period.'_contact_name';
        $phoneKey = $period.'_contact_phone';
        $emailKey = $period.'_contact_email';

        if (! array_key_exists($nameKey, $payload)
            && ! array_key_exists($phoneKey, $payload)
            && ! array_key_exists($emailKey, $payload)) {
            return;
        }

        $name = $this->nullableString($payload[$nameKey] ?? null, 255);
        $phone = $this->nullableString($payload[$phoneKey] ?? null, 50);
        $email = $this->nullableString($payload[$emailKey] ?? null, 255);

        if ($name === null && $phone === null && $email === null) {
            return;
        }

        if ($name === null) {
            throw ValidationException::withMessages([
                $nameKey => 'A contact name is required when a phone number or email is supplied.',
            ]);
        }

        $label = Str::ucfirst($period);
        $contact = $customer->contacts()
            ->where('label', $label)
            ->where('is_primary', false)
            ->first();

        $attributes = [
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'label' => $label,
            'is_primary' => false,
        ];

        if ($contact) {
            $contact->update($attributes);
            return;
        }

        $customer->contacts()->create($attributes);
    }

    private function event(SyncMutation $mutation): Event
    {
        if (! in_array($mutation->operation, ['create', 'upsert', 'update'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline jobs only support create/upsert/update.']);
        }

        $payload = $this->recordPayload($mutation);
        $event = $this->existing($mutation);

        if ($mutation->operation !== 'create' && ! $event instanceof Event) {
            throw ValidationException::withMessages(['entity_id' => 'The offline job does not exist on the server.']);
        }

        $customer = $this->resolveOfflineJobCustomer($mutation, $payload, $event);
        $attributes = $this->offlineJobAttributes($mutation, $payload, $event, $customer);

        if ($event instanceof Event) {
            return $this->updateOfflineEvent($mutation, $event, $payload, $attributes);
        }

        $event = Event::create($attributes);
        $this->createOfflineJobRequirements($event, $payload);

        return $event->fresh(['requirements']);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $attributes
     */
    private function updateOfflineEvent(
        SyncMutation $mutation,
        Event $event,
        array $payload,
        array $attributes,
    ): Event {
        // Lock business → event before checking closure so a concurrent
        // completion/cancellation cannot race this offline Work edit.
        $event = $this->lifecycle->lock($mutation->business_id, $event->id);
        $this->lifecycle->assertOperational($event);

        $nextStatus = $this->requiredString($payload['status'] ?? $event->status, 'status', 30);
        $this->lifecycle->assertTransitionAllowed($event, $nextStatus);
        $attributes['status'] = $nextStatus;

        $this->assertOfflineEventCustomerUnchanged($event, $attributes);
        $this->syncOfflineEventRequirements($event, $payload);

        $event->update($attributes);

        return $event->fresh(['requirements']);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function assertOfflineEventCustomerUnchanged(Event $event, array $attributes): void
    {
        if (
            (int) $event->customer_id !== (int) ($attributes['customer_id'] ?? $event->customer_id)
            && $event->quotes()->exists()
        ) {
            throw ValidationException::withMessages([
                'customer_local_id' => 'The customer cannot be changed after a quote exists for this job.',
            ]);
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function syncOfflineEventRequirements(Event $event, array $payload): void
    {
        if (! array_key_exists('services', $payload)) {
            return;
        }

        $requestedRequirements = $this->offlineJobRequirementDefinitions(
            (int) $event->business_id,
            $payload['services'],
            $payload['other_service'] ?? null
        );

        if (! $event->quotes()->where('status', 'accepted')->exists()) {
            $this->syncOfflineJobRequirements($event, $payload);

            return;
        }

        $currentDescriptions = $event->requirements()->pluck('description')->all();
        $requestedDescriptions = array_keys($requestedRequirements);
        sort($currentDescriptions);
        sort($requestedDescriptions);

        if ($currentDescriptions !== $requestedDescriptions) {
            throw ValidationException::withMessages([
                'services' => 'An accepted quote locks the agreed job scope. Create a separate job for additional work.',
            ]);
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function resolveOfflineJobCustomer(
        SyncMutation $mutation,
        array $payload,
        ?Event $event,
    ): ?Customer {
        if (! array_key_exists('customer_local_id', $payload) || $payload['customer_local_id'] === null) {
            return $event?->customer;
        }

        $customer = $this->resolveIdentityRecord(
            $mutation->business_id,
            'customer',
            $payload['customer_local_id'],
            Customer::class,
        );

        if (! $customer instanceof Customer) {
            throw ValidationException::withMessages([
                'customer_local_id' => 'The offline job references an unknown customer in this business.',
            ]);
        }

        return $customer->loadMissing('primaryContact');
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function offlineJobAttributes(
        SyncMutation $mutation,
        array $payload,
        ?Event $event,
        ?Customer $customer,
    ): array {
        $eventType = $this->requiredString($payload['event_type'] ?? 'Other', 'event_type', 100);
        if (! in_array($eventType, config('zazu.job_types', []), true)) {
            throw ValidationException::withMessages(['event_type' => 'The offline job type is not supported.']);
        }

        return [
            'business_id' => $mutation->business_id,
            'customer_id' => $customer?->id ?? $event?->customer_id,
            'event_day_contact_id' => $this->offlineJobContactId($customer, $payload, 'day', $event),
            'event_night_contact_id' => $this->offlineJobContactId($customer, $payload, 'night', $event),
            'reference' => $event?->reference ?: 'OFFLINE-'.strtoupper(substr($this->entityUuid($mutation), 0, 8)),
            'name' => $this->requiredString($payload['name'] ?? null, 'name', 255),
            'event_type' => $eventType,
            'customer_name' => $customer?->name ?? $this->nullableString($payload['customer_name'] ?? null, 255),
            'customer_phone' => $customer?->primaryContact?->phone ?? $event?->customer_phone,
            'customer_email' => $customer?->primaryContact?->email ?? $event?->customer_email,
            ...$this->offlineScheduleAttributes($payload, $event),
            'event_address' => $this->nullableString($payload['event_address'] ?? null, 255),
            'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
            'status' => $event?->status ?: 'draft',
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, string|null>
     */
    private function offlineScheduleAttributes(array $payload, ?Event $event): array
    {
        $schedule = [
            'event_date' => $this->scheduleDateValue($payload, 'event_date', $event?->event_date?->toDateString()),
            'event_end_date' => $this->scheduleDateValue($payload, 'event_end_date', $event?->event_end_date?->toDateString()),
            'event_start_time' => $this->scheduleTimeValue($payload, 'event_start_time', $event?->event_start_time),
            'event_end_time' => $this->scheduleTimeValue($payload, 'event_end_time', $event?->event_end_time),
            'setup_date' => $this->scheduleDateValue($payload, 'setup_date', $event?->setup_date?->toDateString()),
            'collection_date' => $this->scheduleDateValue($payload, 'collection_date', $event?->collection_date?->toDateString()),
        ];

        $this->scheduleValidator->validate($schedule);

        return $schedule;
    }

    private function scheduleDateValue(array $payload, string $key, ?string $existing): ?string
    {
        return array_key_exists($key, $payload)
            ? $this->nullableDate($payload[$key])
            : $existing;
    }

    private function scheduleTimeValue(array $payload, string $key, ?string $existing): ?string
    {
        return array_key_exists($key, $payload)
            ? $this->nullableTime($payload[$key])
            : ($existing !== null ? substr($existing, 0, 5) : null);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function offlineJobContactId(
        ?Customer $customer,
        array $payload,
        string $period,
        ?Event $event,
    ): ?int {
        $field = 'event_'.$period.'_contact_id';
        $contactId = $payload[$field] ?? null;

        if ($contactId === null || $contactId === '') {
            return $this->existingOfflineJobContactId($customer, $event, $field);
        }

        if (! $customer) {
            throw ValidationException::withMessages([
                $field => 'The offline job contact must belong to its selected customer.',
            ]);
        }

        return $this->selectedOfflineJobContactId($customer, $contactId, $field);
    }

    private function existingOfflineJobContactId(?Customer $customer, ?Event $event, string $field): ?int
    {
        $contactId = $event?->getAttribute($field);
        if (! $customer || ! is_numeric($contactId)) {
            return null;
        }

        $id = (int) $contactId;

        return $customer->contacts()->whereKey($id)->exists() ? $id : null;
    }

    private function selectedOfflineJobContactId(Customer $customer, mixed $value, string $field): int
    {
        if (! is_numeric($value)) {
            throw ValidationException::withMessages([
                $field => 'The offline job contact must belong to its selected customer.',
            ]);
        }

        $contactId = (int) $value;
        if (! $customer->contacts()->whereKey($contactId)->exists()) {
            throw ValidationException::withMessages([
                $field => 'The offline job contact does not belong to the selected customer.',
            ]);
        }

        return $contactId;
    }

    /**
     * Replace the operational requirement list only when an offline client
     * explicitly sends it. Existing matching rows retain quantity, notes,
     * status and capability links; removed descriptions are deleted.
     *
     * @param array<string, mixed> $payload
     */
    private function syncOfflineJobRequirements(Event $event, array $payload): void
    {
        $requirements = $this->offlineJobRequirementDefinitions(
            (int) $event->business_id,
            $payload['services'],
            $payload['other_service'] ?? null
        );

        if ($requirements === []) {
            $event->requirements()->delete();

            return;
        }

        $event->requirements()->whereNotIn('description', array_keys($requirements))->delete();

        foreach ($requirements as $description => $definition) {
            $this->upsertOfflineJobRequirement($event, $description, $definition);
        }
    }

    /**
     * Resolve selected offerings against this business while retaining support
     * for service names already queued by older offline clients.
     *
     * @param mixed $services
     * @return array<string, array{category: string, capability_id: int|null, unit: string}>
     */
    private function offlineJobRequirementDefinitions(int $businessId, mixed $services, mixed $otherService): array
    {
        if (! is_array($services) || ! array_is_list($services)) {
            throw ValidationException::withMessages(['services' => 'Offline job services must be a list.']);
        }

        $legacyCategories = [];
        foreach (config('zazu.service_categories', []) as $category => $availableServices) {
            foreach ($availableServices as $service) {
                $legacyCategories[$service] = $category;
            }
        }

        $names = array_values(array_unique(array_map(
            fn (mixed $service): string => $this->requiredString($service, 'services', 255),
            $services
        )));
        $catalogue = BusinessCapability::query()
            ->where('business_id', $businessId)
            ->whereIn('name', $names)
            ->get()
            ->groupBy('name');

        $requirements = [];
        foreach ($names as $description) {
            $matches = $catalogue->get($description, collect());
            if ($matches->count() > 1) {
                throw ValidationException::withMessages([
                    'services' => 'A selected catalogue offering has an ambiguous name. Rename duplicate offerings before synchronising this job.',
                ]);
            }

            $capability = $matches->first();
            $requirements[$description] = [
                'category' => $capability?->category ?: ($legacyCategories[$description] ?? 'Other'),
                'capability_id' => $capability?->id,
                'unit' => $capability?->default_unit ?: 'service',
            ];
        }

        $otherService = $this->nullableString($otherService, 100);
        if ($otherService !== null) {
            $requirements[$otherService] = [
                'category' => 'Other',
                'capability_id' => null,
                'unit' => 'service',
            ];
        }

        return $requirements;
    }

    /**
     * @param array{category: string, capability_id: int|null, unit: string} $definition
     */
    private function upsertOfflineJobRequirement(Event $event, string $description, array $definition): void
    {
        $requirement = $event->requirements()->where('description', $description)->first();
        if ($requirement) {
            $requirement->update([
                'category' => $definition['category'],
                'capability_id' => $definition['capability_id'],
            ]);

            return;
        }

        $event->requirements()->create([
            'capability_id' => $definition['capability_id'],
            'description' => $description,
            'category' => $definition['category'],
            'quantity' => 1,
            'unit' => $definition['unit'],
            'status' => 'open',
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function createOfflineJobRequirements(Event $event, array $payload): void
    {
        $requirements = $this->offlineJobRequirementDefinitions(
            (int) $event->business_id,
            $payload['services'] ?? [],
            $payload['other_service'] ?? null
        );

        foreach ($requirements as $description => $definition) {
            $event->requirements()->create([
                'capability_id' => $definition['capability_id'],
                'description' => $description,
                'category' => $definition['category'],
                'quantity' => 1,
                'unit' => $definition['unit'],
                'status' => 'open',
            ]);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
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
            ->where('record_type', (new Event())->getMorphClass())
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

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function quote(SyncMutation $mutation): \App\Models\Quote
    {
        if (! in_array($mutation->operation, ['create', 'upsert', 'update'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline quotes only support create/upsert/update.']);
        }

        $payload = $this->recordPayload($mutation);
        $event = $this->resolveOfflineEvent($mutation->business_id, $payload['event_local_id'] ?? null);
        if (! $event) {
            throw ValidationException::withMessages(['payload' => 'An offline quote requires a local job identity.']);
        }

        $quote = $this->existing($mutation);
        if ($quote instanceof \App\Models\Quote) {
            if ($quote->status !== 'draft') {
                throw ValidationException::withMessages(['quote' => 'Only draft offline quotes can be edited.']);
            }

            $version = $quote->versions()->orderByDesc('version')->firstOrFail();
            $this->writeOfflineQuoteVersion($version, $payload);
            return $quote->fresh(['latestVersion.items']);
        }

        $status = $payload['status'] ?? 'draft';
        if (! in_array($status, ['draft', 'sent'], true)) {
            throw ValidationException::withMessages(['status' => 'Offline quotes may only be created as draft or sent.']);
        }

        $currency = strtoupper((string) ($payload['currency'] ?? 'ZAR'));
        if (! array_key_exists($currency, config('zazu.currencies', []))) {
            throw ValidationException::withMessages(['currency' => 'Offline quotes must use a supported business currency.']);
        }

        $quote = \App\Models\Quote::create([
            'event_id' => $event->id,
            'reference' => $this->nullableString($payload['reference'] ?? null, 100)
                ?? 'QUO-OFFLINE-'.strtoupper(substr($this->entityUuid($mutation), 0, 8)),
            'status' => $status,
            'currency' => $currency,
        ]);

        $version = $quote->versions()->create([
            'version' => 1,
            'status' => $status,
            'subtotal' => '0.00',
            'tax_total' => '0.00',
            'total' => '0.00',
            'deposit_percent' => $this->offlineDepositPercent($payload['deposit_percent'] ?? '0.00'),
            'deposit_amount' => '0.00',
            'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
            'tax_rate_id' => null,
            'tax_code' => null,
            'tax_label' => 'No tax',
            'tax_treatment' => 'out_of_scope',
            'tax_rate' => '0.00',
            'tax_snapshot_at' => now(),
        ]);

        $this->writeOfflineQuoteVersion($version, $payload);

        return $quote->fresh(['latestVersion.items']);
    }

    private function writeOfflineQuoteVersion(\App\Models\QuoteVersion $version, array $payload): void
    {
        if (array_key_exists('deposit_percent', $payload)) {
            $version->update([
                'deposit_percent' => $this->offlineDepositPercent($payload['deposit_percent']),
            ]);
        }

        $items = $payload['items'] ?? [];
        if (! is_array($items) || $items === []) {
            throw ValidationException::withMessages(['items' => 'An offline quote requires at least one line.']);
        }

        $version->items()->delete();
        $subtotalCents = 0;

        foreach ($items as $item) {
            if (! is_array($item)) {
                throw ValidationException::withMessages(['items' => 'Offline quote lines must be objects.']);
            }

            $quantityHundredths = $this->positiveQuantity($item['quantity'] ?? null);
            $unitPrice = $this->nonNegativeMoney($item['unit_price'] ?? '0.00');
            $unitPriceCents = Money::toCents($unitPrice);

            try {
                $lineTotalCents = Money::multiplyQuantityByPrice(
                    $quantityHundredths,
                    $unitPriceCents
                );
            } catch (\InvalidArgumentException) {
                throw ValidationException::withMessages(['items' => 'Offline quote line total exceeds the supported calculation range.']);
            }

            $version->items()->create([
                'description' => $this->requiredString($item['description'] ?? null, 'description', 500),
                'quantity' => Money::fromCents($quantityHundredths),
                'unit' => $this->nullableString($item['unit'] ?? null, 100),
                'unit_price' => Money::fromCents($unitPriceCents),
                'line_total' => Money::fromCents($lineTotalCents),
                'capability_id' => null,
                'event_requirement_id' => null,
                'source_snapshot' => [
                    'offline' => true,
                    'quote_currency' => $version->quote->currency,
                ],
            ]);

            if ($lineTotalCents > PHP_INT_MAX - $subtotalCents) {
                throw ValidationException::withMessages(['items' => 'Offline quote total exceeds the supported calculation range.']);
            }
            $subtotalCents += $lineTotalCents;
        }

        $taxCents = 0;
        $totalCents = $subtotalCents;
        $depositPercent = Money::toCents((string) ($version->deposit_percent ?? '0.00'));
        $depositWhole = intdiv($totalCents, 10000) * $depositPercent;
        $depositRemainder = intdiv(((($totalCents % 10000) * $depositPercent) + 5000), 10000);
        if ($depositWhole > PHP_INT_MAX - $depositRemainder) {
            throw ValidationException::withMessages(['deposit_percent' => 'Offline quote deposit exceeds the supported calculation range.']);
        }
        $depositCents = $depositWhole + $depositRemainder;

        $version->update([
            'subtotal' => Money::fromCents($subtotalCents),
            'tax_total' => Money::fromCents($taxCents),
            'total' => Money::fromCents($totalCents),
            'deposit_amount' => Money::fromCents($depositCents),
        ]);
    }

    private function offlineDepositPercent(mixed $value): string
    {
        try {
            $basisPoints = Money::toCents(is_int($value) || is_float($value) || is_string($value) ? $value : '');
        } catch (\InvalidArgumentException) {
            throw ValidationException::withMessages(['deposit_percent' => 'Offline quote deposit percentage must have at most two decimal places.']);
        }

        if ($basisPoints > 10000) {
            throw ValidationException::withMessages(['deposit_percent' => 'Offline quote deposit percentage must be between 0 and 100.']);
        }

        return Money::fromCents($basisPoints);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function quoteAcceptance(SyncMutation $mutation): ?Model
    {
        if (! in_array($mutation->operation, ['create', 'upsert'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline quote acceptance only supports create/upsert.']);
        }

        $payload = $this->recordPayload($mutation);
        $quote = $this->resolveIdentityRecord($mutation->business_id, 'quote', $payload['quote_local_id'] ?? null, \App\Models\Quote::class);

        if (! $quote) {
            throw ValidationException::withMessages(['quote_local_id' => 'The offline acceptance references an unknown quote.']);
        }

        $customerName = $this->requiredString($payload['customer_name'] ?? null, 'customer_name', 255);
        $event = $this->lifecycle->lock($mutation->business_id, $quote->event_id);
        $this->lifecycle->assertOperational($event);

        $quote->refresh();
        if ($quote->status !== 'sent') {
            throw ValidationException::withMessages(['quote' => 'Only a sent quote can be accepted offline.']);
        }

        $version = $quote->versions()->orderByDesc('version')->lockForUpdate()->firstOrFail();
        if ($version->status !== 'sent') {
            throw ValidationException::withMessages(['quote' => 'The latest quote version is not awaiting acceptance.']);
        }

        $version->update(['status' => 'accepted']);
        $quote->update(['status' => 'accepted']);
        $this->lifecycle->confirmAfterQuoteAcceptance($event);

        Audit::record('quote.customer.accepted', $quote, [
            'customer_name' => $customerName,
            'quote_version' => $version->version,
            'offline' => true,
        ], $mutation->business_id);

        return null;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function invoice(SyncMutation $mutation): Invoice
    {
        if (! in_array($mutation->operation, ['create', 'upsert'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline invoices only support create/upsert.']);
        }

        $existing = $this->existing($mutation);
        if ($existing instanceof Invoice) {
            return $existing->fresh(['items', 'payments']);
        }

        $payload = $this->recordPayload($mutation);
        $quote = $this->resolveIdentityRecord($mutation->business_id, 'quote', $payload['quote_local_id'] ?? null, \App\Models\Quote::class);
        if (! $quote || $quote->status !== 'accepted') {
            throw ValidationException::withMessages(['quote_local_id' => 'Offline invoices require an accepted quote.']);
        }

        $version = $quote->latestVersion()->first();
        if (! $version || $version->status !== 'accepted') {
            throw ValidationException::withMessages(['quote_local_id' => 'The accepted quote version is unavailable offline.']);
        }

        if (Invoice::query()->where('business_id', $mutation->business_id)->where('quote_version_id', $version->id)->exists()) {
            throw ValidationException::withMessages(['quote_local_id' => 'This accepted quote version has already been invoiced.']);
        }

        $business = Business::query()->with('taxProfile')->findOrFail($mutation->business_id);
        $customer = $quote->event?->customer;
        $issuedAt = $this->nullableDate($payload['issued_at'] ?? now()->toDateString()) ?? now()->toDateString();
        $dueAt = $this->nullableDate($payload['due_at'] ?? now()->addDays(7)->toDateString()) ?? now()->addDays(7)->toDateString();
        $invoice = Invoice::create([
            'business_id' => $business->id,
            'idempotency_key' => $mutation->mutation_id,
            'event_id' => $quote->event_id,
            'quote_id' => $quote->id,
            'quote_version_id' => $version->id,
            'number' => $this->invoiceNumbers->nextForBusiness($business->id, Carbon::parse($issuedAt)),
            'business_legal_name' => $business->taxProfile?->legal_name ?: $business->name,
            'business_trading_name' => $business->taxProfile?->trading_name ?: $business->name,
            'business_address' => $business->address,
            'business_email' => $business->email,
            'business_phone' => $business->phone,
            'business_tax_number' => $business->taxProfile?->income_tax_number ?: $business->tax_number,
            'business_vat_number' => $business->taxProfile?->vat_number,
            'customer_name' => $customer?->legal_name ?: $customer?->name,
            'customer_address' => $customer?->billing_address,
            'customer_email' => $customer?->primaryContact?->email,
            'customer_phone' => $customer?->primaryContact?->phone,
            'customer_tax_number' => $customer?->tax_number,
            'customer_vat_number' => $customer?->vat_number,
            'status' => 'issued',
            'currency' => $quote->currency,
            'subtotal' => $version->subtotal,
            'tax_total' => $version->tax_total,
            'total' => $version->total,
            'issued_at' => $issuedAt,
            'due_at' => $dueAt,
            'notes' => $version->notes,
            'tax_rate_id' => $version->tax_rate_id,
            'tax_code' => $version->tax_code,
            'tax_label' => $version->tax_label,
            'tax_treatment' => $version->tax_treatment,
            'tax_rate' => $version->tax_rate,
        ]);

        $version->load('items');
        foreach ($version->items as $item) {
            $invoice->items()->create([
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
                'quote_item_id' => $item->id,
            ]);
        }

        Audit::record('finance.invoice.created', $invoice, [
            'number' => $invoice->number,
            'total' => $invoice->total,
            'currency' => $invoice->currency,
            'offline' => true,
        ], $business->id);

        return $invoice->fresh(['items', 'payments']);
    }

    private function payment(SyncMutation $mutation): Payment
    {
        if (! in_array($mutation->operation, ['create', 'upsert'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline payments only support create/upsert.']);
        }

        $payload = $this->recordPayload($mutation);
        $type = $payload['type'] ?? 'payment';
        if (! is_string($type) || ! in_array($type, ['payment', 'deposit'], true)) {
            throw ValidationException::withMessages(['type' => 'Offline payment type must be payment or deposit.']);
        }

        $method = $payload['method'] ?? null;
        if (! is_string($method) || ! in_array($method, ['cash', 'bank_transfer', 'card', 'other'], true)) {
            throw ValidationException::withMessages(['method' => 'Offline payment method is invalid.']);
        }

        $invoice = $this->resolveIdentityRecord($mutation->business_id, 'invoice', $payload['invoice_local_id'] ?? null, Invoice::class);
        if (! $invoice) {
            throw ValidationException::withMessages(['invoice_local_id' => 'The offline payment references an unknown invoice.']);
        }

        $data = [
            'invoice_id' => $invoice->id,
            'idempotency_key' => $mutation->mutation_id,
            'type' => $type,
            'amount' => $this->positiveMoney($payload['amount'] ?? null),
            'method' => $method,
            'reference' => $this->nullableString($payload['reference'] ?? null, 255),
            'paid_at' => $this->nullableDate($payload['paid_at'] ?? now()->toDateString()) ?? now()->toDateString(),
            'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
        ];

        $this->finance->recordPayment($mutation->business_id, $data, $this->lifecycle);

        return Payment::query()
            ->where('business_id', $mutation->business_id)
            ->where('idempotency_key', $mutation->mutation_id)
            ->firstOrFail();
    }

    private function expense(SyncMutation $mutation): FinanceExpense
    {
        if (! in_array($mutation->operation, ['create', 'upsert'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline expenses only support create/upsert.']);
        }

        $payload = $this->recordPayload($mutation);
        $status = $payload['status'] ?? 'unpaid';
        if (! is_string($status) || ! in_array($status, ['unpaid', 'paid'], true)) {
            throw ValidationException::withMessages(['status' => 'Offline expense status must be unpaid or paid.']);
        }

        $event = $this->resolveOfflineEvent($mutation->business_id, $payload['event_local_id'] ?? null);
        $order = $this->resolveExpensePurchaseOrder($mutation->business_id, $payload);
        $supplier = $this->resolveExpenseSupplier($mutation->business_id, $payload);

        $this->finance->recordExpense($mutation->business_id, $this->businessCurrency($mutation->business_id), [
            'idempotency_key' => $mutation->mutation_id,
            'event_id' => $event?->id,
            'purchase_order_id' => $order?->id,
            'supplier_id' => $supplier?->id,
            'description' => $this->requiredString($payload['description'] ?? null, 'description', 500),
            'amount' => $this->positiveMoney($payload['amount'] ?? null),
            'status' => $status,
            'reference' => $this->nullableString($payload['reference'] ?? null, 255),
            'expense_date' => $this->nullableDate($payload['expense_date'] ?? now()->toDateString()) ?? now()->toDateString(),
            'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
        ], $this->lifecycle);

        return FinanceExpense::query()
            ->where('business_id', $mutation->business_id)
            ->where('idempotency_key', $mutation->mutation_id)
            ->firstOrFail();
    }



    private function resolveExpensePurchaseOrder(int $businessId, array $payload): ?PurchaseOrder
    {
        $uuid = $payload['purchase_order_local_id'] ?? null;

        if ($uuid === null || $uuid === '') {
            return null;
        }

        $order = $this->resolveIdentityRecord($businessId, 'purchase_order', $uuid, PurchaseOrder::class);

        if (! $order instanceof PurchaseOrder) {
            throw ValidationException::withMessages([
                'purchase_order_local_id' => 'The offline expense references an unknown purchase order.',
            ]);
        }

        return $order;
    }

    private function resolveExpenseSupplier(int $businessId, array $payload): ?Supplier
    {
        $uuid = $payload['supplier_local_id'] ?? null;

        if ($uuid === null || $uuid === '') {
            return null;
        }

        $supplier = $this->resolveIdentityRecord($businessId, 'supplier', $uuid, Supplier::class);

        if (! $supplier instanceof Supplier) {
            throw ValidationException::withMessages([
                'supplier_local_id' => 'The offline expense references an unknown supplier.',
            ]);
        }

        return $supplier;
    }

    private function inventoryItem(SyncMutation $mutation): InventoryItem
    {
        if (! in_array($mutation->operation, ['create', 'upsert', 'update'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline inventory items only support create/upsert/update.']);
        }

        $payload = $this->recordPayload($mutation);
        $item = $this->existing($mutation);

        if ($mutation->operation !== 'create' && ! $item instanceof InventoryItem) {
            throw ValidationException::withMessages(['entity_id' => 'The offline inventory item does not exist on the server.']);
        }

        $capability = array_key_exists('capability_local_id', $payload)
            ? $this->resolveOfflineCapability($mutation->business_id, $payload['capability_local_id'], 'product')
            : null;

        $sku = $this->nullableString($payload['sku'] ?? null, 100);
        if ($sku !== null) {
            $duplicate = InventoryItem::query()
                ->where('business_id', $mutation->business_id)
                ->where('sku', $sku)
                ->when($item instanceof InventoryItem, fn ($query) => $query->where('id', '!=', $item->id))
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages(['sku' => 'An inventory item with this SKU already exists in this business.']);
            }
        }

        $attributes = [
            'business_id' => $mutation->business_id,
            'capability_id' => $capability?->id,
            'sku' => $sku,
            'name' => $this->requiredString($payload['name'] ?? null, 'name', 255),
            'unit' => $this->requiredString($payload['unit'] ?? 'unit', 'unit', 50),
            'reorder_level' => $this->nullableQuantity($payload['reorder_level'] ?? '0.00'),
        ];

        if ($item instanceof InventoryItem) {
            $item->update($attributes);
            return $item->fresh();
        }

        return InventoryItem::create($attributes);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function inventoryMovement(SyncMutation $mutation): InventoryMovement
    {
        if (! in_array($mutation->operation, ['create', 'upsert'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline inventory movements only support create/upsert.']);
        }

        $existing = $this->existing($mutation);
        if ($existing instanceof InventoryMovement) {
            return $existing->fresh();
        }

        $payload = $this->recordPayload($mutation);
        $item = $this->resolveIdentityRecord(
            $mutation->business_id,
            'inventory_item',
            $payload['inventory_item_local_id'] ?? null,
            InventoryItem::class,
        );

        if (! $item instanceof InventoryItem) {
            throw ValidationException::withMessages(['inventory_item_local_id' => 'The offline inventory movement references an unknown inventory item.']);
        }

        $event = array_key_exists('event_local_id', $payload)
            ? $this->resolveOfflineEvent($mutation->business_id, $payload['event_local_id'])
            : null;

        $purchaseOrder = array_key_exists('purchase_order_local_id', $payload)
            ? $this->resolveIdentityRecord($mutation->business_id, 'purchase_order', $payload['purchase_order_local_id'], PurchaseOrder::class)
            : null;

        if (array_key_exists('purchase_order_local_id', $payload) && ! $purchaseOrder instanceof PurchaseOrder) {
            throw ValidationException::withMessages(['purchase_order_local_id' => 'The offline inventory movement references an unknown purchase order.']);
        }

        $purchaseOrderItem = null;
        if (array_key_exists('purchase_order_item_local_id', $payload)) {
            $purchaseOrderItem = $this->resolveIdentityRecord(
                $mutation->business_id,
                'purchase_order_item',
                $payload['purchase_order_item_local_id'],
                PurchaseOrderItem::class,
            );

            if (! $purchaseOrderItem instanceof PurchaseOrderItem || ! $purchaseOrder) {
                throw ValidationException::withMessages(['purchase_order_item_local_id' => 'The offline inventory movement references an invalid purchase-order line.']);
            }

            if ((int) $purchaseOrderItem->purchase_order_id !== (int) $purchaseOrder->id) {
                throw ValidationException::withMessages(['purchase_order_item_local_id' => 'The purchase-order line does not belong to the referenced purchase order.']);
            }
        }

        $type = $payload['type'] ?? null;
        if (! is_string($type) || ! in_array($type, ['receipt', 'issue', 'return', 'adjustment_in', 'adjustment_out'], true)) {
            throw ValidationException::withMessages(['type' => 'Offline inventory movement type is invalid.']);
        }

        $quantity = $this->positiveQuantity($payload['quantity'] ?? null);
        $unitCost = $this->nonNegativeMoney($payload['unit_cost'] ?? '0.00');
        $movementDate = $this->nullableDate($payload['movement_date'] ?? now()->toDateString()) ?? now()->toDateString();

        \Illuminate\Support\Facades\DB::transaction(function () use (
            $mutation,
            $item,
            $event,
            $purchaseOrder,
            $purchaseOrderItem,
            $type,
            $quantity,
            $unitCost,
            $movementDate,
            $payload,
            &$existing,
        ): void {
            \App\Models\Business::query()->whereKey($mutation->business_id)->lockForUpdate()->firstOrFail();

            $lockedItem = InventoryItem::query()
                ->where('business_id', $mutation->business_id)
                ->lockForUpdate()
                ->findOrFail($item->id);

            if (in_array($type, ['issue', 'adjustment_out'], true)) {
                $onHand = $lockedItem->on_hand_hundredths;
                $requested = $quantity;
                if ($requested > $onHand) {
                    throw ValidationException::withMessages([
                        'quantity' => 'This offline movement would make stock on hand negative.',
                    ]);
                }
            }

            $existing = InventoryMovement::query()
                ->where('business_id', $mutation->business_id)
                ->where('idempotency_key', $mutation->mutation_id)
                ->first();

            if ($existing instanceof InventoryMovement) {
                return;
            }

            $existing = InventoryMovement::create([
                'business_id' => $mutation->business_id,
                'inventory_item_id' => $lockedItem->id,
                'idempotency_key' => $mutation->mutation_id,
                'event_id' => $event?->id,
                'purchase_order_id' => $purchaseOrder?->id,
                'purchase_order_item_id' => $purchaseOrderItem?->id,
                'type' => $type,
                'quantity' => Money::fromCents($quantity),
                'unit_cost' => $unitCost,
                'movement_date' => $movementDate,
                'reference' => $this->nullableString($payload['reference'] ?? null, 255),
                'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
            ]);

            Audit::record('inventory.movement.recorded', $existing, [
                'type' => $existing->type,
                'quantity' => $existing->quantity,
                'item_id' => $lockedItem->id,
                'offline' => true,
            ], $mutation->business_id);
        });

        return $existing instanceof InventoryMovement ? $existing->fresh() : throw new \LogicException('Offline inventory movement was not recorded.');
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function eventCost(SyncMutation $mutation): EventCost
    {
        if (! in_array($mutation->operation, ['create', 'upsert', 'update'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline event costs only support create/upsert/update.']);
        }

        $payload = $this->recordPayload($mutation);
        $event = $this->resolveOfflineEvent($mutation->business_id, $payload['event_local_id'] ?? null);
        if (! $event) {
            throw ValidationException::withMessages(['event_local_id' => 'An offline cost requires a valid local job identity.']);
        }

        $cost = $this->existing($mutation);
        if ($mutation->operation !== 'create' && ! $cost instanceof EventCost) {
            throw ValidationException::withMessages(['entity_id' => 'The offline cost does not exist on the server.']);
        }

        $currency = strtoupper((string) ($payload['currency'] ?? ''));
        $businessCurrency = $this->businessCurrency($mutation->business_id);
        if ($currency === '' || $currency !== $businessCurrency) {
            throw ValidationException::withMessages(['currency' => 'Offline costs must use the active business currency.']);
        }

        $status = $payload['status'] ?? 'planned';
        if (! is_string($status) || ! in_array($status, ['planned', 'incurred', 'cancelled'], true)) {
            throw ValidationException::withMessages(['status' => 'Offline cost status is invalid.']);
        }

        $projected = $this->nonNegativeMoney($payload['projected_amount'] ?? '0.00');
        $actual = array_key_exists('actual_amount', $payload) && $payload['actual_amount'] !== null && $payload['actual_amount'] !== ''
            ? $this->nonNegativeMoney($payload['actual_amount'])
            : null;

        if ($status === 'planned' && $actual !== null) {
            throw ValidationException::withMessages(['actual_amount' => 'A planned cost cannot have an actual amount yet.']);
        }
        if ($status === 'cancelled' && $actual !== null) {
            throw ValidationException::withMessages(['actual_amount' => 'A cancelled cost cannot have an actual amount.']);
        }
        if ($status === 'incurred' && $actual === null) {
            throw ValidationException::withMessages(['actual_amount' => 'An incurred cost must have an actual amount.']);
        }

        $category = $this->requiredString($payload['category'] ?? null, 'category', 100);
        if (! array_key_exists($category, config('zazu.cost_categories'))) {
            throw ValidationException::withMessages(['category' => 'Offline cost category is invalid.']);
        }

        $attributes = [
            'business_id' => $mutation->business_id,
            'event_id' => $event->id,
            'category' => $category,
            'description' => $this->requiredString($payload['description'] ?? null, 'description', 255),
            'currency' => $currency,
            'projected_amount' => $projected,
            'actual_amount' => $actual,
            'status' => $status,
            'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
        ];

        if ($cost instanceof EventCost) {
            $cost->update($attributes);
            return $cost->fresh();
        }

        return EventCost::create($attributes);
    }

    private function asset(SyncMutation $mutation): Asset
    {
        if (! in_array($mutation->operation, ['create', 'upsert', 'update'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline assets only support create/upsert/update.']);
        }

        $payload = $this->recordPayload($mutation);
        $asset = $this->existing($mutation);

        if ($mutation->operation !== 'create' && ! $asset instanceof Asset) {
            throw ValidationException::withMessages(['entity_id' => 'The offline asset does not exist on the server.']);
        }

        $capability = array_key_exists('capability_local_id', $payload)
            ? $this->resolveOfflineCapability($mutation->business_id, $payload['capability_local_id'], 'rental')
            : null;

        $assetTag = $this->requiredString($payload['asset_tag'] ?? null, 'asset_tag', 100);
        $duplicate = Asset::query()
            ->where('business_id', $mutation->business_id)
            ->where('asset_tag', $assetTag)
            ->when($asset instanceof Asset, fn ($query) => $query->whereKeyNot($asset->id))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages(['asset_tag' => 'An asset with this tag already exists in this business.']);
        }

        $attributes = [
            'business_id' => $mutation->business_id,
            'capability_id' => $capability?->id,
            'asset_tag' => $assetTag,
            'name' => $this->requiredString($payload['name'] ?? null, 'name', 255),
            'status' => $asset instanceof Asset ? ($asset->status ?: 'available') : 'available',
            'condition' => $this->requiredEnum($payload['condition'] ?? 'good', ['good', 'fair', 'poor', 'damaged'], 'condition'),
            'location' => $this->nullableString($payload['location'] ?? null, 255),
            'acquired_at' => $this->nullableDate($payload['acquired_at'] ?? null),
            'purchase_cost' => $this->nonNegativeMoney($payload['purchase_cost'] ?? '0.00'),
            'currency' => $this->businessCurrency($mutation->business_id),
            'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
        ];

        if ($asset instanceof Asset) {
            $asset->update($attributes);
            return $asset->fresh();
        }

        return Asset::create($attributes);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function assetAllocation(SyncMutation $mutation): AssetAllocation
    {
        if (! in_array($mutation->operation, ['create', 'upsert', 'update'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline asset allocations only support create/upsert/update.']);
        }

        $payload = $this->recordPayload($mutation);
        $allocation = $this->existing($mutation);

        $asset = array_key_exists('asset_local_id', $payload)
            ? $this->resolveIdentityRecord($mutation->business_id, 'asset', $payload['asset_local_id'], Asset::class)
            : ($allocation instanceof AssetAllocation ? $allocation->asset()->where('business_id', $mutation->business_id)->first() : null);

        if (! $asset instanceof Asset) {
            throw ValidationException::withMessages(['asset_local_id' => 'The offline allocation references an unknown asset.']);
        }

        if ($allocation instanceof AssetAllocation) {
            if ($allocation->status === 'returned' && $mutation->operation === 'create') {
                return $allocation->fresh();
            }

            $status = $payload['status'] ?? 'returned';
            if ($status !== 'returned') {
                throw ValidationException::withMessages(['status' => 'Offline asset allocation updates may only return an existing allocation.']);
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($mutation, $asset, $allocation, $status, $payload): void {
                $lockedAsset = Asset::query()->where('business_id', $mutation->business_id)->lockForUpdate()->findOrFail($asset->id);
                $lockedAllocation = AssetAllocation::query()
                    ->where('business_id', $mutation->business_id)
                    ->lockForUpdate()
                    ->findOrFail($allocation->id);

                $lockedAllocation->update([
                    'status' => $status,
                    'allocated_until' => $status === 'returned'
                        ? ($lockedAllocation->allocated_until?->toDateString() ?? now()->toDateString())
                        : $this->nullableDate($payload['allocated_until'] ?? null),
                    'notes' => $this->nullableString($payload['notes'] ?? $lockedAllocation->notes, 5000),
                ]);

                $hasActiveAllocations = $lockedAsset->allocations()->where('status', 'allocated')->exists();
                $lockedAsset->update(['status' => $hasActiveAllocations ? 'allocated' : 'available']);

                Audit::record(
                    $status === 'returned' ? 'assets.released' : 'assets.allocated',
                    $lockedAsset,
                    ['allocation_id' => $lockedAllocation->id, 'offline' => true],
                    $mutation->business_id,
                );
            });

            return $allocation->fresh();
        }

        if (! $this->resolveOfflineEvent($mutation->business_id, $payload['event_local_id'] ?? null)) {
            throw ValidationException::withMessages(['event_local_id' => 'An offline allocation requires a valid local job identity.']);
        }

        $event = $this->resolveOfflineEvent($mutation->business_id, $payload['event_local_id']);
        $from = $this->nullableDate($payload['allocated_from'] ?? now()->toDateString());
        $until = $this->nullableDate($payload['allocated_until'] ?? null);
        if ($from === null) {
            throw ValidationException::withMessages(['allocated_from' => 'An allocation start date is required.']);
        }
        if ($until !== null && $until < $from) {
            throw ValidationException::withMessages(['allocated_until' => 'Allocation dates are invalid.']);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($mutation, $asset, $event, $payload, $from, $until, &$allocation): void {
            $lockedAsset = Asset::query()->where('business_id', $mutation->business_id)->lockForUpdate()->findOrFail($asset->id);
            abort_unless(in_array($lockedAsset->status, ['available', 'allocated'], true), 422, 'Only available assets can be allocated offline.');

            $overlapQuery = $lockedAsset->allocations()->where('status', 'allocated');
            if ($until !== null) {
                $overlapQuery->whereDate('allocated_from', '<=', $until);
            }
            $overlap = $overlapQuery->where(function ($query) use ($from): void {
                $query->whereNull('allocated_until')->orWhereDate('allocated_until', '>=', $from);
            })->exists();

            abort_if($overlap, 422, 'This asset is already allocated for the selected period.');

            $allocation = $lockedAsset->allocations()->create([
                'business_id' => $mutation->business_id,
                'event_id' => $event->id,
                'allocated_from' => $from,
                'allocated_until' => $until,
                'status' => 'allocated',
                'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
            ]);

            $lockedAsset->update(['status' => 'allocated']);

            Audit::record('assets.allocated', $lockedAsset, [
                'event_id' => $event->id,
                'allocation_id' => $allocation->id,
                'offline' => true,
            ], $mutation->business_id);
        });

        return $allocation->fresh();
    }

    private function resolveOfflineCapability(int $businessId, mixed $uuid, ?string $expectedType = null): ?BusinessCapability
    {
        if ($uuid === null || $uuid === '') {
            return null;
        }

        $capability = $this->resolveIdentityRecord($businessId, 'service', $uuid, BusinessCapability::class);

        if ($capability && $expectedType !== null && $capability->capability_type !== $expectedType) {
            throw ValidationException::withMessages(['capability_local_id' => "This offline record requires a {$expectedType} catalogue capability."]);
        }

        return $capability;
    }

    private function requiredEnum(mixed $value, array $allowed, string $field): string
    {
        if (! is_string($value) || ! in_array($value, $allowed, true)) {
            throw ValidationException::withMessages([$field => "Offline {$field} is invalid."]);
        }

        return $value;
    }

    private function resolveIdentityRecord(int $businessId, string $entityType, mixed $uuid, string $model): ?Model
    {
        if (! is_string($uuid) || ! Str::isUuid($uuid)) {
            throw ValidationException::withMessages(['payload' => "A valid local {$entityType} identity is required."]);
        }

        $identity = SyncEntityIdentity::query()
            ->where('business_id', $businessId)
            ->where('entity_type', $entityType)
            ->where('record_type', (new $model())->getMorphClass())
            ->where('entity_uuid', Str::lower($uuid))
            ->first();

        if (! $identity) {
            return null;
        }

        $query = $model::query()->whereKey($identity->record_id);

        if ($entityType === 'quote') {
            $query->whereHas('event', fn ($event) => $event->where('business_id', $businessId));
        } else {
            $query->where('business_id', $businessId);
        }

        return $query->first();
    }

    private function businessCurrency(int $businessId): string
    {
        return strtoupper((string) Business::query()->whereKey($businessId)->value('currency'));
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function purchaseOrder(SyncMutation $mutation): PurchaseOrder
    {
        if (! in_array($mutation->operation, ['create', 'upsert'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline purchase orders only support create/upsert.']);
        }

        $payload = $this->recordPayload($mutation);
        $existing = $this->existing($mutation);

        if ($existing instanceof PurchaseOrder) {
            return $existing->fresh(['items']);
        }

        $supplier = $this->resolveOfflineSupplier($mutation->business_id, $payload['supplier_local_id'] ?? null);
        $event = $this->resolveOfflineEvent($mutation->business_id, $payload['event_local_id'] ?? null);
        $currency = strtoupper((string) ($payload['currency'] ?? ''));
        $businessCurrency = strtoupper((string) $supplier->business?->currency);

        if ($currency === '' || $currency !== $businessCurrency) {
            throw ValidationException::withMessages(['currency' => 'Offline purchase orders must use the active business currency.']);
        }

        $lines = $payload['lines'] ?? [];
        if (! is_array($lines) || $lines === []) {
            throw ValidationException::withMessages(['lines' => 'At least one purchase order line is required.']);
        }

        $status = $payload['status'] ?? 'draft';
        if (! in_array($status, ['draft', 'ordered'], true)) {
            throw ValidationException::withMessages(['status' => 'Offline purchase orders may only be created as draft or ordered.']);
        }

        $order = PurchaseOrder::create([
            'business_id' => $mutation->business_id,
            'event_id' => $event?->id,
            'supplier_id' => $supplier->id,
            'idempotency_key' => $mutation->mutation_id,
            'reference' => 'PO-OFFLINE-'.strtoupper(substr($this->entityUuid($mutation), 0, 8)),
            'status' => $status,
            'ordered_at' => $status === 'ordered' ? now()->toDateString() : null,
            'currency' => $currency,
            'expected_at' => $this->nullableDate($payload['expected_at'] ?? null),
            'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
            'total_amount' => '0.00',
        ]);

        $totalCents = 0;
        foreach ($lines as $line) {
            $lineCents = $this->createOfflinePurchaseOrderLine($order, $mutation, $line);
            if ($lineCents > PHP_INT_MAX - $totalCents) {
                throw ValidationException::withMessages(['lines' => 'Offline purchase order total exceeds the supported calculation range.']);
            }
            $totalCents += $lineCents;
        }

        $order->update(['total_amount' => Money::fromCents($totalCents)]);

        return $order->fresh(['items']);
    }

    private function resolveOfflineSupplier(int $businessId, mixed $supplierUuid): Supplier
    {
        if (! is_string($supplierUuid) || ! Str::isUuid($supplierUuid)) {
            throw ValidationException::withMessages(['payload' => 'A valid local supplier identity is required.']);
        }

        $identity = SyncEntityIdentity::query()
            ->where('business_id', $businessId)
            ->where('entity_type', 'supplier')
            ->where('record_type', (new Supplier())->getMorphClass())
            ->where('entity_uuid', Str::lower($supplierUuid))
            ->first();

        $supplier = $identity
            ? Supplier::query()->where('business_id', $businessId)->find($identity->record_id)
            : null;

        if (! $supplier) {
            throw ValidationException::withMessages(['payload' => 'The offline purchase order references an unknown supplier.']);
        }

        return $supplier;
    }

    private function createOfflinePurchaseOrderLine(PurchaseOrder $order, SyncMutation $mutation, mixed $line): int
    {
        if (! is_array($line)) {
            throw ValidationException::withMessages(['lines' => 'Offline purchase order lines must be objects.']);
        }

        $description = $this->requiredString($line['description'] ?? null, 'description', 255);
        $quantityHundredths = $this->positivePurchaseQuantity($line['quantity'] ?? null);
        $unitPriceCents = $this->nonNegativePurchasePrice($line['unit_price'] ?? null);
        try {
            $lineTotalCents = Money::multiplyQuantityByPrice($quantityHundredths, $unitPriceCents);
        } catch (\InvalidArgumentException) {
            throw ValidationException::withMessages(['lines' => 'Offline purchase order line total exceeds the supported calculation range.']);
        }

        $capabilityId = $line['capability_id'] ?? null;
        if ($capabilityId !== null && (
            ! is_numeric($capabilityId)
            || ! BusinessCapability::query()->where('business_id', $mutation->business_id)->whereKey((int) $capabilityId)->exists()
        )) {
            throw ValidationException::withMessages(['lines' => 'An offline purchase order line references an invalid catalogue item.']);
        }

        $lineLocalId = $line['local_id'] ?? null;
        if (! is_string($lineLocalId) || ! Str::isUuid($lineLocalId)) {
            throw ValidationException::withMessages(['lines' => 'Each offline purchase order line requires a valid local identity.']);
        }

        $item = $order->items()->create([
            'business_id' => $mutation->business_id,
            'capability_id' => $capabilityId,
            'description' => $description,
            'quantity' => Money::fromCents($quantityHundredths),
            'received_quantity' => '0.00',
            'unit' => $this->nullableString($line['unit'] ?? null, 50),
            'unit_price' => Money::fromCents($unitPriceCents),
            'line_total' => Money::fromCents($lineTotalCents),
        ]);
        $this->identities->register($item, 'purchase_order_item', $lineLocalId);

        return $lineTotalCents;
    }

    private function purchaseReceipt(SyncMutation $mutation): ?Model
    {
        if (! in_array($mutation->operation, ['create', 'upsert'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline purchase receipts only support create/upsert.']);
        }

        $payload = $this->recordPayload($mutation);
        $purchaseOrderUuid = $payload['purchase_order_local_id'] ?? null;
        if (! is_string($purchaseOrderUuid) || ! Str::isUuid($purchaseOrderUuid)) {
            throw ValidationException::withMessages(['payload' => 'A valid local purchase order identity is required.']);
        }

        $identity = SyncEntityIdentity::query()
            ->where('business_id', $mutation->business_id)
            ->where('entity_type', 'purchase_order')
            ->where('record_type', (new PurchaseOrder())->getMorphClass())
            ->where('entity_uuid', Str::lower($purchaseOrderUuid))
            ->first();

        $order = $identity
            ? PurchaseOrder::query()->where('business_id', $mutation->business_id)->find($identity->record_id)
            : null;
        if (! $order) {
            throw ValidationException::withMessages(['payload' => 'The offline purchase receipt references an unknown purchase order.']);
        }

        $received = $payload['received_quantities'] ?? [];
        if (! is_array($received) || $received === []) {
            throw ValidationException::withMessages(['received_quantities' => 'At least one receipt quantity is required.']);
        }

        $this->receivingService->receive(
            $order,
            $mutation->business_id,
            $this->resolveOfflineReceiptQuantities($order, $mutation->business_id, $received),
            $mutation->mutation_id,
            $this->lifecycle,
        );

        return null;
    }

    private function resolveOfflineReceiptQuantities(PurchaseOrder $order, int $businessId, array $received): array
    {
        $resolved = [];
        $hasPositiveQuantity = false;

        foreach ($received as $lineIdentityOrId => $quantity) {
            $line = $this->resolveOfflinePurchaseOrderLine($order, $businessId, $lineIdentityOrId);
            $normalizedQuantity = $this->nonNegativeQuantity($quantity, 'received_quantities');
            $hasPositiveQuantity = $hasPositiveQuantity || Money::toHundredths($normalizedQuantity) > 0;
            $resolved[$line->id] = $normalizedQuantity;
        }

        if (! $hasPositiveQuantity) {
            throw ValidationException::withMessages([
                'received_quantities' => 'At least one received quantity must be greater than zero.',
            ]);
        }

        return $resolved;
    }

    private function resolveOfflinePurchaseOrderLine(PurchaseOrder $order, int $businessId, mixed $lineIdentityOrId): PurchaseOrderItem
    {
        $line = null;

        if (is_string($lineIdentityOrId) && Str::isUuid($lineIdentityOrId)) {
            $lineIdentity = SyncEntityIdentity::query()
                ->where('business_id', $businessId)
                ->where('entity_type', 'purchase_order_item')
                ->where('record_type', (new PurchaseOrderItem())->getMorphClass())
                ->where('entity_uuid', Str::lower($lineIdentityOrId))
                ->first();

            if ($lineIdentity) {
                $line = PurchaseOrderItem::query()
                    ->where('business_id', $businessId)
                    ->where('purchase_order_id', $order->id)
                    ->find($lineIdentity->record_id);
            }
        } elseif (is_numeric($lineIdentityOrId)) {
            $line = PurchaseOrderItem::query()
                ->where('business_id', $businessId)
                ->where('purchase_order_id', $order->id)
                ->find((int) $lineIdentityOrId);
        }

        if (! $line) {
            throw ValidationException::withMessages([
                'received_quantities' => 'The receipt references an unknown purchase order line for this business and order.',
            ]);
        }

        return $line;
    }

    private function resolveOfflineEvent(int $businessId, mixed $eventUuid): ?Event
    {
        if ($eventUuid === null || $eventUuid === '') {
            return null;
        }

        if (! is_string($eventUuid) || ! Str::isUuid($eventUuid)) {
            throw ValidationException::withMessages(['payload' => 'The offline mutation job identity is invalid.']);
        }

        $identity = SyncEntityIdentity::query()
            ->where('business_id', $businessId)
            ->where('entity_type', 'job')
            ->where('record_type', (new Event())->getMorphClass())
            ->where('entity_uuid', Str::lower($eventUuid))
            ->first();

        $event = $identity
            ? Event::query()->where('business_id', $businessId)->find($identity->record_id)
            : null;

        if (! $event) {
            throw ValidationException::withMessages(['payload' => 'The offline mutation references an unknown job.']);
        }

        $event = $this->lifecycle->lock($businessId, $event->id);
        $this->lifecycle->assertOperational($event);

        return $event;
    }

    private function preparationStatus(mixed $value): string
    {
        if (! is_string($value) || ! in_array($value, ['open', 'blocked', 'ready'], true)) {
            throw ValidationException::withMessages(['status' => 'Offline preparation status must be open, blocked, or ready.']);
        }

        return $value;
    }

    private function nullableQuantity(mixed $value): ?string
    {
        return $value === null || $value === ''
            ? null
            : $this->nonNegativeQuantity($value);
    }

    private function nonNegativeQuantity(mixed $value, string $field = 'quantity'): string
    {
        try {
            $quantityHundredths = Money::toHundredths(is_int($value) || is_float($value) || is_string($value) ? $value : '');
        } catch (\InvalidArgumentException) {
            throw ValidationException::withMessages([
                $field => "Offline {$field} must be non-negative and have at most two decimal places.",
            ]);
        }

        return Money::fromCents($quantityHundredths);
    }

    private function positivePurchaseQuantity(mixed $value): int
    {
        try {
            $quantityHundredths = Money::toHundredths(is_int($value) || is_float($value) || is_string($value) ? $value : '');
        } catch (\InvalidArgumentException) {
            throw ValidationException::withMessages(['quantity' => 'Offline purchase order quantity must be positive and have at most two decimal places.']);
        }

        if ($quantityHundredths <= 0) {
            throw ValidationException::withMessages(['quantity' => 'Offline purchase order quantity must be greater than zero.']);
        }

        return $quantityHundredths;
    }

    private function nonNegativePurchasePrice(mixed $value): int
    {
        try {
            return Money::toCents(is_int($value) || is_float($value) || is_string($value) ? $value : '');
        } catch (\InvalidArgumentException) {
            throw ValidationException::withMessages(['unit_price' => 'Offline purchase order price must be non-negative and have at most two decimal places.']);
        }
    }

    private function positiveQuantity(mixed $value): int
    {
        $quantity = $this->nonNegativeQuantity($value);
        $quantityHundredths = Money::toHundredths($quantity);

        if ($quantityHundredths <= 0) {
            throw ValidationException::withMessages(['quantity' => 'Offline quantity must be greater than zero.']);
        }

        return $quantityHundredths;
    }

    private function positiveMoney(mixed $value): string
    {
        $amount = $this->nonNegativeMoney($value);
        if (Money::toCents($amount) <= 0) {
            throw ValidationException::withMessages(['amount' => 'Offline financial amounts must be greater than zero.']);
        }

        return $amount;
    }

    private function nonNegativeMoney(mixed $value): string
    {
        try {
            $cents = Money::toCents(is_int($value) || is_float($value) || is_string($value) ? $value : '');
        } catch (\InvalidArgumentException) {
            throw ValidationException::withMessages(['amount' => 'Offline financial amounts must be non-negative and have at most two decimal places.']);
        }

        return Money::fromCents($cents);
    }

    private function nullableTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_string($value) || ! preg_match('/^(?:[01]\\d|2[0-3]):[0-5]\\d$/D', $value)) {
            throw ValidationException::withMessages(['time' => 'Offline time must use 24-hour HH:MM format.']);
        }

        return $value;
    }

    private function nullableDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (
            ! is_string($value)
            || ! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D', $value, $parts)
            || ! checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])
        ) {
            throw ValidationException::withMessages(['date' => 'Offline date must be a valid calendar date in YYYY-MM-DD format.']);
        }

        return $value;
    }

    private function supplier(SyncMutation $mutation): Supplier
    {
        if (! in_array($mutation->operation, ['create', 'upsert', 'update'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline suppliers only support create/upsert/update.']);
        }

        $payload = $this->recordPayload($mutation);
        $supplier = $this->existing($mutation);

        $attributes = [
            'business_id' => $mutation->business_id,
            'name' => $this->requiredString($payload['name'] ?? null, 'name', 255),
            'contact_name' => $this->nullableString($payload['contact_name'] ?? null, 255),
            'email' => $this->nullableString($payload['email'] ?? null, 255),
            'phone' => $this->nullableString($payload['phone'] ?? null, 64),
            'notes' => $this->nullableString($payload['notes'] ?? null, 5000),
        ];

        if ($supplier instanceof Supplier) {
            $supplier->update($attributes);
            return $supplier->fresh();
        }

        return Supplier::create($attributes);
    }

    private function capability(SyncMutation $mutation): BusinessCapability
    {
        if (! in_array($mutation->operation, ['create', 'upsert', 'update'], true)) {
            throw ValidationException::withMessages(['operation' => 'Offline services only support create/upsert/update.']);
        }

        $payload = $this->recordPayload($mutation);
        $capability = $this->existing($mutation);

        if ($mutation->operation !== 'create' && ! $capability instanceof BusinessCapability) {
            throw ValidationException::withMessages(['entity_id' => 'The offline service does not exist on the server.']);
        }

        $currency = strtoupper((string) ($payload['currency'] ?? ''));
        if ($currency === '' || $currency !== $this->businessCurrency($mutation->business_id)) {
            throw ValidationException::withMessages(['currency' => 'Offline catalogue items must use the active business currency.']);
        }

        $attributes = [
            'business_id' => $mutation->business_id,
            'name' => $this->requiredString($payload['name'] ?? null, 'name', 255),
            'capability_type' => $this->requiredEnum($payload['capability_type'] ?? null, ['service', 'rental', 'product', 'package', 'other'], 'capability_type'),
            'pricing_basis' => $this->requiredEnum($payload['pricing_basis'] ?? null, ['custom', 'fixed', 'per_unit', 'per_person', 'per_hour', 'per_day'], 'pricing_basis'),
            'default_price' => $payload['default_price'] === null || $payload['default_price'] === '' ? null : $this->nonNegativeMoney($payload['default_price']),
            'currency' => $currency,
            'default_unit' => $this->nullableString($payload['default_unit'] ?? null, 50),
            'description' => $this->nullableString($payload['description'] ?? null, 5000),
            'category' => $this->requiredString($payload['category'] ?? null, 'category', 100),
            'is_active' => (bool) ($payload['active'] ?? true),
        ];

        if ($capability instanceof BusinessCapability) {
            $capability->update($attributes);
            return $capability->fresh();
        }

        return BusinessCapability::create($attributes);
    }

    private function existing(SyncMutation $mutation): ?Model
    {
        $identity = SyncEntityIdentity::query()
            ->where('business_id', $mutation->business_id)
            ->where('entity_type', $mutation->entity_type)
            ->where('entity_uuid', $this->entityUuid($mutation))
            ->first();

        if (! $identity) {
            return null;
        }

        $record = $identity->record;
        $recordBusinessId = $record instanceof Model
            ? $this->recordBusinessId($record)
            : null;

        if (
            ! $record instanceof Model
            || ! is_numeric($recordBusinessId)
            || (int) $recordBusinessId !== (int) $mutation->business_id
        ) {
            throw ValidationException::withMessages([
                'entity_id' => 'The offline synchronization identity does not belong to this business.',
            ]);
        }

        return $record;
    }

    private function recordBusinessId(Model $record): mixed
    {
        $businessId = $record->getAttribute('business_id');

        if ($businessId !== null) {
            return $businessId;
        }

        if (method_exists($record, 'event')) {
            return $record->event()->withTrashed()->value('business_id');
        }

        return null;
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
