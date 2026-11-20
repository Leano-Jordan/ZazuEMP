<?php

namespace App\Support\Offline;

use App\Models\Asset;
use App\Models\SyncDevice;
use App\Models\SyncEntityIdentity;
use App\Models\SyncMutation;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

final class SyncMutationPushProcessor
{
    public function __construct(
        private readonly SyncMutationRecorder $recorder,
        private readonly SyncMutationApplier $applier,
        private readonly OfflineDomainMutationHandler $handler,
        private readonly SyncConflictRecorder $conflictRecorder,
    ) {
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    public function process(SyncDevice $device, array $item): array
    {
        try {
            $mutation = $this->recorder->record(
                $device,
                $item['entity_type'],
                $item['entity_id'],
                $item['operation'],
                $item['payload'] ?? [],
                $item['id'],
            );
        } catch (HttpException $exception) {
            if ($exception->getStatusCode() !== 409) {
                throw $exception;
            }

            return $this->mutationIdentityMismatchResponse($device, $item, $exception->getMessage());
        }

        return $this->applyRecordedMutation($device, $mutation);
    }

    /**
     * @return array<string, mixed>
     */
    private function applyRecordedMutation(SyncDevice $device, SyncMutation $mutation): array
    {
        $conflictId = null;
        $conflictType = null;
        $identity = null;
        $retryable = false;

        try {
            $mutation = $this->applier->apply($mutation, [$mutation->entity_type => $this->handler]);
            $identity = SyncEntityIdentity::query()
                ->where('business_id', $mutation->business_id)
                ->where('entity_type', $mutation->entity_type)
                ->where('entity_uuid', $mutation->entity_id)
                ->first();
        } catch (Throwable $exception) {
            if ($exception instanceof HttpException && $exception->getStatusCode() === 401) {
                throw $exception;
            }

            $error = mb_substr($exception->getMessage(), 0, 1000);
            $retryable = ! ($exception instanceof ValidationException);
            $mutation->update([
                'attempts' => $mutation->attempts + 1,
                'last_error' => $error,
            ]);

            $conflict = $this->recordDomainConflict($device, $mutation, $exception, $error);
            $conflictId = $conflict['id'];
            $conflictType = $conflict['type'];
            $retryable = $conflict['retryable'] ?? $retryable;
        }

        return [
            'id' => $mutation->mutation_id,
            'sequence' => $mutation->sequence,
            'status' => $mutation->status,
            'applied' => $mutation->status === 'applied',
            'error' => $mutation->status === 'pending' ? $mutation->last_error : null,
            'conflict_id' => $conflictId,
            'conflict_type' => $conflictType,
            'retryable' => $retryable,
            'identity' => $identity ? [
                'entity_type' => $identity->entity_type,
                'local_id' => $identity->entity_uuid,
                'server_id' => (int) $identity->record_id,
                'revision' => (int) $identity->revision,
            ] : null,
            'related_identity' => $mutation->status === 'applied'
                ? $this->relatedIdentityForMutation($mutation)
                : null,
        ];
    }

    /**
     * @return array{id:int|null,type:string|null,retryable?:bool}
     */
    private function recordDomainConflict(
        SyncDevice $device,
        SyncMutation $mutation,
        Throwable $exception,
        string $error,
    ): array {
        if ($exception instanceof StaleSyncRevisionException) {
            $conflict = $this->conflictRecorder->recordOnce(
                $device,
                $mutation->entity_type,
                $mutation->entity_id,
                'stale_record_version',
                $mutation->mutation_id,
                $mutation->payload,
                $exception->remotePayload(),
            );

            return ['id' => $conflict->id, 'type' => 'stale_record_version', 'retryable' => false];
        }

        if ($exception instanceof ValidationException) {
            $conflict = $this->conflictRecorder->recordOnce(
                $device,
                $mutation->entity_type,
                $mutation->entity_id,
                'domain_rejection',
                $mutation->mutation_id,
                $mutation->payload,
                ['message' => $error],
            );

            return ['id' => $conflict->id, 'type' => 'domain_rejection'];
        }

        return ['id' => null, 'type' => null];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function relatedIdentityForMutation(SyncMutation $mutation): ?array
    {
        $payload = $mutation->payload['record'] ?? [];
        $parent = match ($mutation->entity_type) {
            'quote_acceptance' => ['quote', $payload['quote_local_id'] ?? null],
            'payment' => ['invoice', $payload['invoice_local_id'] ?? null],
            'purchase_receipt' => ['purchase_order', $payload['purchase_order_local_id'] ?? null],
            'inventory_movement' => ['inventory_item', $payload['inventory_item_local_id'] ?? null],
            'asset_allocation' => ['asset', $payload['asset_local_id'] ?? null],
            default => null,
        };

        if (! $parent) {
            return null;
        }

        if ($parent[0] === 'asset' && (! is_string($parent[1]) || ! Str::isUuid($parent[1]))) {
            $allocationIdentity = SyncEntityIdentity::query()
                ->where('business_id', $mutation->business_id)
                ->where('entity_type', 'asset_allocation')
                ->where('entity_uuid', $mutation->entity_id)
                ->first();
            $allocation = $allocationIdentity?->record;
            $assetId = $allocation instanceof \App\Models\AssetAllocation ? $allocation->asset_id : null;
            $identity = is_numeric($assetId)
                ? SyncEntityIdentity::query()
                    ->where('business_id', $mutation->business_id)
                    ->where('entity_type', 'asset')
                    ->where('record_type', (new Asset)->getMorphClass())
                    ->where('record_id', (int) $assetId)
                    ->first()
                : null;
        } else {
            if (! is_string($parent[1]) || ! Str::isUuid($parent[1])) {
                return null;
            }

            $identity = SyncEntityIdentity::query()
                ->where('business_id', $mutation->business_id)
                ->where('entity_type', $parent[0])
                ->where('entity_uuid', Str::lower($parent[1]))
                ->first();
        }

        if (! $identity) {
            return null;
        }

        return [
            'entity_type' => $identity->entity_type,
            'local_id' => $identity->entity_uuid,
            'server_id' => (int) $identity->record_id,
            'revision' => (int) $identity->revision,
        ];
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function mutationIdentityMismatchResponse(
        SyncDevice $device,
        array $item,
        string $message,
    ): array {
        $existing = SyncMutation::query()
            ->where('business_id', $device->business_id)
            ->where('sync_device_id', $device->id)
            ->where('mutation_id', $item['id'])
            ->firstOrFail();

        $conflict = $this->conflictRecorder->recordOnce(
            $device,
            $item['entity_type'],
            $item['entity_id'],
            'mutation_identity_mismatch',
            $existing->mutation_id,
            [
                'entity_type' => $item['entity_type'],
                'entity_id' => $item['entity_id'],
                'operation' => $item['operation'],
                'payload' => $item['payload'] ?? [],
            ],
            [
                'entity_type' => $existing->entity_type,
                'entity_id' => $existing->entity_id,
                'operation' => $existing->operation,
                'payload' => $existing->payload ?? [],
            ],
        );

        return [
            'id' => $existing->mutation_id,
            'sequence' => $existing->sequence,
            'status' => 'pending',
            'applied' => false,
            'error' => mb_substr($message, 0, 1000),
            'conflict_id' => $conflict->id,
            'conflict_type' => 'mutation_identity_mismatch',
            'retryable' => false,
            'identity' => null,
            'related_identity' => null,
        ];
    }
}
