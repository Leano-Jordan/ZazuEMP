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

            $files = $this->collectFiles($business, $data);
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
            if ($zip->numFiles > 5000) {
                throw new RuntimeException('The business archive contains too many entries.');
            }

            $totalBytes = 0;
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $stat = $zip->statIndex($index);
                $totalBytes += (int) ($stat['size'] ?? 0);
                if ($totalBytes > self::MAX_UNCOMPRESSED_BYTES) {
                    throw new RuntimeException('The business archive exceeds the 250 MB restore limit.');
                }
            }

            $manifest = json_decode((string) $zip->getFromName('manifest.json'), true, 512, JSON_THROW_ON_ERROR);
            if (($manifest['format'] ?? null) !== 'zazu-business-archive'
                || ($manifest['version'] ?? null) !== self::VERSION) {
                throw new RuntimeException('This business archive version is not supported by this Zazu installation.');
            }

            $data = json_decode((string) $zip->getFromName('data.json'), true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($data)) {
                throw new RuntimeException('The business archive data is missing or invalid.');
            }

            $definitions = $this->definitions();
            if (array_keys($data) !== array_keys($definitions)) {
                throw new RuntimeException('The archive does not contain the complete supported business dataset.');
            }
            foreach ($definitions as $table => $_definition) {
                if (! is_array($data[$table])) {
                    throw new RuntimeException('The archive contains an invalid dataset: '.$table.'.');
                }
                foreach ($data[$table] as $row) {
                    if (! is_array($row)) {
                        throw new RuntimeException('The archive contains an invalid record in '.$table.'.');
                    }
                }
            }

            $this->assertTargetIsEmpty($target, $definitions);
            if (! is_array($manifest['files'] ?? null)) {
                throw new RuntimeException('The business archive file manifest is invalid.');
            }

            $filePayloads = [];
            $allowedFileColumns = [
                'businesses' => ['logo_path', 'dashboard_image_path', 'wallpaper_path'],
                'business_capabilities' => ['image_path'],
                'customers' => ['profile_photo_path'],
                'compliance_documents' => ['storage_path'],
                'event_attachments' => ['path'],
            ];
            foreach (($manifest['files'] ?? []) as $file) {
                $entry = (string) ($file['entry'] ?? '');
                $table = (string) ($file['table'] ?? '');
                $column = (string) ($file['column'] ?? '');
                if (! Str::startsWith($entry, 'payload/files/')
                    || str_contains($entry, '..')
                    || ! in_array($column, $allowedFileColumns[$table] ?? [], true)
                    || ($table === 'event_attachments' && ($file['disk_column'] ?? null) !== 'disk')
                    || ($table !== 'event_attachments' && ($file['disk_column'] ?? null) !== null)) {
                    throw new RuntimeException('The archive contains an unsafe file reference.');
                }
                $validIds = $table === 'businesses'
                    ? [(int) ($manifest['source_business']['id'] ?? 0)]
                    : array_map('intval', array_column($data[$table] ?? [], 'id'));
                if (! in_array((int) ($file['id'] ?? 0), $validIds, true)) {
                    throw new RuntimeException('The archive contains a file reference outside its business dataset.');
                }
                $bytes = $zip->getFromName($entry);
                if ($bytes === false) {
                    throw new RuntimeException('A referenced business file is missing from the archive.');
                }
                $filePayloads[] = $file + ['bytes' => $bytes];
            }

            return $this->restoreRows($target, $definitions, $data, $filePayloads, $manifest['source_business'] ?? []);
        } finally {
            $zip->close();
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
    private function collectFiles(Business $business, array &$data): array
    {
        $references = [];
        $add = function (string $table, int $id, string $column, ?string $path, ?string $disk = null) use (&$references): void {
            if (! $path) {
                return;
            }
            if (Str::contains($path, ['..', '\\'])) {
                throw new RuntimeException("A referenced business file path is unsafe: {$table}#{$id}.");
            }
            $diskName = $disk ?: 'private';
            if ($disk !== null && ! in_array($diskName, ['private', 'local'], true)) {
                throw new RuntimeException("An attachment uses an unsupported storage disk: {$table}#{$id}.");
            }
            $candidates = $disk !== null ? [$diskName] : ['private', 'local', 'public'];
            $sourceDisk = null;
            foreach ($candidates as $candidate) {
                if (Storage::disk($candidate)->exists($path)) {
                    $sourceDisk = $candidate;
                    break;
                }
            }
            if ($sourceDisk === null) {
                throw new RuntimeException("A referenced business file is missing: {$table}#{$id}.");
            }
            $entry = 'payload/files/'.Str::uuid().'.bin';
            $references[] = [
                'table' => $table,
                'id' => $id,
                'column' => $column,
                'disk_column' => $disk !== null ? 'disk' : null,
                'source_disk' => $sourceDisk,
                'entry' => $entry,
                'original_name' => basename($path),
            ];
        };

        foreach (['logo_path', 'dashboard_image_path', 'wallpaper_path'] as $column) {
            $add('businesses', (int) $business->id, $column, $business->{$column});
        }
        foreach (['business_capabilities' => 'image_path', 'customers' => 'profile_photo_path', 'compliance_documents' => 'storage_path'] as $table => $column) {
            foreach ($data[$table] as $row) {
                $add($table, (int) $row['id'], $column, $row[$column] ?? null);
            }
        }
        foreach ($data['event_attachments'] as $row) {
            $add('event_attachments', (int) $row['id'], 'path', $row['path'] ?? null, $row['disk'] ?? 'private');
        }

        $contents = [];
        foreach ($references as &$reference) {
            $sourcePath = match ($reference['table']) {
                'businesses' => $business->{$reference['column']},
                default => collect($data[$reference['table']])->firstWhere('id', $reference['id'])[$reference['column']],
            };
            $contents[$reference['entry']] = Storage::disk($reference['source_disk'])->get($sourcePath);
        }
        unset($reference);

        return ['manifest' => $references, 'contents' => $contents];
    }

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
    private function restoreRows(Business $target, array $definitions, array $data, array $filePayloads, array $sourceProfile): array
    {
        $idMaps = [];
        $createdFiles = [];
        $records = 0;

        try {
            DB::transaction(function () use ($target, $definitions, $data, $filePayloads, $sourceProfile, &$idMaps, &$createdFiles, &$records): void {
                DB::table('businesses')->where('id', $target->id)->lockForUpdate()->first();
                $this->assertTargetIsEmpty($target, $definitions);

                foreach ($definitions as $table => $definition) {
                    $foreignKeys = Schema::getForeignKeys($table);
                    foreach ($data[$table] as $sourceRow) {
                        $oldId = (int) ($sourceRow['id'] ?? 0);
                        if ($oldId < 1 || array_diff(array_keys($sourceRow), Schema::getColumnListing($table)) !== []) {
                            throw new RuntimeException("Restore stopped: invalid row structure in {$table}.");
                        }
                        $row = $sourceRow;
                        unset($row['id']);

                        if (array_key_exists('business_id', $row)) {
                            $row['business_id'] = $target->id;
                        }

                        if ($table === 'audit_logs' && ! empty($row['subject_id'])) {
                            $subjectClass = ltrim((string) ($row['subject_type'] ?? ''), '\\');
                            if (class_exists($subjectClass)) {
                                $subjectTable = (new $subjectClass())->getTable();
                                $row['subject_id'] = $idMaps[$subjectTable][(string) $row['subject_id']] ?? null;
                            } else {
                                $row['subject_id'] = null;
                            }
                        }

                        foreach ($foreignKeys as $foreignKey) {
                            foreach ($foreignKey['columns'] as $column) {
                                if ($column === 'business_id') {
                                    continue;
                                }

                                $oldForeignId = $row[$column] ?? null;
                                if ($oldForeignId === null) {
                                    continue;
                                }
                                $foreignTable = $foreignKey['foreign_table'];
                                if ($foreignTable === 'businesses') {
                                    $row[$column] = $target->id;
                                } elseif ($foreignTable === 'users') {
                                    $row[$column] = null;
                                } elseif (isset($idMaps[$foreignTable][(string) $oldForeignId])) {
                                    $row[$column] = $idMaps[$foreignTable][(string) $oldForeignId];
                                } else {
                                    throw new RuntimeException("Restore stopped: unresolved {$table}.{$column} relationship.");
                                }
                            }
                        }

                        $newId = DB::table($table)->insertGetId($row);
                        $idMaps[$table][(string) $oldId] = $newId;
                        $records++;
                    }
                }

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

                $safeProfile = array_intersect_key($sourceProfile, array_flip(['name', 'currency', 'email', 'phone', 'address', 'website', 'tax_number']));
                if ($safeProfile !== []) {
                    DB::table('businesses')->where('id', $target->id)->update($safeProfile);
                }
            });

            return ['tables' => count($definitions), 'records' => $records, 'files' => count($filePayloads)];
        } catch (\Throwable $exception) {
            foreach ($createdFiles as $file) {
                Storage::disk($file['disk'])->delete($file['path']);
            }
            throw $exception;
        }
    }

}
