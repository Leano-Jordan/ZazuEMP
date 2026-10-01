<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Customer;
use App\Support\Audit;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CustomerImportService
{
    public function __construct(
        private readonly CustomerImportMapper $mapper,
    ) {
    }

    /**
     * Import only explicitly approved, safe rows.
     *
     * The preview is rebuilt immediately before the write so matching cannot
     * become stale between preview and import. Only "new" rows may be created.
     * Existing rows may be explicitly skipped. Duplicate and needs-review rows
     * are never written by this operation.
     *
     * @param array<int, array<string, string>> $rows
     * @param array<int, string> $approvedActions Row number => create|skip
     * @return array{created: int, skipped: int}
     */
    public function import(
        Business $business,
        array $rows,
        array $approvedActions,
    ): array {
        $previewByRow = $this->previewByRow($rows, $business);

        $this->validateApprovedActions($previewByRow, $approvedActions);
        $this->validateUnapprovedRows($previewByRow, $approvedActions);

        return DB::transaction(
            fn (): array => $this->performImport($business, $rows, $approvedActions)
        );
    }

    /**
     * @param array<int, array{row_number: int}> $previewRows
     * @return array<int, array<string, mixed>>
     */
    private function previewByRow(array $rows, Business $business): array
    {
        $preview = $this->mapper->preview($rows, $business);
        $previewByRow = [];

        foreach ($preview['rows'] as $previewRow) {
            $previewByRow[$previewRow['row_number']] = $previewRow;
        }

        return $previewByRow;
    }

    /**
     * @param array<int, array<string, mixed>> $previewByRow
     * @param array<int, string> $approvedActions
     */
    private function validateApprovedActions(array $previewByRow, array $approvedActions): void
    {
        foreach ($approvedActions as $rowNumber => $action) {
            if (!isset($previewByRow[$rowNumber])) {
                throw new RuntimeException("Import row {$rowNumber} does not exist.");
            }

            if (!in_array($action, ['create', 'skip'], true)) {
                throw new RuntimeException("Unsupported import action for row {$rowNumber}.");
            }

            $row = $previewByRow[$rowNumber];

            if ($row['status'] !== 'ready') {
                throw new RuntimeException("Import row {$rowNumber} has validation errors and cannot be imported.");
            }

            if ($action === 'create' && $row['match']['status'] !== 'new') {
                throw new RuntimeException("Import row {$rowNumber} is not a new customer and cannot be created.");
            }

            if ($action === 'skip' && $row['match']['status'] !== 'existing') {
                throw new RuntimeException("Import row {$rowNumber} may only be skipped when it matches an existing customer.");
            }
        }
    }

    /**
     * @param array<int, array<string, mixed>> $previewByRow
     * @param array<int, string> $approvedActions
     */
    private function validateUnapprovedRows(array $previewByRow, array $approvedActions): void
    {
        $unapprovedRows = array_diff(array_keys($previewByRow), array_keys($approvedActions));

        foreach ($unapprovedRows as $rowNumber) {
            $row = $previewByRow[$rowNumber];

            if ($row['match']['status'] === 'new') {
                throw new RuntimeException("New customer row {$rowNumber} requires explicit approval before import.");
            }

            if ($row['match']['status'] === 'existing') {
                continue;
            }

            throw new RuntimeException("Import row {$rowNumber} requires resolution before import.");
        }
    }

    /**
     * @param array<int, string> $approvedActions
     * @return array{created: int, skipped: int}
     */
    private function performImport(Business $business, array $rows, array $approvedActions): array
    {
        Business::query()
            ->whereKey($business->id)
            ->lockForUpdate()
            ->firstOrFail();

        $freshPreview = $this->mapper->preview($rows, $business);
        $created = 0;
        $skipped = 0;

        foreach ($freshPreview['rows'] as $previewRow) {
            $action = $approvedActions[$previewRow['row_number']] ?? null;

            if ($action === 'skip') {
                $skipped++;
                continue;
            }

            if ($action !== 'create') {
                continue;
            }

            $this->assertFreshRowIsCreatable($previewRow);
            $this->createCustomerFromPreview($business, $previewRow);
            $created++;
        }

        return compact('created', 'skipped');
    }

    /**
     * @param array<string, mixed> $previewRow
     */
    private function assertFreshRowIsCreatable(array $previewRow): void
    {
        if ($previewRow['status'] !== 'ready' || $previewRow['match']['status'] !== 'new') {
            throw new RuntimeException("Import row {$previewRow['row_number']} changed before it could be created.");
        }
    }

    /**
     * @param array<string, mixed> $previewRow
     */
    private function createCustomerFromPreview(Business $business, array $previewRow): void
    {
        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => trim($previewRow['customer']['name']),
            'legal_name' => $previewRow['customer']['legal_name'],
            'registration_number' => $previewRow['customer']['registration_number'],
            'tax_number' => $previewRow['customer']['tax_number'],
            'vat_number' => $previewRow['customer']['vat_number'],
            'billing_address' => $previewRow['customer']['billing_address'],
            'notes' => $previewRow['customer']['notes'],
        ]);

        $customer->contacts()->create([
            'name' => trim($previewRow['primary_contact']['name']),
            'phone' => $previewRow['primary_contact']['phone'],
            'email' => $previewRow['primary_contact']['email'],
            'label' => 'Primary',
            'is_primary' => true,
        ]);

        Audit::record(
            'customers.imported',
            $customer,
            [
                'source' => 'spreadsheet',
                'source_row' => $previewRow['row_number'],
            ],
            $business->id,
        );
    }
}
