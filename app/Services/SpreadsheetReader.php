<?php

namespace App\Services;

use Illuminate\Support\Str;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class SpreadsheetReader
{
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
     * @return array<string, array{headers: array<int, string>, rows: array<int, array<string, string>>}>
     */
    private function readXlsx(string $path): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('XLSX import requires the PHP ZIP extension.');
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('Unable to open the XLSX workbook.');
        }

        try {
            return $this->readXlsxWorkbook($zip);
        } finally {
            $zip->close();
        }
    }

    /**
     * @return array<string, array{headers: array<int, string>, rows: array<int, array<string, string>>}>
     */
    private function readXlsxWorkbook(ZipArchive $zip): array
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relationshipsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookXml === false || $relationshipsXml === false) {
            throw new RuntimeException('The XLSX workbook structure is invalid.');
        }

        $workbook = $this->xml($workbookXml);
        $relationships = $this->xml($relationshipsXml);
        $sharedStrings = $this->readSharedStrings($zip);
        $relationshipMap = $this->relationshipMap($relationships);
        $sheets = [];

        foreach ($workbook->sheets->sheet as $sheet) {
            $result = $this->readXlsxSheet($zip, $sheet, $relationshipMap, $sharedStrings, count($sheets) + 1);

            if ($result !== null) {
                $sheets[$result['name']] = $result['data'];
            }
        }

        if ($sheets === []) {
            throw new RuntimeException('The XLSX workbook contains no readable sheets.');
        }

        return $sheets;
    }

    /**
     * @return array<string, string>
     */
    private function relationshipMap(SimpleXMLElement $relationships): array
    {
        $map = [];

        foreach ($relationships->Relationship as $relationship) {
            $map[(string) $relationship['Id']] = (string) $relationship['Target'];
        }

        return $map;
    }

    /**
     * @param array<string, string> $relationshipMap
     * @param array<int, string> $sharedStrings
     * @return array{name: string, data: array{headers: array<int, string>, rows: array<int, array<string, string>>}}|null
     */
    private function readXlsxSheet(
        ZipArchive $zip,
        SimpleXMLElement $sheet,
        array $relationshipMap,
        array $sharedStrings,
        int $sheetNumber,
    ): ?array {
        $name = trim((string) $sheet['name']) ?: 'Sheet ' . $sheetNumber;
        $relationshipId = (string) $sheet
            ->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')
            ->id;
        $target = $relationshipMap[$relationshipId] ?? null;

        if (!$target) {
            return null;
        }

        $target = ltrim(str_replace('\\', '/', $target), '/');
        $sheetPath = Str::startsWith($target, 'xl/') ? $target : 'xl/' . $target;
        $sheetXml = $zip->getFromName($sheetPath);

        if ($sheetXml === false) {
            return null;
        }

        $sheetData = $this->xml($sheetXml);
        $rawRows = [];

        foreach ($sheetData->sheetData->row as $row) {
            $values = $this->readXlsxRow($row, $sharedStrings);

            if ($values !== []) {
                $rawRows[] = $values;
            }
        }

        return ['name' => $name, 'data' => $this->normaliseRows($rawRows)];
    }

    /**
     * @param array<int, string> $sharedStrings
     * @return array<int, string>
     */
    private function readXlsxRow(SimpleXMLElement $row, array $sharedStrings): array
    {
        $values = [];

        foreach ($row->c as $cell) {
            $values[$this->columnIndex((string) $cell['r'])] = $this->readXlsxCell($cell, $sharedStrings);
        }

        return $values;
    }

    /**
     * @param array<int, string> $sharedStrings
     */
    private function readXlsxCell(SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) $cell['t'];
        $value = (string) ($cell->v ?? '');

        if ($type === 's') {
            return trim($sharedStrings[(int) $value] ?? '');
        }

        if ($type === 'inlineStr') {
            return $this->inlineString($cell);
        }

        if ($type === 'b') {
            return $value === '1' ? 'TRUE' : 'FALSE';
        }

        return trim($value);
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
            $strings[] = trim($this->xmlText($item));
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
     * @param array<int, array<int|string, string>> $rows
     * @return array{headers: array<int, string>, rows: array<int, array<string, string>>}
     */
    private function normaliseRows(array $rows): array
    {
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
        $maxColumns = max(count($headerRow), $this->maxRowColumnCount($rows));
        $headers = [];
        $seen = [];

        for ($index = 0; $index < $maxColumns; $index++) {
            $base = trim((string) ($headerRow[$index] ?? '')) ?: 'Column ' . ($index + 1);
            $header = $base;
            $suffix = 2;

            while (isset($seen[strtolower($header)])) {
                $header = $base . ' ' . $suffix++;
            }

            $seen[strtolower($header)] = true;
            $headers[$index] = $header;
        }

        return $headers;
    }

    /**
     * @param array<int, array<int|string, string>> $rows
     */
    private function maxRowColumnCount(array $rows): int
    {
        $maxColumns = 0;

        foreach ($rows as $row) {
            $maxColumns = max($maxColumns, count($row));

            if ($row !== []) {
                $maxColumns = max($maxColumns, max(array_keys($row)) + 1);
            }
        }

        return $maxColumns;
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
            $hasValue = false;

            foreach ($headers as $index => $header) {
                $value = trim((string) ($row[$index] ?? ''));
                $record[$header] = $value;
                $hasValue = $hasValue || $value !== '';
            }

            if ($hasValue) {
                $normalisedRows[] = $record;
            }
        }

        return $normalisedRows;
    }

    private function isCsvMime(?string $mimeType): bool
    {
        return in_array($mimeType, ['text/csv', 'application/csv', 'text/plain'], true);
    }
}
