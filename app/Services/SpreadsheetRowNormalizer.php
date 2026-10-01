<?php

namespace AppServices;

class SpreadsheetRowNormalizer
{
    /**
     * @param array<int, array<int|string, string>> $rows
     * @return array{headers: array<int, string>, rows: array<int, array<string, string>>}
     */
    public function normalise(array $rows): array
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
}
