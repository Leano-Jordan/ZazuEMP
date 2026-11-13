<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

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
            'customer_contacts' => ['scope' => 'parent', 'parent' => 'customers'],
            'events' => ['scope' => 'business'],
            'event_requirements' => ['scope' => 'parent', 'parent' => 'events'],
            'quotes' => ['scope' => 'parent', 'parent' => 'events'],
            'quote_versions' => ['scope' => 'parent', 'parent' => 'quotes'],
            'quote_items' => ['scope' => 'parent', 'parent' => 'quote_versions'],
            'travel_costs' => ['scope' => 'parent', 'parent' => 'events'],
            'event_costs' => ['scope' => 'business'],
            'event_preparation_items' => ['scope' => 'business'],
            'purchase_orders' => ['scope' => 'business'],
            'purchase_order_items' => ['scope' => 'business'],
            'inventory_items' => ['scope' => 'business'],
            'inventory_movements' => ['scope' => 'business'],
            'assets' => ['scope' => 'business'],
            'asset_allocations' => ['scope' => 'business'],
            'invoices' => ['scope' => 'business'],
            'invoice_items' => ['scope' => 'parent', 'parent' => 'invoices'],
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
            }

            $files = $this->collectFiles($business, $data);
            $zip->addFromString('manifest.json', json_encode([
                'format' => 'zazu-business-archive',
                'version' => self::VERSION,
                'created_at' => now()->toIso8601String(),
                'source_business' => [
                    'name' => $business->name,
                    'currency' => $business->currency,
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
    public function restore(string $archivePath, Business $target): array
    {
        $zip = new ZipArchive();
        if ($zip->open($archivePath) !== true) {
            throw new RuntimeException('The uploaded file is not a valid Zazu business archive.');
        }

        try {
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

            $this->assertTargetIsEmpty($target, $definitions);
            $filePayloads = [];
            foreach (($manifest['files'] ?? []) as $file) {
                $entry = (string) ($file['entry'] ?? '');
                if (! Str::startsWith($entry, 'payload/files/') || str_contains($entry, '..')) {
                    throw new RuntimeException('The archive contains an unsafe file path.');
                }
                $bytes = $zip->getFromName($entry);
                if ($bytes === false) {
                    throw new RuntimeException('A referenced business file is missing from the archive.');
                }
                $filePayloads[] = $file + ['bytes' => $bytes];
            }

            return $this->restoreRows($target, $definitions, $data, $filePayloads);
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
            $foreignKey = Str::singular($definition['parent']).'_id';
            $query->whereIn($foreignKey, $parentIds);
        }

        return $query->get()->map(fn ($row): array => (array) $row)->all();
    }

    private function collectFiles(Business $business, array &$data): array
    {
        $references = [];
        $add = function (string $table, int $id, string $column, ?string $path, ?string $disk = null) use (&$references): void {
            if (! $path) {
                return;
            }
            $diskName = $disk ?: 'private';
            if ($diskName !== 'private' || ! Storage::disk('private')->exists($path)) {
                throw new RuntimeException("A referenced private business file is missing: {$table}#{$id}.");
            }
            $entry = 'payload/files/'.Str::uuid().'.bin';
            $references[] = [
                'table' => $table,
                'id' => $id,
                'column' => $column,
                'disk_column' => $disk !== null ? 'disk' : null,
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
            $contents[$reference['entry']] = Storage::disk('private')->get($sourcePath);
        }
        unset($reference);

        return ['manifest' => $references, 'contents' => $contents];
    }

    private function assertTargetIsEmpty(Business $target, array $definitions): void
    {
        $loaded = [];
        foreach ($definitions as $table => $definition) {
            $query = DB::table($table);
            if ($definition['scope'] === 'business') {
                $query->where('business_id', $target->id);
            } else {
                $parentIds = DB::table($definition['parent'])
                    ->where('business_id', $target->id)->pluck('id')->all();
                if ($parentIds === []) {
                    continue;
                }
                $query->whereIn(Str::singular($definition['parent']).'_id', $parentIds);
            }
            if ($query->exists()) {
                throw new RuntimeException('Restore stopped: the selected business workspace is not empty.');
            }
        }
    }

    private function restoreRows(Business $target, array $definitions, array $data, array $filePayloads): array
    {
        $idMaps = [];
        $createdFiles = [];
        $records = 0;

        try {
            DB::transaction(function () use ($target, $definitions, $data, $filePayloads, &$idMaps, &$createdFiles, &$records): void {
                foreach ($definitions as $table => $definition) {
                    $foreignKeys = Schema::getForeignKeys($table);
                    foreach ($data[$table] as $sourceRow) {
                        $oldId = (int) $sourceRow['id'];
                        $row = $sourceRow;
                        unset($row['id']);

                        if (array_key_exists('business_id', $row)) {
                            $row['business_id'] = $target->id;
                        }

                        foreach ($foreignKeys as $foreignKey) {
                            foreach ($foreignKey['columns'] as $column) {
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
                    $path = 'business-archives/'.$target->id.'/'.Str::uuid().'/'.basename($file['original_name']);
                    Storage::disk('private')->put($path, $file['bytes']);
                    $createdFiles[] = $path;

                    DB::table($file['table'])->where('id', $newId)->update([
                        $file['column'] => $path,
                        ...($file['disk_column'] ? [$file['disk_column'] => 'private'] : []),
                    ]);
                }

                $sourceProfile = $this->sourceProfile($data);
                if ($sourceProfile !== []) {
                    DB::table('businesses')->where('id', $target->id)->update($sourceProfile);
                }
            });

            return ['tables' => count($definitions), 'records' => $records, 'files' => count($filePayloads)];
        } catch (\Throwable $exception) {
            foreach ($createdFiles as $path) {
                Storage::disk('private')->delete($path);
            }
            throw $exception;
        }
    }

    private function sourceProfile(array $data): array
    {
        return [];
    }
}
