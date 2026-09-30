<?php

namespace App\Services;

class CustomerImportMapper
{
    /**
     * Map spreadsheet rows to the existing Customer + primary contact shape.
     *
     * This layer is intentionally read-only: it never creates or updates
     * business records and it never silently invents missing values.
     *
     * @param array<int, array<string, string>> $rows
     * @return array{rows: array<int, array{source: array<string, string>, customer: array<string, ?string>, primary_contact: array<string, ?string>, issues: array<int, string>}>, headers: array<int, string>}
     */
    public function map(array $rows): array
    {
        return [
            'headers' => $this->headers($rows),
            'rows' => array_map(fn (array $row): array => $this->mapRow($row), $rows),
        ];
    }

    /**
     * @param array<string, string> $row
     * @return array{source: array<string, string>, customer: array<string, ?string>, primary_contact: array<string, ?string>, issues: array<int, string>}
     */
    public function mapRow(array $row): array
    {
        $columns = [];

        foreach ($row as $header => $value) {
            $columns[$this->normaliseHeader($header)] = [
                'header' => $header,
                'value' => trim((string) $value),
            ];
        }

        $customer = [
            'name' => $this->value($columns, [
                'customer name',
                'customer',
                'client name',
                'client',
                'company name',
                'company',
                'business name',
            ]),
            'legal_name' => $this->value($columns, ['legal name', 'registered name']),
            'registration_number' => $this->value($columns, [
                'registration number',
                'registration no',
                'company registration number',
                'company reg number',
                'reg number',
                'reg no',
            ]),
            'tax_number' => $this->value($columns, [
                'tax number',
                'tax no',
                'tax reference',
            ]),
            'vat_number' => $this->value($columns, [
                'vat number',
                'vat no',
                'vat registration number',
                'vat registration',
            ]),
            'billing_address' => $this->value($columns, [
                'billing address',
                'billing address 1',
                'address',
                'physical address',
                'postal address',
            ]),
            'notes' => $this->value($columns, ['notes', 'note', 'comments', 'comment']),
        ];

        $primaryContact = [
            'name' => $this->value($columns, [
                'primary contact name',
                'contact name',
                'contact person',
                'contact',
            ]),
            'phone' => $this->value($columns, [
                'primary contact phone',
                'contact phone',
                'phone',
                'phone number',
                'mobile',
                'mobile number',
                'cell',
                'cell number',
                'telephone',
                'tel',
            ]),
            'email' => $this->value($columns, [
                'primary contact email',
                'contact email',
                'email',
                'email address',
            ]),
        ];

        $issues = [];

        if (!$customer['name']) {
            $issues[] = 'Customer name is required.';
        }

        if (!$primaryContact['name']) {
            $issues[] = 'Primary contact name is required.';
        }

        if ($primaryContact['email'] !== null && filter_var($primaryContact['email'], FILTER_VALIDATE_EMAIL) === false) {
            $issues[] = 'Primary contact email is not valid.';
        }

        return [
            'source' => $row,
            'customer' => $customer,
            'primary_contact' => $primaryContact,
            'issues' => $issues,
        ];
    }

    /**
     * @param array<int, array<string, string>> $rows
     * @return array<int, string>
     */
    private function headers(array $rows): array
    {
        $headers = [];

        foreach ($rows as $row) {
            foreach (array_keys($row) as $header) {
                if (!in_array($header, $headers, true)) {
                    $headers[] = $header;
                }
            }
        }

        return $headers;
    }

    /**
     * @param array<string, array{header: string, value: string}> $columns
     * @param array<int, string> $aliases
     */
    private function value(array $columns, array $aliases): ?string
    {
        foreach ($aliases as $alias) {
            $key = $this->normaliseHeader($alias);

            if (isset($columns[$key]) && $columns[$key]['value'] !== '') {
                return $columns[$key]['value'];
            }
        }

        return null;
    }

    private function normaliseHeader(string $header): string
    {
        $header = strtolower(trim($header));
        $header = preg_replace('/[^a-z0-9]+/', ' ', $header) ?? $header;

        return trim(preg_replace('/\s+/', ' ', $header) ?? $header);
    }
}
