<?php

namespace App\Services;

use RuntimeException;

/**
 * Validates untrusted business-archive file references before any payload is staged.
 */
final class BusinessArchiveFileManifestValidator
{
    private const ALLOWED_KEYS = [
        'table',
        'id',
        'column',
        'disk_column',
        'source_disk',
        'entry',
        'original_name',
    ];

    /**
     * @return array<string, bool>
     */
    public function validate(array $manifest): array
    {
        $files = $manifest['files'] ?? null;
        if (! is_array($files)) {
            throw new RuntimeException('The business archive file manifest is invalid.');
        }
        if (! array_is_list($files)) {
            throw new RuntimeException('The business archive file manifest must be a list.');
        }

        $references = [];
        $entries = [];
        foreach ($files as $file) {
            $reference = $this->validatedReference($file);
            $key = implode(':', [
                $reference['table'],
                (string) $reference['id'],
                $reference['column'],
            ]);
            $entry = $reference['entry'];

            if (isset($references[$key])) {
                throw new RuntimeException('The archive contains a duplicate business file reference.');
            }
            if ($entry === '' || isset($entries[$entry])) {
                throw new RuntimeException('The archive contains a duplicate or invalid file payload entry.');
            }

            $references[$key] = true;
            $entries[$entry] = true;
        }

        return $references;
    }

    /**
     * @return array{table:string,id:int,column:string,entry:string}
     */
    private function validatedReference(mixed $file): array
    {
        if (! is_array($file)) {
            throw new RuntimeException('The archive contains an invalid business file reference.');
        }

        $this->assertAllowedKeys($file);
        $this->assertRequiredFields($file);

        return [
            'table' => $file['table'],
            'id' => (int) $file['id'],
            'column' => $file['column'],
            'entry' => $file['entry'],
        ];
    }

    private function assertAllowedKeys(array $file): void
    {
        if (array_key_exists('bytes', $file)) {
            throw new RuntimeException('The archive file manifest must not contain inline payload bytes.');
        }
        if (array_diff(array_keys($file), self::ALLOWED_KEYS) !== []) {
            throw new RuntimeException('The archive contains an invalid business file reference.');
        }
    }

    private function assertRequiredFields(array $file): void
    {
        $id = $file['id'] ?? null;
        if (! is_string($file['table'] ?? null)
            || ! is_string($file['column'] ?? null)
            || ! is_string($file['entry'] ?? null)
            || ! is_string($file['source_disk'] ?? null)
            || ! $this->hasValidRecordId($id)
            || (array_key_exists('original_name', $file) && ! is_string($file['original_name']))) {
            throw new RuntimeException('The archive contains an invalid business file reference.');
        }
    }

    private function hasValidRecordId(mixed $id): bool
    {
        return (is_int($id) || (is_string($id) && ctype_digit($id))) && (int) $id > 0;
    }
}
