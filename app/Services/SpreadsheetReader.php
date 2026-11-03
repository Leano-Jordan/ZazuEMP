<?php

namespace App\Services;

use Illuminate\Support\Str;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 *
 * This class deliberately keeps CSV/XLSX format dispatch and ZIP/XML
 * coordination together as the bounded spreadsheet-reader boundary.
 */
class SpreadsheetReader
{
    private const MAX_SOURCE_BYTES = 5 * 1024 * 1024;
    private const MAX_DATA_ROWS = 5000;
    private const MAX_TOTAL_ROWS = self::MAX_DATA_ROWS + 1;
    private const MAX_COLUMNS = 100;
    private const MAX_CELL_BYTES = 8192;
    private const MAX_CELLS = 250000;
    private const MAX_ARCHIVE_ENTRIES = 256;
    private const MAX_ARCHIVE_UNCOMPRESSED_BYTES = 20 * 1024 * 1024;
    private const MAX_ARCHIVE_ENTRY_BYTES = 8 * 1024 * 1024;
    private const MAX_WORKSHEETS = 20;
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
        $this->assertSourceSize($path);
        $extension = strtolower(pathinfo($originalName ?: $path, PATHINFO_EXTENSION));

        if ($extension === 'csv' || $this->isCsvMime($mimeType)) {
            return ['Sheet 1' => $this->readCsv($path)];
        }

        if ($extension === 'xlsx' || $mimeType === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            return $this->readXlsx($path);
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
                if (count($rows) >= self::MAX_TOTAL_ROWS) {
                    throw new RuntimeException('The spreadsheet exceeds the limit of 5,000 data rows.');
                }

                $this->assertBoundedRow($row);
                $rows[] = array_map(
                    fn ($value) => trim((string) ($value ?? '')),
                    $row
                );
            }
        } finally {
            fclose($handle);
        }

        return $this->normaliseRows($rows);
    }

    /**
     * Read the XLSX package while keeping ZIP/XML resources bounded.
     *
     * @return array<string, array{headers: array<int, string>, rows: array<int, array<string, string>>}>
     */
    private function readXlsx(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('XLSX import requires the PHP ZIP extension.');
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('Unable to open the XLSX workbook.');
        }

        try {
            $this->assertSafeArchive($zip);
            $workbook = $this->readXlsxDocument($zip, 'xl/workbook.xml');
            $relationships = $this->readXlsxDocument($zip, 'xl/_rels/workbook.xml.rels');
            $sharedStrings = $this->readSharedStrings($zip);

            return $this->readXlsxSheets($zip, $workbook, $relationships, $sharedStrings);
        } finally {
            $zip->close();
        }
    }

    private function readXlsxDocument(ZipArchive $zip, string $entry): SimpleXMLElement
    {
        $contents = $zip->getFromName($entry);

        if ($contents === false) {
            throw new RuntimeException('The XLSX workbook structure is invalid.');
        }

        return $this->xml($contents);
    }

    /**
     * @param array<int, string> $sharedStrings
     * @return array<string, array{headers: array<int, string>, rows: array<int, array<string, string>>}>
     */
    private function readXlsxSheets(
        ZipArchive $zip,
        SimpleXMLElement $workbook,
        SimpleXMLElement $relationships,
        array $sharedStrings,
    ): array {
        $relationshipMap = [];

        foreach ($relationships->Relationship as $relationship) {
            $relationshipMap[(string) $relationship['Id']] = (string) $relationship['Target'];
        }

        $sheets = [];
        $sheetCount = 0;
        $totalCells = 0;

        foreach ($workbook->sheets->sheet as $sheet) {
            $sheetCount++;
            if ($sheetCount > self::MAX_WORKSHEETS) {
                throw new RuntimeException('The XLSX workbook exceeds the limit of 20 worksheets.');
            }

            $name = trim((string) $sheet['name']) ?: 'Sheet ' . $sheetCount;
            $relationshipId = (string) $sheet
                ->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')->id;
            $target = $relationshipMap[$relationshipId] ?? null;

            if (! $target) {
                continue;
            }

            $data = $this->readXlsxSheet($zip, $target, $sharedStrings, $totalCells);

            if ($data !== null) {
                $sheets[$name] = $data;
            }
        }

        if ($sheets === []) {
            throw new RuntimeException('The XLSX workbook contains no readable sheets.');
        }

        return $sheets;
    }

    /**
     * @param array<int, string> $sharedStrings
     * @return array{headers: array<int, string>, rows: array<int, array<string, string>>}|null
     */
    private function readXlsxSheet(
        ZipArchive $zip,
        string $target,
        array $sharedStrings,
        int &$totalCells,
    ): ?array {
        $target = ltrim(str_replace('\\', '/', $target), '/');
        $sheetPath = Str::startsWith($target, 'xl/') ? $target : 'xl/' . $target;
        $sheetXml = $zip->getFromName($sheetPath);

        if ($sheetXml === false) {
            return null;
        }

        $sheetData = $this->xml($sheetXml);
        $rows = $this->readXlsxRows($sheetData, $sharedStrings, $totalCells);

        return $this->normaliseRows($rows);
    }

    /**
     * @param array<int, string> $sharedStrings
     * @return array<int, array<int, string>>
     */
    private function readXlsxRows(
        SimpleXMLElement $sheetData,
        array $sharedStrings,
        int &$totalCells,
    ): array {
        $rows = [];
        $rowCount = 0;

        foreach ($sheetData->sheetData->row as $row) {
            $rowCount++;
            if ($rowCount > self::MAX_TOTAL_ROWS) {
                throw new RuntimeException('The spreadsheet exceeds the limit of 5,000 data rows per worksheet.');
            }

            $values = [];

            foreach ($row->c as $cell) {
                [$column, $value] = $this->readXlsxCell($cell, $sharedStrings, $totalCells);
                $values[$column] = $value;
            }

            $this->assertBoundedRow($values);

            if ($values !== []) {
                $rows[] = $values;
            }
        }

        return $rows;
    }

    /**
     * @param array<int, string> $sharedStrings
     * @return array{0: int, 1: string}
     */
    private function readXlsxCell(
        SimpleXMLElement $cell,
        array $sharedStrings,
        int &$totalCells,
    ): array {
        $column = $this->columnIndex((string) $cell['r']);

        if ($column >= self::MAX_COLUMNS) {
            throw new RuntimeException('The spreadsheet exceeds the limit of 100 columns.');
        }

        $totalCells++;
        if ($totalCells > self::MAX_CELLS) {
            throw new RuntimeException('The XLSX workbook exceeds the limit of 250,000 cells.');
        }

        $type = (string) $cell['t'];
        $value = (string) ($cell->v ?? '');

        if ($type === 's') {
            $value = $sharedStrings[(int) $value] ?? '';
        } elseif ($type === 'inlineStr') {
            $value = $this->inlineString($cell);
        } elseif ($type === 'b') {
            $value = $value === '1' ? 'TRUE' : 'FALSE';
        }

        $this->assertCellSize($value);

        return [$column, trim($value)];
    }

    /**
     * @return array<int, string>
     */
    private function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $document = $this->xml($xml);
        $strings = [];

        foreach ($document->si as $item) {
            if (count($strings) >= self::MAX_CELLS) {
                throw new RuntimeException('The XLSX workbook contains too many shared strings.');
            }

            $value = trim($this->xmlText($item));
            $this->assertCellSize($value);
            $strings[] = $value;
        }

        return $strings;
    }

    private function inlineString(SimpleXMLElement $cell): string
    {
        return trim($this->xmlText($cell->is));
    }

    private function xmlText(SimpleXMLElement $element): string
    {
        $text = '';

        foreach ($element->t as $part) {
            $text .= (string) $part;
        }

        foreach ($element->r as $run) {
            $text .= (string) $run->t;
        }

        return $text;
    }

    private function xml(string $contents): SimpleXMLElement
    {
        $xml = simplexml_load_string($contents, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);

        if ($xml === false) {
            throw new RuntimeException('The spreadsheet contains invalid XML.');
        }

        return $xml;
    }

    private function columnIndex(string $reference): int
    {
        if (!preg_match('/^([A-Z]+)\d+$/i', $reference, $matches)) {
            throw new RuntimeException('The XLSX workbook contains an invalid cell reference.');
        }

        $index = 0;
        foreach (str_split(strtoupper($matches[1])) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return $index - 1;
    }

    /**
     * Normalize sparse source rows into unique, named columns.
     *
     * @param array<int, array<int|string, string>> $rows
     * @return array{headers: array<int, string>, rows: array<int, array<string, string>>}
     */
    private function normaliseRows(array $rows): array
    {
        if (count($rows) > self::MAX_TOTAL_ROWS) {
            throw new RuntimeException('The spreadsheet exceeds the limit of 5,000 data rows.');
        }

        foreach ($rows as $row) {
            $this->assertBoundedRow($row);
        }

        if ($rows === []) {
            return ['headers' => [], 'rows' => []];
        }

        $headerRow = array_shift($rows);
        $headers = $this->normaliseHeaders($headerRow, $rows);

        return [
            'headers' => array_values($headers),
            'rows' => $this->normaliseDataRows($rows, $headers),
        ];
    }

    /**
     * @param array<int|string, string> $headerRow
     * @param array<int, array<int|string, string>> $rows
     * @return array<int, string>
     */
    private function normaliseHeaders(array $headerRow, array $rows): array
    {
        $maxColumns = count($headerRow);

        foreach ($rows as $row) {
            foreach (array_keys($row) as $column) {
                if (is_int($column)) {
                    $maxColumns = max($maxColumns, $column + 1);
                }
            }
        }

        $headers = [];
        $seen = [];

        for ($index = 0; $index < $maxColumns; $index++) {
            $candidate = trim((string) ($headerRow[$index] ?? ''));
            $candidate = $candidate !== '' ? $candidate : 'Column ' . ($index + 1);
            $headers[$index] = $this->uniqueHeader($candidate, $seen);
        }

        return $headers;
    }

    /**
     * @param array<string, bool> $seen
     */
    private function uniqueHeader(string $candidate, array &$seen): string
    {
        $base = $candidate;
        $suffix = 2;

        while (isset($seen[strtolower($candidate)])) {
            $candidate = $base . ' ' . $suffix++;
        }

        $seen[strtolower($candidate)] = true;

        return $candidate;
    }

    /**
     * @param array<int, array<int|string, string>> $rows
     * @param array<int, string> $headers
     * @return array<int, array<string, string>>
     */
    private function normaliseDataRows(array $rows, array $headers): array
    {
        $normalisedRows = [];

        foreach ($rows as $row) {
            $record = [];

            foreach ($headers as $index => $header) {
                $record[$header] = trim((string) ($row[$index] ?? ''));
            }

            if ($this->rowHasValues($record)) {
                $normalisedRows[] = $record;
            }
        }

        return $normalisedRows;
    }

    /**
     * @param array<string, string> $record
     */
    private function rowHasValues(array $record): bool
    {
        foreach ($record as $value) {
            if ($value !== '') {
                return true;
            }
        }

        return false;
    }

    private function assertSourceSize(string $path): void
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('Unable to read the spreadsheet.');
        }

        $size = filesize($path);
        if ($size === false || $size > self::MAX_SOURCE_BYTES) {
            throw new RuntimeException('The spreadsheet exceeds the 5 MB processing limit.');
        }
    }

    private function assertSafeArchive(ZipArchive $zip): void
    {
        if ($zip->numFiles > self::MAX_ARCHIVE_ENTRIES) {
            throw new RuntimeException('The XLSX workbook contains too many archive entries.');
        }

        $totalBytes = 0;

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $entry = $zip->statIndex($index);

            if ($entry === false || ! isset($entry['size']) || ! is_numeric($entry['size'])) {
                throw new RuntimeException('The XLSX workbook contains an invalid archive entry.');
            }

            $entryBytes = (int) $entry['size'];
            if ($entryBytes > self::MAX_ARCHIVE_ENTRY_BYTES) {
                throw new RuntimeException('The XLSX workbook contains an archive entry above the 8 MB processing limit.');
            }

            $totalBytes += $entryBytes;
            if ($totalBytes > self::MAX_ARCHIVE_UNCOMPRESSED_BYTES) {
                throw new RuntimeException('The XLSX workbook exceeds the 20 MB uncompressed processing limit.');
            }
        }
    }

    /**
     * @param array<int|string, mixed> $row
     */
    private function assertBoundedRow(array $row): void
    {
        if (count($row) > self::MAX_COLUMNS) {
            throw new RuntimeException('The spreadsheet exceeds the limit of 100 columns.');
        }

        foreach (array_keys($row) as $key) {
            if (is_int($key) && $key >= self::MAX_COLUMNS) {
                throw new RuntimeException('The spreadsheet exceeds the limit of 100 columns.');
            }
        }

        foreach ($row as $value) {
            $this->assertCellSize((string) ($value ?? ''));
        }
    }

    private function assertCellSize(string $value): void
    {
        if (strlen($value) > self::MAX_CELL_BYTES) {
            throw new RuntimeException('The spreadsheet contains a cell larger than the 8 KB processing limit.');
        }
    }

    private function isCsvMime(?string $mimeType): bool
    {
        return in_array($mimeType, ['text/csv', 'application/csv', 'text/plain'], true);
    }
}

