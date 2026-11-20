<?php

namespace App\Support\Offline;

use App\Models\SyncConflict;

final class SyncConflictPresenter
{
    private function conflictMessage(SyncConflict $conflict): string
    {
        return match ($conflict->conflict_type) {
            'stale_record_version' => ($conflict->remote_payload['deleted'] ?? false)
                ? 'This record was deleted on the server after this device last synced. Use the server version to remove the local copy.'
                : 'This record changed on the server after this device last synced. Compare both versions before choosing which one to keep.',
            'mutation_identity_mismatch' => 'This queued change was replayed with different content. Zazu will preserve it under a new mutation ID if you retry.',
            'domain_rejection' => 'The server rejected this saved change because it no longer satisfies the current business rules.',
            default => 'This saved change needs review before it can sync.',
        };
    }

    /**
     * @return array<int, array{field:string,local:string,remote:string}>
     */
    private function comparison(SyncConflict $conflict): array
    {
        $localRecord = $this->recordFromPayload($conflict->local_payload ?? []);
        $remoteRecord = $this->recordFromPayload($conflict->remote_payload ?? []);

        return array_slice([
            ...$this->compareScalarFields($localRecord, $remoteRecord),
            ...$this->compareCollectionCounts($localRecord, $remoteRecord),
        ], 0, 12);
    }

    /**
     * @param array<string, mixed> $localRecord
     * @param array<string, mixed> $remoteRecord
     * @return array<int, array{field:string,local:string,remote:string}>
     */
    private function compareScalarFields(array $localRecord, array $remoteRecord): array
    {
        $fields = [
            'name' => 'Name', 'description' => 'Description', 'status' => 'Status',
            'category' => 'Category', 'quantity' => 'Quantity', 'unit' => 'Unit',
            'contact_name' => 'Contact', 'phone' => 'Phone', 'email' => 'Email',
            'event_type' => 'Work type', 'reference' => 'Reference', 'notes' => 'Notes',
            'amount' => 'Amount', 'total_amount' => 'Total', 'projected_amount' => 'Projected cost',
            'actual_amount' => 'Actual cost', 'sku' => 'SKU', 'asset_tag' => 'Asset tag',
            'location' => 'Location', 'condition' => 'Condition', 'currency' => 'Currency',
            'paid_amount' => 'Paid', 'balance' => 'Balance', 'expected_at' => 'Expected date',
            'allocated_from' => 'Allocation start', 'allocated_until' => 'Allocation end',
        ];

        $rows = [];
        foreach ($fields as $key => $label) {
            $local = $localRecord[$key] ?? null;
            $remote = $remoteRecord[$key] ?? null;
            if ($local === $remote || (is_scalar($local) && is_scalar($remote) && (string) $local === (string) $remote)) {
                continue;
            }

            $rows[] = [
                'field' => $label,
                'local' => $this->comparisonValue($local),
                'remote' => $this->comparisonValue($remote),
            ];
        }

        return $rows;
    }

    /**
     * @param array<string, mixed> $localRecord
     * @param array<string, mixed> $remoteRecord
     * @return array<int, array{field:string,local:string,remote:string}>
     */
    private function compareCollectionCounts(array $localRecord, array $remoteRecord): array
    {
        $collections = [
            'contacts' => 'Contacts', 'requirements' => 'Requirements', 'items' => 'Line items',
            'payments' => 'Payments', 'movements' => 'Movements', 'allocations' => 'Allocations',
        ];
        $rows = [];

        foreach ($collections as $key => $label) {
            $local = $localRecord[$key] ?? null;
            $remote = $remoteRecord[$key] ?? null;
            if (! is_array($local) || ! is_array($remote) || count($local) === count($remote)) {
                continue;
            }

            $rows[] = [
                'field' => $label,
                'local' => (string) count($local),
                'remote' => (string) count($remote),
            ];
        }

        return $rows;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function recordFromPayload(array $payload): array
    {
        if (is_array($payload['record'] ?? null)) {
            return $payload['record'];
        }

        if (is_array($payload['payload']['record'] ?? null)) {
            return $payload['payload']['record'];
        }

        return [];
    }

    private function comparisonValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        return is_scalar($value) ? (string) $value : 'Changed';
    }

    public function payload(SyncConflict $conflict): array
    {
        return [
            'id' => $conflict->id,
            'mutation_id' => $conflict->mutation_id,
            'entity_type' => $conflict->entity_type,
            'entity_id' => $conflict->entity_id,
            'conflict_type' => $conflict->conflict_type,
            'message' => $this->conflictMessage($conflict),
            'comparison' => $this->comparison($conflict),
            'status' => $conflict->status,
            'local_payload' => $conflict->local_payload,
            'remote_payload' => $conflict->remote_payload,
            'resolution_note' => $conflict->resolution_note,
            'resolved_at' => $conflict->resolved_at?->toIso8601String(),
            'created_at' => $conflict->created_at?->toIso8601String(),
        ];
    }

}
