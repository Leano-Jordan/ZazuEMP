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
use App\Support\Offline\SyncMutationPushProcessor;
use App\Support\Offline\SyncMutationProtocol;
use App\Support\Offline\SyncMutationRecorder;
use App\Support\Offline\SyncConflictRecorder;
use App\Support\Offline\SyncConflictResolutionService;
use App\Support\Offline\SyncConflictPresenter;
use App\Support\Offline\SyncDeviceAccessGuard;
use App\Support\Offline\SyncBootstrapPayloadBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class SyncController extends Controller
{
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

        $requestedSelection = $validated['selection'] ?? [];
        $selection = $requestedSelection
            ? $this->validateSelection($business, $requestedSelection)
            : app(SyncBootstrapPayloadBuilder::class)->defaultSelection($business);
        $pairingCode = (string) random_int(10000000, 99999999);
        $installationId = (string) Str::uuid();
        $expiresAt = now()->addMinutes($validated['expires_in_minutes'] ?? 10);

        SyncDevice::create([
            'business_id' => $business->id,
            'approved_by_user_id' => $request->user()->getAuthIdentifier(),
            'installation_id' => $installationId,
            'device_name' => null,
            'device_type' => 'phone',
            'status' => 'pending',
            'metadata' => [
                'pairing_code_hash' => Hash::make($pairingCode),
                'pairing_expires_at' => $expiresAt->toIso8601String(),
                'bootstrap_selection_mode' => $requestedSelection ? 'explicit' : 'recent',
                'bootstrap_selection' => $selection,
            ],
        ]);

        return response()->json([
            'pairing_code' => $pairingCode,
            'expires_at' => $expiresAt->toIso8601String(),
            'selection_count' => count($selection),
        ]);
    }

    public function registerPrimaryDevice(Request $request, CurrentBusiness $currentBusiness): JsonResponse
    {
        $validated = $request->validate([
            'installation_id' => ['required', 'uuid'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'device_type' => ['nullable', 'string', 'in:phone,tablet,desktop'],
        ]);

        $business = $currentBusiness->resolve($request->user());
        abort_unless($business, 404);

        $ownerUserId = (int) $request->user()->getAuthIdentifier();

        [$device, $token] = DB::transaction(function () use ($validated, $business, $ownerUserId): array {
            $device = SyncDevice::query()
                ->where('installation_id', $validated['installation_id'])
                ->lockForUpdate()
                ->first();

            if ($device && (int) $device->business_id !== (int) $business->id) {
                abort(409, 'This installation is already registered to another business workspace.');
            }

            $token = Str::random(64);
            $now = now();
            $metadata = $device?->metadata ?? [];
            unset($metadata['pairing_code_hash'], $metadata['pairing_expires_at']);

            if (! $device) {
                $device = SyncDevice::create([
                    'business_id' => $business->id,
                    'approved_by_user_id' => $ownerUserId,
                    'installation_id' => $validated['installation_id'],
                    'device_name' => $validated['device_name'] ?? 'Zazu device',
                    'device_type' => $validated['device_type'] ?? 'phone',
                    'status' => 'active',
                    'last_seen_at' => $now,
                    'token_expires_at' => $now->copy()->addDays(SyncDevice::SYNC_TOKEN_IDLE_EXPIRY_DAYS),
                    'metadata' => array_merge($metadata, ['sync_token_hash' => hash('sha256', $token), 'registered_at' => $now->toIso8601String()]),
                ]);
            } else {
                $device->forceFill([
                    'approved_by_user_id' => $ownerUserId,
                    'device_name' => $validated['device_name'] ?? $device->device_name ?? 'Zazu device',
                    'device_type' => $validated['device_type'] ?? $device->device_type ?? 'phone',
                    'status' => 'active',
                    'revoked_at' => null,
                    'last_seen_at' => $now,
                    'token_expires_at' => $now->copy()->addDays(SyncDevice::SYNC_TOKEN_IDLE_EXPIRY_DAYS),
                    'metadata' => array_merge($metadata, ['sync_token_hash' => hash('sha256', $token), 'registered_at' => $now->toIso8601String()]),
                ])->save();
            }

            return [$device->fresh(), $token];
        });

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

    public function provision(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pairing_code' => ['required', 'digits:8'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'device_type' => ['nullable', 'string', 'max:32'],
        ]);

        [$device, $token] = DB::transaction(function () use ($validated): array {
            $candidates = SyncDevice::query()
                ->where('status', 'pending')
                ->whereNull('revoked_at')
                ->whereNotNull('metadata->pairing_code_hash')
                ->where('metadata->pairing_expires_at', '>', now()->toIso8601String())
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($candidates as $candidate) {
                $metadata = $candidate->metadata ?? [];
                $expiresAt = isset($metadata['pairing_expires_at'])
                    ? now()->parse($metadata['pairing_expires_at'])
                    : null;

                if (! $expiresAt || ! $expiresAt->isFuture() || ! isset($metadata['pairing_code_hash'])) {
                    continue;
                }

                if (! Hash::check($validated['pairing_code'], $metadata['pairing_code_hash'])) {
                    continue;
                }

                $token = Str::random(64);
                $pairedAt = now();

                $candidate->update([
                    'device_name' => $validated['device_name'] ?? 'Zazu phone',
                    'device_type' => $validated['device_type'] ?? 'phone',
                    'status' => 'active',
                    'last_seen_at' => $pairedAt,
                    'token_expires_at' => $pairedAt->copy()->addDays(SyncDevice::SYNC_TOKEN_IDLE_EXPIRY_DAYS),
                    'metadata' => array_merge($metadata, [
                        'sync_token_hash' => hash('sha256', $token),
                        'paired_at' => $pairedAt->toIso8601String(),
                        'pairing_code_hash' => null,
                        'pairing_expires_at' => null,
                    ]),
                ]);

                return [$candidate->fresh(), $token];
            }

            throw ValidationException::withMessages([
                'pairing_code' => 'The pairing code is invalid or expired.',
            ]);
        });

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

    public function revoke(Request $request): JsonResponse
    {
        /** @var SyncDevice $authenticatedDevice */
        $authenticatedDevice = $request->attributes->get('sync_device');

        return DB::transaction(function () use ($authenticatedDevice): JsonResponse {
            // A stale token must not revoke a replacement credential registered
            // for the same installation after this request passed middleware.
            $device = app(SyncDeviceAccessGuard::class)->lockActive($authenticatedDevice);

            $metadata = $device->metadata ?? [];
            unset($metadata['sync_token_hash'], $metadata['pairing_code_hash'], $metadata['pairing_expires_at']);

            $device->forceFill([
                'status' => 'revoked',
                'revoked_at' => now(),
                'token_expires_at' => null,
                'metadata' => $metadata,
            ])->save();

            return response()->json([
                'revoked' => true,
                'device_id' => (int) $device->id,
            ]);
        });
    }

    public function bootstrap(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'delivery_sequences' => ['sometimes', 'array', 'max:100'],
            'delivery_sequences.*' => ['required', 'integer', 'min:1'],
        ]);
        $device = $request->attributes->get('sync_device');

        return response()->json($this->bootstrapPayload(
            $device,
            array_values(array_unique(array_map('intval', $validated['delivery_sequences'] ?? []))),
        ));
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
        return DB::transaction(function () use ($request): JsonResponse {
            $device = $request->attributes->get('sync_device');
            app(SyncDeviceAccessGuard::class)->lockActive($device);
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

            $presenter = app(SyncConflictPresenter::class);

            return response()->json([
                'conflicts' => $query->limit(100)->get()->map(fn (SyncConflict $conflict) => $presenter->payload($conflict))->values(),
            ]);
        });
    }

    public function resolveConflict(Request $request, SyncConflict $conflict, ?SyncConflictResolutionService $resolver = null): JsonResponse
    {
        $device = $request->attributes->get('sync_device');
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:retry,discard'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        return ($resolver ?? app(SyncConflictResolutionService::class))->resolve($device, $conflict, $validated);
    }

    public function uploadAttachment(Request $request): JsonResponse
    {
        $storedPath = null;

        try {
            return DB::transaction(function () use ($request, &$storedPath): JsonResponse {
                $device = $request->attributes->get('sync_device');
                app(SyncDeviceAccessGuard::class)->lockActive($device);

                $validated = $request->validate([
                    'event_id' => ['required', 'integer', 'min:1'],
                    'idempotency_key' => ['required', 'uuid'],
                    'description' => ['nullable', 'string', 'max:255'],
                    'file' => ['required', 'file', 'max:20480', 'mimetypes:image/jpeg,image/png,image/webp,application/pdf,text/plain,text/csv,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
                ]);

                // Resolve the target event before checking the idempotency key. A
                // reused key must never make an attachment appear to belong to another
                // job, even when the caller is otherwise authorized for this business.
                $event = Event::query()
                    ->where('business_id', $device->business_id)
                    ->findOrFail($validated['event_id']);

                $existing = EventAttachment::query()
                    ->where('business_id', $device->business_id)
                    ->where('idempotency_key', $validated['idempotency_key'])
                    ->first();

                if ($existing) {
                    return $this->existingAttachmentResponse($existing, $event, $request, $validated);
                }

                $path = $this->storePrivateAttachment($request->file('file'), (int) $event->id);
                $storedPath = $path;

                // The business-row lock above serializes the idempotency lookup
                // and insert across trusted-device uploads for this workspace.
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

                $device->update(['last_seen_at' => now()]);

                return response()->json([
                    'attachment' => $this->attachmentPayload($attachment),
                    'duplicate' => false,
                ], 201);
            });
        } catch (\Throwable $exception) {
            // A storage write cannot roll back with the database. Clean it up if
            // the transaction itself fails after the file was persisted.
            if ($storedPath !== null) {
                Storage::disk('private')->delete($storedPath);
            }

            throw $exception;
        }
    }

    /**
     * Verify an idempotent retry without accepting a reused key for different data.
     *
     * @param array<string, mixed> $validated
     */
    private function existingAttachmentResponse(
        EventAttachment $existing,
        Event $event,
        Request $request,
        array $validated
    ): JsonResponse {
        $samePayload = $this->existingAttachmentPayloadMatches($existing, $event, $request, $validated);

        if ($samePayload === null) {
            return response()->json([
                'message' => 'The previous attachment could not be verified. Keep this upload queued and contact the workspace owner.',
            ], 503);
        }

        if (! $samePayload) {
            return response()->json([
                'message' => 'This idempotency key is already assigned to a different attachment. Keep the local file and create a new upload attempt.',
            ], 409);
        }

        return response()->json([
            'attachment' => $this->attachmentPayload($existing),
            'duplicate' => true,
        ]);
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function existingAttachmentPayloadMatches(
        EventAttachment $existing,
        Event $event,
        Request $request,
        array $validated
    ): ?bool {
        $incomingFile = $request->file('file');

        if (
            (int) $existing->event_id !== (int) $event->id
            || $existing->original_name !== $incomingFile->getClientOriginalName()
            || (int) $existing->size !== (int) $incomingFile->getSize()
            || $existing->mime_type !== $incomingFile->getMimeType()
            || $existing->description !== ($validated['description'] ?? null)
        ) {
            return false;
        }

        return $this->attachmentFileContentMatches($existing, $incomingFile->getRealPath());
    }

    private function attachmentFileContentMatches(EventAttachment $existing, string|false $incomingPath): ?bool
    {
        if ($incomingPath === false) {
            return null;
        }

        $incomingHash = hash_file('sha256', $incomingPath);
        $existingStream = Storage::disk($existing->disk)->readStream($existing->path);

        if (! is_resource($existingStream) || $incomingHash === false) {
            if (is_resource($existingStream)) {
                fclose($existingStream);
            }

            return null;
        }

        $hashContext = hash_init('sha256');
        hash_update_stream($hashContext, $existingStream);
        fclose($existingStream);

        return hash_equals(hash_final($hashContext), $incomingHash);
    }

    private function storePrivateAttachment(UploadedFile $file, int $eventId): string
    {
        $path = $file->store('jobs/'.$eventId.'/attachments', 'private');

        abort_unless(
            is_string($path) && $path !== '',
            503,
            'Private attachment storage is unavailable. Keep the upload queued and retry.'
        );

        return $path;
    }

    public function listAttachments(Request $request): JsonResponse
    {
        return DB::transaction(function () use ($request): JsonResponse {
            $device = $request->attributes->get('sync_device');
            app(SyncDeviceAccessGuard::class)->lockActive($device);
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
        });
    }

    public function downloadAttachment(Request $request, EventAttachment $attachment)
    {
        return DB::transaction(function () use ($request, $attachment) {
            $device = $request->attributes->get('sync_device');
            app(SyncDeviceAccessGuard::class)->lockActive($device);
            abort_unless((int) $attachment->business_id === (int) $device->business_id, 404);
            abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

            return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
        });
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

    public function push(
        Request $request,
        SyncMutationRecorder $recorder,
        SyncMutationApplier $applier,
        OfflineDomainMutationHandler $handler,
        SyncConflictRecorder $conflictRecorder,
    ): JsonResponse {
        $device = $request->attributes->get('sync_device');
        $processor = new SyncMutationPushProcessor($recorder, $applier, $handler, $conflictRecorder);

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
            $result = $processor->process($device, $item);
            $recorded[] = $result;

            if ($result['status'] === 'applied') {
                $applied[] = $result['id'];
            }
        }

        $device->update(['last_seen_at' => now()]);

        return response()->json([
            'recorded' => $recorded,
            'applied' => $applied,
            'message' => 'Supported offline mutations are applied idempotently; unsupported or rejected mutations remain queued with an error for later handling.',
        ]);
    }

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
            $model = SyncBootstrapPayloadBuilder::SELECTION_TYPES[$type];
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

    private function bootstrapPayload(SyncDevice $device, array $deliverySequences = []): array
    {
        return DB::transaction(function () use ($device, $deliverySequences): array {
            $activeDevice = app(SyncDeviceAccessGuard::class)->lockActive($device);

            $activeDevice->update(['last_seen_at' => now()]);

            return app(SyncBootstrapPayloadBuilder::class)->build($activeDevice, $deliverySequences);
        });
    }


    private function rethrowUnauthorizedSyncException(\Throwable $exception): void
    {
        if (
            $exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException
            && $exception->getStatusCode() === 401
        ) {
            throw $exception;
        }
    }

}
