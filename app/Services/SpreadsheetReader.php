<?php

namespace AppServices;

use RuntimeException;

class SpreadsheetReader
{
    public function __construct(
        private readonly XlsxSpreadsheetReader $xlsxReader,
        private readonly SpreadsheetRowNormalizer $normalizer,
    ) {
    }

    /**
     * Read a CSV or XLSX workbook into a predictable, row-oriented structure.
     *
     * This is deliberately a reader only. It does not create or update Zazu
     * business records; import mapping/validation belongs to the next layer.
     *
     * @return array<string, array{headers: array<int, string>, rows: array<int, array<string, string>>}>
     */
    public function read(string $path, ?string $originalName = null, ?string $mimeType = null): array
    {
        $extension = strtolower(pathinfo($originalName ?: $path, PATHINFO_EXTENSION));

        if ($extension === 'csv' || $this->isCsvMime($mimeType)) {
            return ['Sheet 1' => $this->readCsv($path)];
        }

        if ($extension === 'xlsx' || $mimeType === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            return $this->xlsxReader->read($path);
        }

        throw new RuntimeException('Unsupported spreadsheet format. Use CSV or XLSX.');
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<string, string>>}
     */
    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('Unable to open the spreadsheet.');
        }

        try {
            $rows = [];

            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = array_map(
                    fn ($value) => trim((string) ($value ?? '')),
                    $row
                );
            }
        } finally {
            fclose($handle);
        }

        return $this->normalizer->normalise($rows);
    }

    private function isCsvMime(?string $mimeType): bool
    {
        return in_array($mimeType, ['text/csv', 'application/csv', 'text/plain'], true);
    }
}
