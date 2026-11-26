<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

/**
 * Versioned tenant archive coordinator. Its explicit table and FK allowlists
 * intentionally centralise fail-closed restore rules for the supported schema.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
final class BusinessArchiveService
{
    public function __construct(
        private readonly BusinessArchiveFileService $files,
        private readonly BusinessArchiveFileCollector $fileCollector,
        private readonly BusinessArchiveZipWriter $zipWriter
    ) {}

    private const VERSION = 1;

    private const MAX_UNCOMPRESSED_BYTES = 262144000;

    private const SAFE_RESTORE_FILE_EXTENSIONS = [
        'businesses' => ['jpg', 'jpeg', 'png', 'webp'],
        'business_capabilities' => ['jpg', 'jpeg', 'png', 'webp'],
        'customers' => ['jpg', 'jpeg', 'png', 'webp'],
        'compliance_documents' => ['pdf', 'jpg', 'jpeg', 'png', 'webp'],
        'event_attachments' => ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'txt', 'csv', 'doc', 'docx', 'xls', 'xlsx'],
    ];

    /**
     * Parent scope is used only for tables without their own business_id.
     * The order is also the dependency order used during restore.
     *
     * @return array<string, array{scope:'business'|'parent',parent?:string,parent_key?:string}>
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
            // Preserve the per-business invoice counter when moving a workspace.
            'invoice_number_sequences' => ['scope' => 'business'],
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

        $files = $this->fileCollector->collectFiles($business, $data);

        return $this->zipWriter->create($business, $data, $files);
    }

    /**
     * Validate an archive without importing it or touching a business workspace.
     *
     * @param  null|callable(string):void  $onStage
     * @return array{format_version:int,business_name:string,tables:int,records:int,files:int,uncompressed_bytes:int}
     */
    public function verify(string $archivePath, ?callable $onStage = null): array
    {
        $this->reportRestoreStage($onStage, 'archive_open');
        $zip = new ZipArchive;
        $status = $zip->open($archivePath, ZipArchive::RDONLY);
        if ($status !== true) {
            $description = match ($status) {
                ZipArchive::ER_EXISTS => 'file already exists',
                ZipArchive::ER_INCONS => 'archive is inconsistent',
                ZipArchive::ER_INVAL => 'invalid archive argument',
                ZipArchive::ER_MEMORY => 'memory allocation failed',
                ZipArchive::ER_NOENT => 'archive file does not exist',
                ZipArchive::ER_NOZIP => 'file is not a ZIP archive',
                ZipArchive::ER_OPEN => 'archive file could not be opened',
                ZipArchive::ER_READ => 'archive read failed',
                ZipArchive::ER_SEEK => 'archive seek failed',
                default => 'unclassified libzip failure',
            };
            throw new RuntimeException(
                sprintf('Zazu could not open the business archive (libzip status %d: %s).', $status, $description),
                is_int($status) ? $status : 0
            );
        }

        $filePayloads = [];
        try {
            $this->reportRestoreStage($onStage, 'archive_envelope_validation');
            [$manifest, $data, $definitions] = $this->readArchiveEnvelope($zip);

            $this->reportRestoreStage($onStage, 'attachment_payload_staging');
            $filePayloads = $this->files->readArchiveFilePayloads($zip, $manifest, $data);

            $uncompressedBytes = 0;
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $stat = $zip->statIndex($index);
                if (! is_array($stat) || ! isset($stat['size'])) {
                    throw new RuntimeException('The business archive contains an invalid ZIP entry.');
                }

                $uncompressedBytes += (int) $stat['size'];
            }

            $this->reportRestoreStage($onStage, 'verified');

            return [
                'format_version' => (int) $manifest['version'],
                'business_name' => is_string($manifest['source_business']['name'] ?? null)
                    ? $manifest['source_business']['name']
                    : 'Unnamed business',
                'tables' => count($definitions),
                'records' => array_sum(array_map('count', $data)),
                'files' => count($filePayloads),
                'uncompressed_bytes' => $uncompressedBytes,
            ];
        } finally {
            foreach ($filePayloads as $file) {
                @unlink($file['staged_path']);
            }
            $zip->close();
        }
    }

    /**
     * Restores into an existing, empty business workspace. IDs are remapped
     * and every database foreign key is checked against the archive allowlist.
     *
     * @param  null|callable(array{tables:int,records:int,files:int}):void  $afterRestore
     * @param  null|callable(string):void  $onStage
     * @return array{tables:int,records:int,files:int}
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function restore(
        string $archivePath,
        Business $target,
        ?callable $afterRestore = null,
        ?callable $onStage = null
    ): array {
        $this->reportRestoreStage($onStage, 'archive_open');
        $zip = new ZipArchive;
        $status = $zip->open($archivePath, ZipArchive::RDONLY);
        if ($status !== true) {
            $description = match ($status) {
                ZipArchive::ER_EXISTS => 'file already exists',
                ZipArchive::ER_INCONS => 'archive is inconsistent',
                ZipArchive::ER_INVAL => 'invalid archive argument',
                ZipArchive::ER_MEMORY => 'memory allocation failed',
                ZipArchive::ER_NOENT => 'archive file does not exist',
                ZipArchive::ER_NOZIP => 'file is not a ZIP archive',
                ZipArchive::ER_OPEN => 'archive file could not be opened',
                ZipArchive::ER_READ => 'archive read failed',
                ZipArchive::ER_SEEK => 'archive seek failed',
                default => 'unclassified libzip failure',
            };
            throw new RuntimeException(
                sprintf('Zazu could not open the business archive (libzip status %d: %s).', $status, $description),
                is_int($status) ? $status : 0
            );
        }

        $filePayloads = [];
        try {
            $this->reportRestoreStage($onStage, 'archive_envelope_validation');
            [$manifest, $data, $definitions] = $this->readArchiveEnvelope($zip);

            // Reject an occupied destination before staging potentially large file
            // payloads. restoreRows repeats this check under the business lock.
            $this->reportRestoreStage($onStage, 'target_workspace_preflight');
            $this->assertTargetIsEmpty($target, $definitions);

            $this->reportRestoreStage($onStage, 'attachment_payload_staging');
            $filePayloads = $this->files->readArchiveFilePayloads($zip, $manifest, $data);

            return $this->restoreRows(
                $target,
                $definitions,
                $data,
                $filePayloads,
                $manifest['source_business'],
                $afterRestore,
                $onStage
            );
        } finally {
            foreach ($filePayloads as $file) {
                @unlink($file['staged_path']);
            }
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

        // Version-1 archives created before invoice sequences were added contain
        // the previous complete table set. Keep those archives restorable.
        $legacyTables = array_values(array_diff(array_keys($definitions), ['invoice_number_sequences']));
        $isLegacyDataset = is_array($data) && array_keys($data) === $legacyTables;
        if ($isLegacyDataset) {
            $data = array_replace(array_fill_keys(array_keys($definitions), []), $data);
        }

        if (! is_array($data) || array_keys($data) !== array_keys($definitions)) {
            throw new RuntimeException('The archive does not contain the complete supported business dataset.');
        }

        foreach ($definitions as $table => $_definition) {
            $this->validateArchiveTableRows($table, $data[$table], (int) $manifest['source_business']['id']);
        }

        $this->assertArchiveTableCounts($manifest['tables'] ?? null, $data, $isLegacyDataset);

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

    private function assertArchiveTableCounts(mixed $declaredCounts, array $data, bool $isLegacyDataset): void
    {
        if (! is_array($declaredCounts)) {
            throw new RuntimeException('The business archive table-count manifest is invalid.');
        }

        $this->assertArchiveTableCountKeys($declaredCounts, $data, $isLegacyDataset);

        foreach ($data as $table => $rows) {
            $declaredCount = $this->declaredArchiveTableCount($table, $declaredCounts, $isLegacyDataset);
            if (! is_int($declaredCount) || $declaredCount < 0 || $declaredCount !== count($rows)) {
                throw new RuntimeException('The business archive record counts do not match its manifest.');
            }
        }
    }

    private function assertArchiveTableCountKeys(array $declaredCounts, array $data, bool $isLegacyDataset): void
    {
        $missingTables = array_diff(array_keys($data), array_keys($declaredCounts));
        $extraTables = array_diff(array_keys($declaredCounts), array_keys($data));

        if ($isLegacyDataset) {
            // Older v1 archives predate invoice_number_sequences. That one
            // omitted table is reconstructed as an empty array above.
            $missingTables = array_diff($missingTables, ['invoice_number_sequences']);
        }

        if ($missingTables !== [] || $extraTables !== []) {
            throw new RuntimeException('The business archive table-count manifest does not match its dataset.');
        }
    }

    private function declaredArchiveTableCount(string $table, array $declaredCounts, bool $isLegacyDataset): mixed
    {
        if (array_key_exists($table, $declaredCounts)) {
            return $declaredCounts[$table];
        }

        return $isLegacyDataset && $table === 'invoice_number_sequences' ? 0 : null;
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
        if (! is_int($rawId) && ! (is_string($rawId) && ctype_digit($rawId))) {
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
        if ((! is_int($businessId) && ! (is_string($businessId) && ctype_digit($businessId)))
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
                throw new RuntimeException('Restore stopped: this workspace still has offline device or sync state. Choose a clean, unpaired workspace; existing sync history is never overwritten.');
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
                throw new RuntimeException('Restore stopped: this workspace already contains business records. Choose a newly created empty workspace; Zazu will not overwrite or merge existing records.');
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
        if (! is_int($oldForeignId) && ! (is_string($oldForeignId) && ctype_digit($oldForeignId))) {
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

    /**
     * @param  null|callable(array{tables:int,records:int,files:int}):void  $afterRestore
     * @param  null|callable(string):void  $onStage
     * @return array{tables:int,records:int,files:int}
     */
    private function restoreRows(
        Business $target,
        array $definitions,
        array $data,
        array $filePayloads,
        array $sourceProfile,
        ?callable $afterRestore = null,
        ?callable $onStage = null
    ): array {
        $idMaps = [];
        $createdFiles = [];
        $records = 0;
        $result = ['tables' => count($definitions), 'records' => 0, 'files' => count($filePayloads)];

        try {
            DB::transaction(function () use ($target, $definitions, $data, $filePayloads, $sourceProfile, $afterRestore, $onStage, &$idMaps, &$createdFiles, &$records, &$result): void {
                DB::table('businesses')->where('id', $target->id)->lockForUpdate()->first();

                $this->reportRestoreStage($onStage, 'target_workspace_validation');
                $this->assertTargetIsEmpty($target, $definitions);

                $this->reportRestoreStage($onStage, 'database_records');
                $this->restoreArchiveTables($target, $definitions, $data, $idMaps, $records);

                $this->reportRestoreStage($onStage, 'attachment_storage');
                $this->restoreArchiveFiles($target, $filePayloads, $idMaps, $createdFiles);

                $this->reportRestoreStage($onStage, 'business_profile');
                $this->restoreBusinessProfile($target, $sourceProfile);

                $result['records'] = $records;
                if ($afterRestore !== null) {
                    $this->reportRestoreStage($onStage, 'restore_audit_write');
                    $afterRestore($result);
                }

                $this->reportRestoreStage($onStage, 'database_transaction_commit');
            });

            return $result;
        } catch (\Throwable $exception) {
            $this->cleanupRestoredFiles($createdFiles, (int) $target->id);

            throw $exception;
        }
    }

    private function reportRestoreStage(?callable $onStage, string $stage): void
    {
        if ($onStage !== null) {
            $onStage($stage);
        }
    }

    private function cleanupRestoredFiles(array $createdFiles, int $businessId): void
    {
        foreach ($createdFiles as $file) {
            try {
                if (Storage::disk($file['disk'])->delete($file['path'])) {
                    continue;
                }

                throw new RuntimeException('A partially restored business file could not be removed.');
            } catch (\Throwable $exception) {
                Log::error('Business archive rollback could not remove a partially restored file.', [
                    'business_id' => $businessId,
                    'disk' => $file['disk'],
                    'exception_class' => $exception::class,
                    'exception_code' => (string) $exception->getCode(),
                ]);
            }
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
            $allowedExtensions = self::SAFE_RESTORE_FILE_EXTENSIONS[$file['table']] ?? [];
            $originalExtension = strtolower(pathinfo((string) ($file['original_name'] ?? ''), PATHINFO_EXTENSION));
            $extension = in_array($originalExtension, $allowedExtensions, true) ? $originalExtension : 'bin';
            $filename = Str::uuid().'.'.$extension;
            $path = match ($file['table']) {
                'businesses' => 'business-branding/'.$target->id.'/'.$filename,
                'business_capabilities' => 'catalogue/'.$filename,
                'customers' => 'profile-photos/customers/'.$newId.'/'.$filename,
                'compliance_documents' => 'business-compliance/'.$target->id.'/'.$filename,
                default => 'business-archives/'.$target->id.'/'.$filename,
            };

            // Archive metadata is untrusted. Only catalogue images are intended
            // for the public disk; branding, customer photos, compliance files
            // and attachments must be private regardless of the source archive.
            $restoreDisk = $file['table'] === 'business_capabilities' ? 'public' : 'private';
            $source = @fopen($file['staged_path'], 'rb');
            if ($source === false) {
                throw new RuntimeException('Restore stopped: a staged business file could not be opened.');
            }

            // Register before writing so rollback also removes a partially written destination.
            $createdFiles[] = ['disk' => $restoreDisk, 'path' => $path];
            try {
                $written = Storage::disk($restoreDisk)->writeStream($path, $source);
            } finally {
                fclose($source);
            }

            if (! $written) {
                throw new RuntimeException('Restore stopped: a business file could not be written to its storage disk.');
            }
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
