<?php

namespace AppServices;

use IlluminateSupportStr;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class XlsxSpreadsheetReader
{
    public function __construct(
        private readonly SpreadsheetRowNormalizer $normalizer,
    ) {
    }

    /**
     * @return array<string, array{headers: array<int, string>, rows: array<int, array<string, string>>}>
     */
    public function read(string $path): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('XLSX import requires the PHP ZIP extension.');
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new RuntimeException('Unable to open the XLSX workbook.');
        }

        try {
            return $this->readWorkbook($zip);
        } finally {
            $zip->close();
        }
    }

    /**
     * @return array<string, array{headers: array<int, string>, rows: array<int, array<string, string>>}>
     */
    private function readWorkbook(ZipArchive $zip): array
    {
        $workbook = $this->xmlFromArchive($zip, 'xl/workbook.xml');
        $relationships = $this->xmlFromArchive($zip, 'xl/_rels/workbook.xml.rels');
        $sharedStrings = $this->readSharedStrings($zip);
        $relationshipMap = $this->relationshipMap($relationships);
        $sheets = [];

        foreach ($workbook->sheets->sheet as $sheet) {
            $result = $this->readSheet(
                $zip,
                $sheet,
                $relationshipMap,
                $sharedStrings,
                count($sheets) + 1,
            );

            if ($result !== null) {
                $sheets[$result['name']] = $result['data'];
            }
        }

        if ($sheets === []) {
            throw new RuntimeException('The XLSX workbook contains no readable sheets.');
        }

        return $sheets;
    }

    private function xmlFromArchive(ZipArchive $zip, string $path): SimpleXMLElement
    {
        $contents = $zip->getFromName($path);

        if ($contents === false) {
            throw new RuntimeException('The XLSX workbook structure is invalid.');
        }

        return $this->xml($contents);
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
    private function readSheet(
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

        $target = ltrim(str_replace('\', '/', $target), '/');
        $sheetPath = Str::startsWith($target, 'xl/') ? $target : 'xl/' . $target;
        $sheetXml = $zip->getFromName($sheetPath);

        if ($sheetXml === false) {
            return null;
        }

        $sheetData = $this->xml($sheetXml);
        $rawRows = [];

        foreach ($sheetData->sheetData->row as $row) {
            $values = $this->readRow($row, $sharedStrings);

            if ($values !== []) {
                $rawRows[] = $values;
            }
        }

        return ['name' => $name, 'data' => $this->normalizer->normalise($rawRows)];
    }

    /**
     * @param array<int, string> $sharedStrings
     * @return array<int, string>
     */
    private function readRow(SimpleXMLElement $row, array $sharedStrings): array
    {
        $values = [];

        foreach ($row->c as $cell) {
            $values[$this->columnIndex((string) $cell['r'])] = $this->readCell($cell, $sharedStrings);
        }

        return $values;
    }

    /**
     * @param array<int, string> $sharedStrings
     */
    private function readCell(SimpleXMLElement $cell, array $sharedStrings): string
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
}
