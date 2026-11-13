<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/**
 * Versioned tenant archive coordinator. Its explicit table and FK allowlists
 * intentionally centralise fail-closed restore rules for the supported schema.
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
final class BusinessArchiveService
{
    public function __construct(private readonly BusinessArchiveFileService $files)
    {
    }

    private const VERSION = 1;
    private const MAX_UNCOMPRESSED_BYTES = 262144000;

    /**
     * Parent scope is used only for tables without their own business_id.
     * The order is also the dependency order used during restore.
     *
     * @return array<string, array{scope:string,parent?:string,foreign?:array<string,string>}>
     */
    private function definitions(): array
    {
        return [
            'business_capabilities' => ['scope' => 'business'],
            'customers' => ['scope' => 'business'],
            'suppliers' => ['scope' => 'business'],
            'tax_rates' => ['scope' => 'business'],
            'business_tax_profiles' => ['scope' => 'business'],
            'customer_contacts' => ['scope' => 'parent', 'parent' => 'customers', 'parent_key' => 'customer_id'],
            'events' => ['scope' => 'business'],
            'event_requirements' => ['scope' => 'parent', 'parent' => 'events', 'parent_key' => 'event_id'],
            'quotes' => ['scope' => 'parent', 'parent' => 'events', 'parent_key' => 'event_id'],
            'quote_versions' => ['scope' => 'parent', 'parent' => 'quotes', 'parent_key' => 'quote_id'],
            'quote_items' => ['scope' => 'parent', 'parent' => 'quote_versions', 'parent_key' => 'quote_version_id'],
            'travel_costs' => ['scope' => 'parent', 'parent' => 'events', 'parent_key' => 'event_id'],
            'event_costs' => ['scope' => 'business'],
            'event_preparation_items' => ['scope' => 'business'],
            'purchase_orders' => ['scope' => 'business'],
            'purchase_order_items' => ['scope' => 'business'],
            'inventory_items' => ['scope' => 'business'],
            'inventory_movements' => ['scope' => 'business'],
            'assets' => ['scope' => 'business'],
            'asset_allocations' => ['scope' => 'business'],
            'invoices' => ['scope' => 'business'],
            'invoice_items' => ['scope' => 'parent', 'parent' => 'invoices', 'parent_key' => 'invoice_id'],
            'payments' => ['scope' => 'business'],
            'finance_expenses' => ['scope' => 'business'],
            'purchase_order_receipts' => ['scope' => 'business'],
            'compliance_documents' => ['scope' => 'business'],
            'event_attachments' => ['scope' => 'business'],
            'audit_logs' => ['scope' => 'business'],
        ];
    }

    public function download(Business $business): string
    {
        $archivePath = tempnam(sys_get_temp_dir(), 'zazu-business-archive-');
        if ($archivePath === false) {
            throw new RuntimeException('Unable to create a temporary business archive.');
        }

        $zip = new ZipArchive();
        if ($zip->open($archivePath, ZipArchive::OVERWRITE) !== true) {
            @unlink($archivePath);
            throw new RuntimeException('Unable to open the business archive.');
        }

        try {
            $data = [];
            foreach ($this->definitions() as $table => $definition) {
                $data[$table] = $this->rowsForBusiness($table, $definition, $business->id, $data);
            if ($table === 'business_tax_profiles') {
                foreach ($data[$table] as &$profileRow) {
                    $profileRow['tcs_pin'] = null;
                }
                unset($profileRow);
            }
            }

            $files = $this->files->collectFiles($business, $data);
            $zip->addFromString('manifest.json', json_encode([
                'format' => 'zazu-business-archive',
                'version' => self::VERSION,
                'created_at' => now()->toIso8601String(),
                'source_business' => [
                    'id' => (int) $business->id,
                    'name' => $business->name,
                    'currency' => $business->currency,
                    'email' => $business->email,
                    'phone' => $business->phone,
                    'address' => $business->address,
                    'website' => $business->website,
                    'tax_number' => $business->tax_number,
                ],
                'tables' => array_map('count', $data),
                'files' => $files['manifest'],
            ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
            $zip->addFromString('data.json', json_encode($data, JSON_THROW_ON_ERROR));
            foreach ($files['contents'] as $entry => $bytes) {
                $zip->addFromString($entry, $bytes);
            }
        } finally {
            $zip->close();
        }

        return $archivePath;
    }

    /**
     * Restores into an existing, empty business workspace. IDs are remapped
     * and every database foreign key is checked against the archive allowlist.
     *
     * @return array{tables:int,records:int,files:int}
     */
    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function restore(string $archivePath, Business $target): array
    {
        $zip = new ZipArchive();
        if ($zip->open($archivePath) !== true) {
            throw new RuntimeException('The uploaded file is not a valid Zazu business archive.');
        }

        try {
            [$manifest, $data, $definitions] = $this->readArchiveEnvelope($zip);
            $filePayloads = $this->files->readArchiveFilePayloads($zip, $manifest, $data);

            return $this->restoreRows($target, $definitions, $data, $filePayloads, $manifest['source_business']);
        } finally {
            $zip->close();
        }
    }

    /**
     * @return array{0:array,1:array,2:array}
     */

    private function readArchiveEnvelope(ZipArchive $zip): array
    {
        $this->assertArchiveZipEntries($zip);
        $manifest = $this->decodeArchiveManifest($zip);
        $data = json_decode((string) $zip->getFromName('data.json'), true, 512, JSON_THROW_ON_ERROR);
        $definitions = $this->definitions();

        if (! is_array($data) || array_keys($data) !== array_keys($definitions)) {
            throw new RuntimeException('The archive does not contain the complete supported business dataset.');
        }

        foreach ($definitions as $table => $_definition) {
            $this->validateArchiveTableRows($table, $data[$table], (int) $manifest['source_business']['id']);
        }

        return [$manifest, $data, $definitions];
    }

    private function assertArchiveZipEntries(ZipArchive $zip): void
    {
        if ($zip->numFiles > 5000) {
            throw new RuntimeException('The business archive contains too many entries.');
        }

        $totalBytes = 0;
        $entryNames = [];
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $stat = $zip->statIndex($index);
            if (! is_array($stat) || ! is_string($stat['name'] ?? null) || ! isset($stat['size'])) {
                throw new RuntimeException('The business archive contains an invalid ZIP entry.');
            }
            if (isset($entryNames[$stat['name']])) {
                throw new RuntimeException('The business archive contains duplicate ZIP entry names.');
            }
            $entryNames[$stat['name']] = true;
            $totalBytes += (int) $stat['size'];
            if ($totalBytes > self::MAX_UNCOMPRESSED_BYTES) {
                throw new RuntimeException('The business archive exceeds the 250 MB restore limit.');
            }
        }
    }

    private function decodeArchiveManifest(ZipArchive $zip): array
    {
        $manifest = json_decode((string) $zip->getFromName('manifest.json'), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($manifest)
            || ($manifest['format'] ?? null) !== 'zazu-business-archive'
            || ($manifest['version'] ?? null) !== self::VERSION) {
            throw new RuntimeException('This business archive version is not supported by this Zazu installation.');
        }
        if (! is_array($manifest['source_business'] ?? null)
            || (int) ($manifest['source_business']['id'] ?? 0) < 1) {
            throw new RuntimeException('The business archive source profile is invalid.');
        }

        return $manifest;
    }

    private function validateArchiveTableRows(string $table, mixed $rows, int $sourceBusinessId): void
    {
        if (! is_array($rows)) {
            throw new RuntimeException('The archive contains an invalid dataset: '.$table.'.');
        }

        $seenRecordIds = [];
        foreach ($rows as $row) {
            $recordId = $this->validateArchiveRecord($table, $row, $sourceBusinessId);
            if (isset($seenRecordIds[$recordId])) {
                throw new RuntimeException('The archive contains a missing, invalid, or duplicate record identifier in '.$table.'.');
            }
            $seenRecordIds[$recordId] = true;
        }
    }

    private function validateArchiveRecord(string $table, mixed $row, int $sourceBusinessId): int
    {
        if (! is_array($row)) {
            throw new RuntimeException('The archive contains an invalid record in '.$table.'.');
        }
        $this->assertArchiveRecordBusiness($table, $row, $sourceBusinessId);
        $rawId = $row['id'] ?? null;
        if (! is_int($rawId) && !(is_string($rawId) && ctype_digit($rawId))) {
            throw new RuntimeException('The archive contains an invalid record identifier in '.$table.'.');
        }
        $recordId = (int) $rawId;
        if ($recordId < 1) {
            throw new RuntimeException('The archive contains a missing, invalid, or duplicate record identifier in '.$table.'.');
        }

        return $recordId;
    }

    private function assertArchiveRecordBusiness(string $table, array $row, int $sourceBusinessId): void
    {
        if (! array_key_exists('business_id', $row)) {
            return;
        }
        $businessId = $row['business_id'];
        if ((! is_int($businessId) && !(is_string($businessId) && ctype_digit($businessId)))
            || (int) $businessId !== $sourceBusinessId) {
            throw new RuntimeException('The archive contains a record outside its declared source business in '.$table.'.');
        }
    }

    private function rowsForBusiness(string $table, array $definition, int $businessId, array $loaded): array
    {
        $query = DB::table($table)->orderBy('id');
        if ($definition['scope'] === 'business') {
            $query->where('business_id', $businessId);
        } else {
            $parentIds = array_column($loaded[$definition['parent']] ?? [], 'id');
            if ($parentIds === []) {
                return [];
            }
            $foreignKey = $definition['parent_key'];
            $query->whereIn($foreignKey, $parentIds);
        }

        return $query->get()->map(fn ($row): array => (array) $row)->all();
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function assertTargetIsEmpty(Business $target, array $definitions): void
    {
        foreach ([
            'sync_devices',
            'sync_mutations',
            'sync_cursors',
            'sync_conflicts',
            'sync_streams',
            'sync_deliveries',
            'sync_entity_identities',
        ] as $syncTable) {
            if (Schema::hasTable($syncTable)
                && DB::table($syncTable)->where('business_id', $target->id)->exists()) {
                throw new RuntimeException('Restore stopped: the target workspace is not empty or still has offline sync state. Restore before pairing devices.');
            }
        }

        $targetRows = [];
        foreach ($definitions as $table => $definition) {
            $query = DB::table($table);
            if ($definition['scope'] === 'business') {
                $query->where('business_id', $target->id);
            } else {
                $parentIds = array_column($targetRows[$definition['parent']] ?? [], 'id');
                if ($parentIds === []) {
                    $targetRows[$table] = [];
                    continue;
                }
                $query->whereIn($definition['parent_key'], $parentIds);
            }
            $rows = $query->get()->map(fn ($row): array => (array) $row)->all();
            if ($rows !== []) {
                throw new RuntimeException('Restore stopped: the selected business workspace is not empty.');
            }
            $targetRows[$table] = $rows;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function remapArchiveForeignKey(
        string $table,
        string $column,
        string $foreignTable,
        mixed $oldForeignId,
        Business $target,
        array $idMaps
    ): mixed {
        if ($oldForeignId === null) {
            return null;
        }
        if (! is_int($oldForeignId) && !(is_string($oldForeignId) && ctype_digit($oldForeignId))) {
            throw new RuntimeException("Restore stopped: invalid foreign key value in {$table}.{$column}.");
        }
        if ((int) $oldForeignId < 1) {
            throw new RuntimeException("Restore stopped: invalid foreign key value in {$table}.{$column}.");
        }
        if ($foreignTable === 'businesses') {
            return $target->id;
        }
        if ($foreignTable === 'users') {
            return null;
        }
        $mappedId = $idMaps[$foreignTable][(string) $oldForeignId] ?? null;
        if ($mappedId !== null) {
            return $mappedId;
        }

        throw new RuntimeException("Restore stopped: unresolved {$table}.{$column} -> {$foreignTable}#{$oldForeignId}.");
    }

    private function restoreRows(Business $target, array $definitions, array $data, array $filePayloads, array $sourceProfile): array
    {
        $idMaps = [];
        $createdFiles = [];
        $records = 0;

        try {
            DB::transaction(function () use ($target, $definitions, $data, $filePayloads, $sourceProfile, &$idMaps, &$createdFiles, &$records): void {
                DB::table('businesses')->where('id', $target->id)->lockForUpdate()->first();
                $this->assertTargetIsEmpty($target, $definitions);
                $this->restoreArchiveTables($target, $definitions, $data, $idMaps, $records);
                $this->restoreArchiveFiles($target, $filePayloads, $idMaps, $createdFiles);
                $this->restoreBusinessProfile($target, $sourceProfile);
            });

            return ['tables' => count($definitions), 'records' => $records, 'files' => count($filePayloads)];
        } catch (\Throwable $exception) {
            foreach ($createdFiles as $file) {
                Storage::disk($file['disk'])->delete($file['path']);
            }
            throw $exception;
        }
    }

    private function restoreArchiveTables(Business $target, array $definitions, array $data, array &$idMaps, int &$records): void
    {
        foreach ($definitions as $table => $_definition) {
            $foreignKeys = Schema::getForeignKeys($table);
            foreach ($data[$table] as $sourceRow) {
                $newId = $this->restoreArchiveRow($target, $table, $sourceRow, $foreignKeys, $idMaps);
                $idMaps[$table][(string) $sourceRow['id']] = $newId;
                $records++;
            }
        }
    }

    private function restoreArchiveRow(Business $target, string $table, array $sourceRow, array $foreignKeys, array $idMaps): int
    {
        $oldId = (int) ($sourceRow['id'] ?? 0);
        if ($oldId < 1 || array_diff(array_keys($sourceRow), Schema::getColumnListing($table)) !== []) {
            throw new RuntimeException("Restore stopped: invalid row structure in {$table}.");
        }

        $row = $sourceRow;
        unset($row['id']);
        if (array_key_exists('business_id', $row)) {
            $row['business_id'] = $target->id;
        }
        if ($table === 'audit_logs') {
            // Archives are untrusted input: preserve the audit event, but detach
            // polymorphic subjects so imported class names are never autoloaded.
            $row['subject_type'] = null;
            $row['subject_id'] = null;
        }

        $this->remapArchiveRowForeignKeys($target, $table, $sourceRow, $row, $foreignKeys, $idMaps);

        return (int) DB::table($table)->insertGetId($row);
    }

    private function remapArchiveRowForeignKeys(Business $target, string $table, array $sourceRow, array &$row, array $foreignKeys, array $idMaps): void
    {
        foreach ($foreignKeys as $foreignKey) {
            foreach ($foreignKey['columns'] as $column) {
                if ($column === 'business_id') {
                    continue;
                }
                // Resolve from the original archive row so overlapping simple and
                // composite tenant constraints remain deterministic.
                $row[$column] = $this->remapArchiveForeignKey(
                    $table,
                    $column,
                    $foreignKey['foreign_table'],
                    $sourceRow[$column] ?? null,
                    $target,
                    $idMaps
                );
            }
        }
    }

    private function restoreArchiveFiles(Business $target, array $filePayloads, array $idMaps, array &$createdFiles): void
    {
        foreach ($filePayloads as $file) {
            $newId = $file['table'] === 'businesses'
                ? (int) $target->id
                : (int) $idMaps[$file['table']][(string) $file['id']];
            $path = 'business-archives/'.$target->id.'/'.Str::uuid().'.bin';
            $restoreDisk = $file['table'] === 'event_attachments' ? 'private' : ($file['source_disk'] ?? 'private');
            if (! in_array($restoreDisk, ['private', 'local', 'public'], true)
                || ! Storage::disk($restoreDisk)->put($path, $file['bytes'])) {
                throw new RuntimeException('Restore stopped: a business file could not be written to its storage disk.');
            }
            $createdFiles[] = ['disk' => $restoreDisk, 'path' => $path];
            DB::table($file['table'])->where('id', $newId)->update([
                $file['column'] => $path,
                ...($file['disk_column'] ? [$file['disk_column'] => 'private'] : []),
            ]);
        }
    }

    private function restoreBusinessProfile(Business $target, array $sourceProfile): void
    {
        $safeProfile = array_intersect_key($sourceProfile, array_flip(['name', 'currency', 'email', 'phone', 'address', 'website', 'tax_number']));
        if ($safeProfile !== []) {
            DB::table('businesses')->where('id', $target->id)->update($safeProfile);
        }
    }

}
