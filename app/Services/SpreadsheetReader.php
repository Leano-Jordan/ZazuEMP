<?php

namespace AppServices;

use IlluminateSupportStr;
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * XLSX parsing necessarily coordinates ZIP/XML package structure,
     * workbook relationships, shared strings and worksheet cells in one
     * bounded read operation.
     *
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
            $workbookXml = $zip->getFromName('xl/workbook.xml');
            $relationshipsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

            if ($workbookXml === false || $relationshipsXml === false) {
                throw new RuntimeException('The XLSX workbook structure is invalid.');
            }

            $workbook = $this->xml($workbookXml);
            $relationships = $this->xml($relationshipsXml);
            $sharedStrings = $this->readSharedStrings($zip);

            $relationshipMap = [];
            foreach ($relationships->Relationship as $relationship) {
                $relationshipMap[(string) $relationship['Id']] = (string) $relationship['Target'];
            }

            $sheets = [];

            foreach ($workbook->sheets->sheet as $sheet) {
                $name = trim((string) $sheet['name']) ?: 'Sheet ' . (count($sheets) + 1);
                $relationshipId = (string) $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')->id;
                $target = $relationshipMap[$relationshipId] ?? null;

                if (!$target) {
                    continue;
                }

                $target = ltrim(str_replace('\\', '/', $target), '/');
                $sheetPath = Str::startsWith($target, 'xl/') ? $target : 'xl/' . $target;
                $sheetXml = $zip->getFromName($sheetPath);

                if ($sheetXml === false) {
                    continue;
                }

                $sheetData = $this->xml($sheetXml);
                $rawRows = [];

                foreach ($sheetData->sheetData->row as $row) {
                    $values = [];

                    foreach ($row->c as $cell) {
                        $reference = (string) $cell['r'];
                        $column = $this->columnIndex($reference);
                        $type = (string) $cell['t'];
                        $value = (string) ($cell->v ?? '');

                        if ($type === 's') {
                            $value = $sharedStrings[(int) $value] ?? '';
                        } elseif ($type === 'inlineStr') {
                            $value = $this->inlineString($cell);
                        } elseif ($type === 'b') {
                            $value = $value === '1' ? 'TRUE' : 'FALSE';
                        }

                        $values[$column] = trim($value);
                    }

                    if ($values !== []) {
                        $rawRows[] = $values;
                    }
                }

                $sheets[$name] = $this->normaliseRows($rawRows);
            }

            if ($sheets === []) {
                throw new RuntimeException('The XLSX workbook contains no readable sheets.');
            }

            return $sheets;
        } finally {
            $zip->close();
        }
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * Row normalization is a compact parser boundary handling variable
     * spreadsheet columns, duplicate headers and sparse rows.
     *
     * @param array<int, array<int|string, string>> $rows
     * @return array{headers: array<int, string>, rows: array<int, array<string, string>>}
     */
    private function normaliseRows(array $rows): array
    {
        if ($rows === []) {
            return ['headers' => [], 'rows' => []];
        }

        $headerRow = array_shift($rows);
        $maxColumns = 0;

        foreach ($rows as $row) {
            $maxColumns = max($maxColumns, count($row));
            if ($row !== []) {
                $maxColumns = max($maxColumns, max(array_keys($row)) + 1);
            }
        }

        $maxColumns = max($maxColumns, count($headerRow));
        $headers = [];
        $seen = [];

        for ($index = 0; $index < $maxColumns; $index++) {
            $header = trim((string) ($headerRow[$index] ?? ''));
            $header = $header !== '' ? $header : 'Column ' . ($index + 1);

            $base = $header;
            $suffix = 2;
            while (isset($seen[strtolower($header)])) {
                $header = $base . ' ' . $suffix++;
            }

            $seen[strtolower($header)] = true;
            $headers[$index] = $header;
        }

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

        return ['headers' => array_values($headers), 'rows' => $normalisedRows];
    }

    private function isCsvMime(?string $mimeType): bool
    {
        return in_array($mimeType, ['text/csv', 'application/csv', 'text/plain'], true);
    }
}

