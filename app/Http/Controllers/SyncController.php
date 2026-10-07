<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventPreparationItem;
use App\Models\FinanceExpense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\Supplier;
use App\Models\SyncDevice;
use App\Support\CurrentBusiness;
use App\Support\Offline\OfflineDomainMutationHandler;
use App\Support\Offline\SyncEntityIdentityRegistry;
use App\Support\Offline\SyncMutationApplier;
use App\Support\Offline\SyncMutationProtocol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

    public function push(Request $request, \App\Support\Offline\SyncMutationRecorder $recorder, SyncMutationApplier $applier, OfflineDomainMutationHandler $handler): JsonResponse
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

            try {
                $mutation = $applier->apply($mutation, [$mutation->entity_type => $handler]);
                $applied[] = $mutation->mutation_id;
            } catch (\Throwable $exception) {
                $mutation->update([
                    'attempts' => $mutation->attempts + 1,
                    'last_error' => mb_substr($exception->getMessage(), 0, 1000),
                ]);
            }

            $recorded[] = [
                'id' => $mutation->mutation_id,
                'sequence' => $mutation->sequence,
                'status' => $mutation->status,
                'applied' => $mutation->status === 'applied',
                'error' => $mutation->status === 'pending' ? $mutation->last_error : null,
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
        $byEntityId = [];
        foreach ($mutations as $index => $mutation) {
            $byEntityId[$mutation['entity_id']] = $index;
        }

        $depth = [];
        foreach (array_keys($mutations) as $index) {
            $depth[$index] = $this->dependencyDepth($index, $mutations, $byEntityId, $depth);
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
     * @param array<string, int> $byEntityId
     * @param array<int, int> $depth
     */
    private function dependencyDepth(int $index, array $mutations, array $byEntityId, array &$depth, array $trail = []): int
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
            if (isset($byEntityId[$localId])) {
                $maxDepth = max(
                    $maxDepth,
                    $this->dependencyDepth($byEntityId[$localId], $mutations, $byEntityId, $depth, $trail) + 1,
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
            'selected' => $selected,
            'device' => [
                'id' => $device->id,
                'installation_id' => $device->installation_id,
            ],
        ];
    }
}
