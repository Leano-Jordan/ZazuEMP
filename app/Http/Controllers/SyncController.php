<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Models\Customer;
use App\Models\Event;
use App\Models\Quote;
use App\Models\SyncDevice;
use App\Support\CurrentBusiness;
use App\Support\Offline\SyncEntityIdentityRegistry;
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
    ];

    public function createPairing(Request $request, CurrentBusiness $currentBusiness): JsonResponse
    {
        $business = $currentBusiness->resolve($request->user());

        abort_unless($business, 404);

        $validated = $request->validate([
            'expires_in_minutes' => ['nullable', 'integer', 'min:5', 'max:30'],
            'selection' => ['nullable', 'array', 'max:100'],
            'selection.*.type' => ['required', 'string', 'in:customer,job,quote,service'],
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
        /** @var SyncDevice $device */
        $device = $request->attributes->get('sync_device');
        $device->update(['last_seen_at' => now()]);

        return response()->json($this->bootstrapPayload($device));
    }

    public function pull(Request $request, SyncMutationProtocol $protocol): JsonResponse
    {
        /** @var SyncDevice $device */
        $device = $request->attributes->get('sync_device');
        $limit = (int) $request->integer('limit', 100);

        $mutations = $protocol->pull($device, (string) $request->input('stream', 'business'), $limit);

        $device->update(['last_seen_at' => now()]);

        return response()->json([
            'mutations' => $mutations->values(),
        ]);
    }

    public function acknowledge(Request $request, SyncMutationProtocol $protocol): JsonResponse
    {
        /** @var SyncDevice $device */
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

        return response()->json([
            'cursor' => $cursor,
        ]);
    }

    public function push(Request $request, \App\Support\Offline\SyncMutationRecorder $recorder): JsonResponse
    {
        /** @var SyncDevice $device */
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
        foreach ($validated['mutations'] as $item) {
            $mutation = $recorder->record(
                $device,
                $item['entity_type'],
                $item['entity_id'],
                $item['operation'],
                $item['payload'] ?? [],
                $item['id'],
            );
            $recorded[] = [
                'id' => $mutation->mutation_id,
                'sequence' => $mutation->sequence,
                'status' => $mutation->status,
            ];
        }

        $device->update(['last_seen_at' => now()]);

        return response()->json([
            'recorded' => $recorded,
            'applied' => false,
            'message' => 'Mutations are durably recorded for domain-safe application; the phone must retain them until application acknowledgement is added.',
        ]);
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

            $record = $query->where('id', $item['id'])->first();

            if (! $record) {
                throw ValidationException::withMessages([
                    'selection' => "Selected {$type} record {$item['id']} does not belong to the active business.",
                ]);
            }

            $result[] = ['type' => $type, 'id' => (int) $record->id];
        }

        return $result;
    }

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

            $record = $model::query()
                ->where('id', $item['id'])
                ->where('business_id', $business->id)
                ->first();

            if (! $record) {
                continue;
            }

            $identity = $registry->identify($record, $item['type']);

            $selected[] = [
                'type' => $item['type'],
                'local_id' => $identity->entity_uuid,
                'server_id' => $record->id,
                'record' => $record->toArray(),
            ];
        }

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
            'selected' => $selected,
            'device' => [
                'id' => $device->id,
                'installation_id' => $device->installation_id,
            ],
        ];
    }
}
