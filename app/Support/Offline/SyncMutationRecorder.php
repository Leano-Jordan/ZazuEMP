<?php

namespace App\Support\Offline;

use App\Models\SyncDelivery;
use App\Models\SyncDevice;
use App\Models\SyncMutation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\SyncStream;
use Illuminate\Validation\ValidationException;
use LogicException;

class SyncMutationRecorder
{
    public function record(
        SyncDevice $device,
        string $entityType,
        string $entityId,
        string $operation,
        array $payload = [],
        ?string $mutationId = null,
        string $stream = 'business',
    ): SyncMutation {
        if (! $device->isActive()) {
            throw new LogicException('Cannot record a mutation for an inactive sync device.');
        }

        $this->assertSupportedStream($stream);

        $mutationId ??= (string) Str::uuid();

        return DB::transaction(function () use ($device, $stream, $mutationId, $entityType, $entityId, $operation, $payload): SyncMutation {
            // Serialize duplicate replay checks with the same business lock used
            // by domain application so two tabs/devices cannot create the same
            // mutation concurrently and turn an idempotent retry into a 500.
            DB::table('businesses')->where('id', $device->business_id)->lockForUpdate()->first();

            // The middleware's model is only an authentication snapshot. Re-check
            // the durable device row under the same transaction as mutation creation
            // so a concurrent revocation and this write have a deterministic order.
            $lockedDevice = SyncDevice::query()
                ->where('business_id', $device->business_id)
                ->whereKey($device->getKey())
                ->active()
                ->lockForUpdate()
                ->first();

            $expectedTokenHash = (string) (($device->metadata ?? [])['sync_token_hash'] ?? '');
            $currentTokenHash = (string) (($lockedDevice?->metadata ?? [])['sync_token_hash'] ?? '');

            $sameCredential = $expectedTokenHash === ''
                ? $currentTokenHash === ''
                : hash_equals($expectedTokenHash, $currentTokenHash);

            abort_unless(
                $lockedDevice && $sameCredential,
                401,
                'This sync device was revoked or replaced before the offline change was recorded.'
            );

            $existing = SyncMutation::query()
                ->where('mutation_id', $mutationId)
                ->first();

            if ($existing) {
                $this->assertMutationOwner($existing, $device);
                abort_unless(
                    $this->sameMutationIdentity($existing, $stream, $entityType, $entityId, $operation, $payload),
                    409,
                    'This mutation identifier was already used for a different offline change. Keep the original change and create a new mutation.'
                );

                return $existing;
            }

            $counter = SyncStream::query()
                ->where('business_id', $device->business_id)
                ->where('stream', $stream)
                ->lockForUpdate()
                ->first();

            if (! $counter) {
                $counter = SyncStream::create([
                    'business_id' => $device->business_id,
                    'stream' => $stream,
                    'next_sequence' => 1,
                ]);
            }

            $sequence = $counter->next_sequence;
            $counter->increment('next_sequence');

            $mutation = SyncMutation::create([
                'business_id' => $device->business_id,
                'sync_device_id' => $device->id,
                'stream' => $stream,
                'sequence' => $sequence,
                'mutation_id' => $mutationId,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'operation' => $operation,
                'status' => 'pending',
                'payload' => $payload,
                'occurred_at' => now(),
            ]);

            return $mutation;
        });
    }

    /**
     * Sort associative payload keys recursively while preserving list order.
     *
     * @param array<mixed> $payload
     * @return array<mixed>
     */
    private function canonicalizePayload(array $payload): array
    {
        if (! array_is_list($payload)) {
            ksort($payload);
        }

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = $this->canonicalizePayload($value);
            }
        }

        return $payload;
    }

    private function assertMutationOwner(SyncMutation $existing, SyncDevice $device): void
    {
        if (
            (int) $existing->business_id !== (int) $device->business_id
            || (int) $existing->sync_device_id !== (int) $device->id
        ) {
            throw ValidationException::withMessages([
                'mutation_id' => 'The mutation identifier is already owned by another business or device.',
            ]);
        }
    }

    /**
     * @param array<mixed> $payload
     */
    private function sameMutationIdentity(
        SyncMutation $existing,
        string $stream,
        string $entityType,
        string $entityId,
        string $operation,
        array $payload
    ): bool {
        return $existing->stream === $stream
            && $existing->entity_type === $entityType
            && $existing->entity_id === $entityId
            && $existing->operation === $operation
            && $this->canonicalizePayload($existing->payload ?? []) === $this->canonicalizePayload($payload);
    }

    public function publish(SyncMutation $mutation): void
    {
        if (! $mutation->exists || $mutation->status !== 'applied') {
            throw new LogicException('Only an applied sync mutation can be published.');
        }

        $this->assertSupportedStream($mutation->stream);

        DB::transaction(function () use ($mutation): void {
            $this->queueDeliveries($mutation);
        });
    }

    private function queueDeliveries(SyncMutation $mutation): void
    {
        SyncDevice::query()
            ->where('business_id', $mutation->business_id)
            ->where('id', '!=', $mutation->sync_device_id)
            ->where('status', 'active')
            ->whereNull('revoked_at')
            ->orderBy('id')
            ->get()
            ->each(function (SyncDevice $destination) use ($mutation): void {
                // Re-check active state after acquiring the row lock. A device
                // may have been revoked after the initial destination scan.
                $lockedDestination = SyncDevice::query()
                    ->where('business_id', $mutation->business_id)
                    ->whereKey($destination->id)
                    ->active()
                    ->lockForUpdate()
                    ->first();

                if (! $lockedDestination) {
                    return;
                }

                $existing = SyncDelivery::query()
                    ->where('sync_mutation_id', $mutation->id)
                    ->where('destination_device_id', $destination->id)
                    ->first();

                if ($existing) {
                    return;
                }

                $next = SyncDelivery::query()
                    ->where('destination_device_id', $destination->id)
                    ->where('stream', $mutation->stream)
                    ->lockForUpdate()
                    ->max('delivery_sequence');

                SyncDelivery::create([
                    'business_id' => $mutation->business_id,
                    'sync_mutation_id' => $mutation->id,
                    'destination_device_id' => $destination->id,
                    'stream' => $mutation->stream,
                    'delivery_sequence' => ((int) $next) + 1,
                    'status' => 'pending',
                ]);
            });
    }
    private function assertSupportedStream(string $stream): void
    {
        if (! in_array($stream, SyncMutationProtocol::SUPPORTED_STREAMS, true)) {
            throw ValidationException::withMessages([
                'stream' => 'The requested sync stream is not supported.',
            ]);
        }
    }

}

