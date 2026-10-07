<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventPreparationItem;
use App\Models\EventAttachment;
use App\Models\FinanceExpense;
use App\Models\Asset;
use App\Models\EventCost;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\Supplier;
use App\Models\SyncConflict;
use App\Models\SyncEntityIdentity;
use App\Models\SyncDevice;
use App\Models\SyncMutation;
use App\Support\CurrentBusiness;
use App\Support\Offline\OfflineDomainMutationHandler;
use App\Support\Offline\SyncEntityIdentityRegistry;
use App\Support\Offline\SyncMutationApplier;
use App\Support\Offline\SyncMutationProtocol;
use App\Support\Offline\SyncConflictRecorder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class SyncController extends Controller
{
    private const SELECTION_TYPES = [
        'customer' => Customer::class,
        'job' => Event::class,
        'quote' => Quote::class,
        'service' => BusinessCapability::class,
        'preparation' => EventPreparationItem::class,
        'supplier' => Supplier::class,
        'purchase_order' => PurchaseOrder::class,
    ];

    public function createPairing(Request $request, CurrentBusiness $currentBusiness): JsonResponse
    {
        $business = $currentBusiness->resolve($request->user());
        abort_unless($business, 404);

        $validated = $request->validate([
            'expires_in_minutes' => ['nullable', 'integer', 'min:5', 'max:30'],
            'selection' => ['nullable', 'array', 'max:100'],
            'selection.*.type' => ['required', 'string', 'in:customer,job,quote,service,preparation,supplier,purchase_order'],
            'selection.*.id' => ['required', 'integer', 'min:1'],
        ]);

        $selection = $this->validateSelection($business, $validated['selection'] ?? []);
        $pairingCode = (string) random_int(100000, 999999);
        $installationId = (string) Str::uuid();
        $expiresAt = now()->addMinutes($validated['expires_in_minutes'] ?? 10);

        SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => $installationId,
            'device_name' => null,
            'device_type' => 'phone',
            'status' => 'pending',
            'metadata' => [
                'pairing_code_hash' => Hash::make($pairingCode),
                'pairing_expires_at' => $expiresAt->toIso8601String(),
                'bootstrap_selection' => $selection,
            ],
        ]);

        return response()->json([
            'pairing_code' => $pairingCode,
            'expires_at' => $expiresAt->toIso8601String(),
            'selection_count' => count($selection),
        ]);
    }

    public function provision(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pairing_code' => ['required', 'digits:6'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'device_type' => ['nullable', 'string', 'max:32'],
        ]);

        $device = SyncDevice::query()->where('status', 'pending')->orderBy('id')->get()->first(function (SyncDevice $candidate): bool {
            $metadata = $candidate->metadata ?? [];
            $expiresAt = isset($metadata['pairing_expires_at']) ? now()->parse($metadata['pairing_expires_at']) : null;

            return $expiresAt && $expiresAt->isFuture()
                && isset($metadata['pairing_code_hash'])
                && Hash::check(request()->input('pairing_code'), $metadata['pairing_code_hash']);
        });

        if (! $device) {
            throw ValidationException::withMessages(['pairing_code' => 'The pairing code is invalid or expired.']);
        }

        $metadata = $device->metadata ?? [];
        $token = Str::random(64);

        $device->update([
            'device_name' => $validated['device_name'] ?? 'Zazu phone',
            'device_type' => $validated['device_type'] ?? 'phone',
            'status' => 'active',
            'last_seen_at' => now(),
            'metadata' => array_merge($metadata, [
                'sync_token_hash' => hash('sha256', $token),
                'paired_at' => now()->toIso8601String(),
                'pairing_code_hash' => null,
                'pairing_expires_at' => null,
            ]),
        ]);

        return response()->json([
            'device' => [
                'id' => $device->id,
                'installation_id' => $device->installation_id,
                'business_id' => $device->business_id,
            ],
            'token' => $token,
            'bootstrap' => $this->bootstrapPayload($device),
        ]);
    }

    public function bootstrap(Request $request): JsonResponse
    {
        $device = $request->attributes->get('sync_device');
        $device->update(['last_seen_at' => now()]);

        return response()->json($this->bootstrapPayload($device));
    }

    public function pull(Request $request, SyncMutationProtocol $protocol): JsonResponse
    {
        $device = $request->attributes->get('sync_device');
        $limit = (int) $request->integer('limit', 100);
        $mutations = $protocol->pull($device, (string) $request->input('stream', 'business'), $limit);
        $device->update(['last_seen_at' => now()]);

        return response()->json(['mutations' => $mutations->values()]);
    }

    public function acknowledge(Request $request, SyncMutationProtocol $protocol): JsonResponse
    {
        $device = $request->attributes->get('sync_device');
        $validated = $request->validate([
            'sequence' => ['required', 'integer', 'min:0'],
            'stream' => ['nullable', 'string', 'max:64'],
        ]);

        $cursor = $protocol->acknowledgeThrough(
            $device,
            (int) $validated['sequence'],
            (string) ($validated['stream'] ?? 'business'),
        );

        $device->update(['last_seen_at' => now()]);
        return response()->json(['cursor' => $cursor]);
    }

    public function listConflicts(Request $request): JsonResponse
    {
        $device = $request->attributes->get('sync_device');
        $status = (string) $request->query('status', 'open');

        if (! in_array($status, ['open', 'resolved', 'all'], true)) {
            throw ValidationException::withMessages(['status' => 'Unsupported conflict status.']);
        }

        $query = SyncConflict::query()
            ->where('business_id', $device->business_id)
            ->where('sync_device_id', $device->id)
            ->latest('id');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        return response()->json([
            'conflicts' => $query->limit(100)->get()->map(fn (SyncConflict $conflict) => $this->conflictPayload($conflict))->values(),
        ]);
    }

    public function resolveConflict(Request $request, SyncConflict $conflict): JsonResponse
    {
        $device = $request->attributes->get('sync_device');
        abort_unless(
            (int) $conflict->business_id === (int) $device->business_id
            && (int) $conflict->sync_device_id === (int) $device->id,
            404
        );

        $validated = $request->validate([
            'action' => ['required', 'string', 'in:retry,discard'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($conflict->status !== 'open') {
            return response()->json(['conflict' => $this->conflictPayload($conflict)]);
        }

        $mutation = null;
        if ($conflict->mutation_id) {
            $mutation = SyncMutation::query()
                ->where('business_id', $device->business_id)
                ->where('sync_device_id', $device->id)
                ->where('mutation_id', $conflict->mutation_id)
                ->first();
        }

        if ($validated['action'] === 'retry') {
            abort_unless($mutation, 404);
            $mutation->update([
                'status' => 'pending',
                'last_error' => null,
            ]);
        } elseif ($mutation) {
            $mutation->update([
                'status' => 'discarded',
                'last_error' => $validated['note'] ?? 'Offline change discarded after conflict resolution.',
            ]);
        }

        $conflict->update([
            'status' => 'resolved',
            'resolution_note' => $validated['note'] ?? ($validated['action'] === 'retry' ? 'Queued for retry.' : 'Discarded.'),
            'resolved_at' => now(),
        ]);

        return response()->json([
            'conflict' => $this->conflictPayload($conflict->fresh()),
            'mutation_status' => $mutation?->fresh()->status,
        ]);
    }

    private function conflictPayload(SyncConflict $conflict): array
    {
        return [
            'id' => $conflict->id,
            'mutation_id' => $conflict->mutation_id,
            'entity_type' => $conflict->entity_type,
            'entity_id' => $conflict->entity_id,
            'conflict_type' => $conflict->conflict_type,
            'status' => $conflict->status,
            'local_payload' => $conflict->local_payload,
            'remote_payload' => $conflict->remote_payload,
            'resolution_note' => $conflict->resolution_note,
            'resolved_at' => $conflict->resolved_at?->toIso8601String(),
            'created_at' => $conflict->created_at?->toIso8601String(),
        ];
    }

    public function uploadAttachment(Request $request): JsonResponse
    {
        $device = $request->attributes->get('sync_device');
        $validated = $request->validate([
            'event_id' => ['required', 'integer', 'min:1'],
            'idempotency_key' => ['required', 'uuid'],
            'description' => ['nullable', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:20480', 'mimetypes:image/jpeg,image/png,image/webp,application/pdf,text/plain,text/csv,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        ]);

        $existing = EventAttachment::query()
            ->where('business_id', $device->business_id)
            ->where('idempotency_key', $validated['idempotency_key'])
            ->first();

        if ($existing) {
            return response()->json([
                'attachment' => $this->attachmentPayload($existing),
                'duplicate' => true,
            ]);
        }

        $event = Event::query()
            ->where('business_id', $device->business_id)
            ->findOrFail($validated['event_id']);

        $path = $request->file('file')->store('jobs/'.$event->id.'/attachments', 'private');

        try {
            $attachment = EventAttachment::create([
                'event_id' => $event->id,
                'business_id' => $event->business_id,
                'uploaded_by' => null,
                'original_name' => $request->file('file')->getClientOriginalName(),
                'disk' => 'private',
                'path' => $path,
                'mime_type' => $request->file('file')->getMimeType(),
                'size' => $request->file('file')->getSize(),
                'source' => 'sync',
                'description' => $validated['description'] ?? null,
                'idempotency_key' => $validated['idempotency_key'],
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('private')->delete($path);
            throw $exception;
        }

        $device->update(['last_seen_at' => now()]);

        return response()->json([
            'attachment' => $this->attachmentPayload($attachment),
            'duplicate' => false,
        ], 201);
    }

    public function listAttachments(Request $request): JsonResponse
    {
        $device = $request->attributes->get('sync_device');
        $eventId = $request->query('event_id');

        $query = EventAttachment::query()
            ->where('business_id', $device->business_id)
            ->when($eventId, fn ($q) => $q->where('event_id', $eventId))
            ->latest('id');

        if ($eventId !== null) {
            Event::query()
                ->where('business_id', $device->business_id)
                ->findOrFail($eventId);
        }

        return response()->json([
            'attachments' => $query->get()->map(fn (EventAttachment $attachment) => $this->attachmentPayload($attachment))->values(),
        ]);
    }

    public function downloadAttachment(Request $request, EventAttachment $attachment)
    {
        $device = $request->attributes->get('sync_device');
        abort_unless((int) $attachment->business_id === (int) $device->business_id, 404);
        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }

    private function attachmentPayload(EventAttachment $attachment): array
    {
        return [
            'id' => $attachment->id,
            'event_id' => $attachment->event_id,
            'original_name' => $attachment->original_name,
            'mime_type' => $attachment->mime_type,
            'size' => $attachment->size,
            'idempotency_key' => $attachment->idempotency_key,
            'source' => $attachment->source,
            'description' => $attachment->description,
            'created_at' => $attachment->created_at?->toIso8601String(),
        ];
    }

    public function push(Request $request, \App\Support\Offline\SyncMutationRecorder $recorder, SyncMutationApplier $applier, OfflineDomainMutationHandler $handler, SyncConflictRecorder $conflictRecorder): JsonResponse
    {
        $device = $request->attributes->get('sync_device');

        $validated = $request->validate([
            'mutations' => ['required', 'array', 'min:1', 'max:100'],
            'mutations.*.id' => ['required', 'uuid'],
            'mutations.*.entity_type' => ['required', 'string', 'max:255'],
            'mutations.*.entity_id' => ['required', 'string', 'max:255'],
            'mutations.*.operation' => ['required', 'string', 'max:32'],
            'mutations.*.payload' => ['nullable', 'array'],
        ]);

        $recorded = [];
        $applied = [];

        foreach ($this->orderMutationsByDependencies($validated['mutations']) as $item) {
            $mutation = $recorder->record($device, $item['entity_type'], $item['entity_id'], $item['operation'], $item['payload'] ?? [], $item['id']);

            $conflictId = null;
            $identity = null;

            try {
                $mutation = $applier->apply($mutation, [$mutation->entity_type => $handler]);
                $applied[] = $mutation->mutation_id;

                $identity = SyncEntityIdentity::query()
                    ->where('business_id', $mutation->business_id)
                    ->where('entity_type', $mutation->entity_type)
                    ->where('entity_uuid', $mutation->entity_id)
                    ->first();

            } catch (\Throwable $exception) {
                $error = mb_substr($exception->getMessage(), 0, 1000);

                $mutation->update([
                    'attempts' => $mutation->attempts + 1,
                    'last_error' => $error,
                ]);

                if ($exception instanceof ValidationException) {
                    $conflict = SyncConflict::query()
                        ->where('business_id', $mutation->business_id)
                        ->where('mutation_id', $mutation->mutation_id)
                        ->where('status', 'open')
                        ->first();

                    if (! $conflict) {
                        $conflict = $conflictRecorder->record(
                            $mutation->business_id,
                            $mutation->entity_type,
                            $mutation->entity_id,
                            'domain_rejection',
                            $device,
                            $mutation->mutation_id,
                            $mutation->payload,
                            ['message' => $error],
                        );
                    }

                    $conflictId = $conflict->id;
                }
            }

            $recorded[] = [
                'id' => $mutation->mutation_id,
                'sequence' => $mutation->sequence,
                'status' => $mutation->status,
                'applied' => $mutation->status === 'applied',
                'error' => $mutation->status === 'pending' ? $mutation->last_error : null,
                'conflict_id' => $conflictId,
                'identity' => $identity ? [
                    'entity_type' => $identity->entity_type,
                    'local_id' => $identity->entity_uuid,
                    'server_id' => $identity->record_id,
                ] : null,
            ];
        }

        $device->update(['last_seen_at' => now()]);

        return response()->json([
            'recorded' => $recorded,
            'applied' => $applied,
            'message' => 'Supported offline mutations are applied idempotently; unsupported or rejected mutations remain queued with an error for later handling.',
        ]);
    }

    /**
     * Keep dependent offline mutations behind mutations that create their referenced local identities.
     *
     * @param array<int, array<string, mixed>> $mutations
     * @return array<int, array<string, mixed>>
     */
    private function orderMutationsByDependencies(array $mutations): array
    {
        $byLocalId = [];
        foreach ($mutations as $index => $mutation) {
            $byLocalId[$mutation['entity_id']][] = $index;

            $payloadLocalId = $mutation['payload']['local_id'] ?? null;
            if (is_string($payloadLocalId) && $payloadLocalId !== $mutation['entity_id']) {
                $byLocalId[$payloadLocalId][] = $index;
            }
        }

        $depth = [];
        foreach (array_keys($mutations) as $index) {
            $depth[$index] = $this->dependencyDepth($index, $mutations, $byLocalId, $depth);
        }

        $indexed = array_map(
            fn (int $index): array => ['index' => $index, 'mutation' => $mutations[$index]],
            array_keys($mutations),
        );

        usort($indexed, function (array $a, array $b) use ($depth): int {
            return ($depth[$a['index']] <=> $depth[$b['index']]) ?: ($a['index'] <=> $b['index']);
        });

        return array_column($indexed, 'mutation');
    }

    /**
     * @param array<int, array<string, mixed>> $mutations
     * @param array<string, array<int, int>> $byLocalId
     * @param array<int, int> $depth
     */
    private function dependencyDepth(int $index, array $mutations, array $byLocalId, array &$depth, array $trail = []): int
    {
        if (isset($depth[$index])) {
            return $depth[$index];
        }

        if (isset($trail[$index])) {
            return 0;
        }

        $trail[$index] = true;
        $maxDepth = 0;

        foreach ($this->dependencyLocalIds($mutations[$index]['payload'] ?? []) as $localId) {
            foreach ($byLocalId[$localId] ?? [] as $dependencyIndex) {
                if ($dependencyIndex === $index) {
                    continue;
                }

                $maxDepth = max(
                    $maxDepth,
                    $this->dependencyDepth($dependencyIndex, $mutations, $byLocalId, $depth, $trail) + 1,
                );
            }
        }

        return $depth[$index] = $maxDepth;
    }

    /**
     * @return array<int, string>
     */
    private function dependencyLocalIds(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $identities = [];
        foreach ($value as $key => $child) {
            if (is_string($key) && str_ends_with($key, '_local_id') && is_string($child)) {
                $identities[] = $child;
                continue;
            }

            $identities = array_merge($identities, $this->dependencyLocalIds($child));
        }

        return array_values(array_unique($identities));
    }

    private function validateSelection(Business $business, array $selection): array
    {
        $result = [];

        foreach ($selection as $item) {
            $type = $item['type'];
            $model = self::SELECTION_TYPES[$type];
            $query = $model::query();

            if ($item['type'] === 'quote') {
                $query->whereHas('event', fn ($event) => $event->where('business_id', $business->id));
            } else {
                $query->where('business_id', $business->id);
            }

            $record = $query->whereKey($item['id'])->first();

            if (! $record) {
                throw ValidationException::withMessages([
                    'selection' => "Selected {$type} record {$item['id']} does not belong to the active business.",
                ]);
            }

            $result[] = ['type' => $type, 'id' => (int) $record->id];
        }

        return $result;
    }

    /** @SuppressWarnings(PHPMD.ExcessiveMethodLength) */
    private function bootstrapPayload(SyncDevice $device): array
    {
        $business = Business::query()->findOrFail($device->business_id);
        $metadata = $device->metadata ?? [];
        $registry = app(SyncEntityIdentityRegistry::class);
        $selected = [];

        foreach ($metadata['bootstrap_selection'] ?? [] as $item) {
            $model = self::SELECTION_TYPES[$item['type']] ?? null;
            if (! $model) {
                continue;
            }

            $query = $model::query();

            if ($item['type'] === 'quote') {
                $query->whereHas('event', fn ($event) => $event->where('business_id', $business->id));
            } else {
                $query->where('business_id', $business->id);
            }

            $record = $query->whereKey($item['id'])->first();
            if (! $record) {
                continue;
            }

            $identity = $registry->identify($record, $item['type']);
            $recordData = $record->toArray();

            if ($item['type'] === 'quote') {
                $record->loadMissing('latestVersion.items');
                $recordData['latest_version'] = $record->latestVersion?->toArray();
            }

            if ($item['type'] === 'job') {
                $record->loadMissing('requirements');
                $recordData['requirements'] = $record->requirements->map(function ($requirement) use ($registry): array {
                    return [
                        'local_id' => $registry->identify($requirement, 'event_requirement')->entity_uuid,
                        'server_id' => $requirement->id,
                        'record' => $requirement->toArray(),
                    ];
                })->values()->all();
            }

            $selected[] = [
                'type' => $item['type'],
                'local_id' => $identity->entity_uuid,
                'server_id' => $record->id,
                'record' => $recordData,
            ];
        }

        $suppliers = Supplier::query()
            ->where('business_id', $business->id)
            ->orderBy('name')
            ->get()
            ->map(fn (Supplier $supplier) => [
                'local_id' => $registry->identify($supplier, 'supplier')->entity_uuid,
                'server_id' => $supplier->id,
                'record' => $supplier->toArray(),
            ])
            ->values();

        $purchaseOrders = PurchaseOrder::query()
            ->where('business_id', $business->id)
            ->with('items')
            ->latest()
            ->get()
            ->map(fn (PurchaseOrder $order) => [
                'local_id' => $registry->identify($order, 'purchase_order')->entity_uuid,
                'server_id' => $order->id,
                'record' => $order->toArray(),
                'items' => $order->items->map(fn ($item) => [
                    'local_id' => $registry->identify($item, 'purchase_order_item')->entity_uuid,
                    'server_id' => $item->id,
                    'record' => $item->toArray(),
                ])->values(),
            ])
            ->values();

        $invoices = Invoice::query()
            ->where('business_id', $business->id)
            ->with(['items', 'payments'])
            ->latest('id')
            ->limit(500)
            ->get()
            ->map(fn (Invoice $invoice) => [
                'local_id' => $registry->identify($invoice, 'invoice')->entity_uuid,
                'server_id' => $invoice->id,
                'record' => array_merge($invoice->toArray(), [
                    'paid_amount' => $invoice->paid_amount,
                    'balance' => $invoice->balance,
                    'items' => $invoice->items->map(fn ($item) => $item->toArray())->values(),
                    'payments' => $invoice->payments->map(fn (Payment $payment) => [
                        'local_id' => $registry->identify($payment, 'payment')->entity_uuid,
                        'server_id' => $payment->id,
                        'record' => $payment->toArray(),
                    ])->values(),
                ]),
            ])
            ->values();

        $inventoryItems = InventoryItem::query()
            ->where('business_id', $business->id)
            ->with('movements')
            ->orderBy('name')
            ->limit(500)
            ->get()
            ->map(fn (InventoryItem $item) => [
                'local_id' => $registry->identify($item, 'inventory_item')->entity_uuid,
                'server_id' => $item->id,
                'record' => array_merge($item->toArray(), [
                    'on_hand' => $item->on_hand,
                    'movements' => $item->movements->map(fn ($movement) => [
                        'local_id' => $registry->identify($movement, 'inventory_movement')->entity_uuid,
                        'server_id' => $movement->id,
                        'record' => $movement->toArray(),
                    ])->values()->all(),
                ]),
            ])
            ->values();

        $assets = Asset::query()
            ->where('business_id', $business->id)
            ->with(['allocations' => fn ($query) => $query->where('status', 'allocated')])
            ->orderBy('name')
            ->limit(500)
            ->get()
            ->map(fn (Asset $asset) => [
                'local_id' => $registry->identify($asset, 'asset')->entity_uuid,
                'server_id' => $asset->id,
                'record' => array_merge($asset->toArray(), [
                    'allocations' => $asset->allocations->map(fn ($allocation) => [
                        'local_id' => $registry->identify($allocation, 'asset_allocation')->entity_uuid,
                        'server_id' => $allocation->id,
                        'record' => $allocation->toArray(),
                    ])->values()->all(),
                ]),
            ])
            ->values();

        $costs = EventCost::query()
            ->where('business_id', $business->id)
            ->with('event')
            ->latest('id')
            ->limit(500)
            ->get()
            ->map(fn (EventCost $cost) => [
                'local_id' => $registry->identify($cost, 'event_cost')->entity_uuid,
                'server_id' => $cost->id,
                'record' => array_merge($cost->toArray(), [
                    'event_name' => $cost->event?->name,
                ]),
            ])
            ->values();

        $expenses = FinanceExpense::query()
            ->where('business_id', $business->id)
            ->latest('id')
            ->limit(500)
            ->get()
            ->map(fn (FinanceExpense $expense) => [
                'local_id' => $registry->identify($expense, 'expense')->entity_uuid,
                'server_id' => $expense->id,
                'record' => $expense->toArray(),
            ])
            ->values();

        return [
            'version' => 1,
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'currency' => $business->currency,
                'email' => $business->email,
                'phone' => $business->phone,
                'address' => $business->address,
                'website' => $business->website,
                'tax_number' => $business->tax_number,
            ],
            'catalogue' => BusinessCapability::query()
                ->where('business_id', $business->id)
                ->get()
                ->map(fn (BusinessCapability $capability) => [
                    'local_id' => $registry->identify($capability, 'service')->entity_uuid,
                    'server_id' => $capability->id,
                    'record' => $capability->toArray(),
                ])
                ->values(),
            'suppliers' => $suppliers,
            'purchase_orders' => $purchaseOrders,
            'invoices' => $invoices,
            'expenses' => $expenses,
            'inventory_items' => $inventoryItems,
            'assets' => $assets,
            'costs' => $costs,
            'selected' => $selected,
            'device' => [
                'id' => $device->id,
                'installation_id' => $device->installation_id,
            ],
        ];
    }
}
