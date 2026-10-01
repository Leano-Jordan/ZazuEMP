<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Customer;
use Illuminate\Support\Collection;

class CustomerImportMatcher
{
    /**
     * Match already-mapped spreadsheet rows against customers in one business.
     *
     * This is read-only. It never creates, updates, deletes, or merges records.
     *
     * @param array<int, array{source: array<string, string>, customer: array<string, ?string>, primary_contact: array<string, ?string>, issues: array<int, string>}> $rows
     * @return array<int, array{match: array{status: string, customer_id: int|null, matched_by: array<int, string>, source_duplicate_of_row: int|null, message: string}}>
     */
    public function match(Business $business, array $rows): array
    {
        $customers = Customer::query()
            ->where('business_id', $business->id)
            ->with('contacts')
            ->get();

        $indexes = $this->buildIndexes($customers);
        $sourceKeys = $this->sourceKeys($rows);

        return array_map(
            fn (array $row, int $index): array => [
                'match' => $this->matchRow($row, $index, $indexes, $sourceKeys),
            ],
            $rows,
            array_keys($rows)
        );
    }

    /**
     * @param Collection<int, Customer> $customers
     * @return array<string, array<string, array<int, int>>>
     */
    private function buildIndexes(Collection $customers): array
    {
        $indexes = [
            'name' => [],
            'registration_number' => [],
            'tax_number' => [],
            'vat_number' => [],
            'email' => [],
            'phone' => [],
        ];

        foreach ($customers as $customer) {
            $this->index($indexes['name'], $this->normaliseText($customer->name), $customer->id);

            foreach (['registration_number', 'tax_number', 'vat_number'] as $field) {
                $value = $this->normaliseIdentifier($customer->{$field});

                if ($value !== '') {
                    $this->index($indexes[$field], $value, $customer->id);
                }
            }

            foreach ($customer->contacts as $contact) {
                $email = $this->normaliseEmail($contact->email);
                $phone = $this->normalisePhone($contact->phone);

                if ($email !== '') {
                    $this->index($indexes['email'], $email, $customer->id);
                }

                if ($phone !== '') {
                    $this->index($indexes['phone'], $phone, $customer->id);
                }
            }
        }

        return $indexes;
    }

    /**
     * @param array<string, array<int, int>> $index
     */
    private function index(array &$index, string $key, int $customerId): void
    {
        if ($key === '') {
            return;
        }

        $index[$key] ??= [];

        if (!in_array($customerId, $index[$key], true)) {
            $index[$key][] = $customerId;
        }
    }

    /**
     * @param array<int, array{customer: array<string, ?string>, primary_contact: array<string, ?string>}> $rows
     * @return array<string, array<int, int>>
     */
    private function sourceKeys(array $rows): array
    {
        $keys = [];

        foreach ($rows as $index => $row) {
            $customer = $row['customer'];
            $contact = $row['primary_contact'];

            foreach ([
                'name' => $this->normaliseText($customer['name']),
                'registration_number' => $this->normaliseIdentifier($customer['registration_number']),
                'tax_number' => $this->normaliseIdentifier($customer['tax_number']),
                'vat_number' => $this->normaliseIdentifier($customer['vat_number']),
                'email' => $this->normaliseEmail($contact['email']),
                'phone' => $this->normalisePhone($contact['phone']),
            ] as $field => $value) {
                if ($value === '') {
                    continue;
                }

                $key = $field . ':' . $value;
                $keys[$key] ??= [];
                $keys[$key][] = $index + 2;
            }
        }

        return $keys;
    }

    /**
     * @param array{customer: array<string, ?string>, primary_contact: array<string, ?string>} $row
     * @param array<string, array<string, array<int, int>>> $indexes
     * @param array<string, array<int, int>> $sourceKeys
     * @return array{status: string, customer_id: int|null, matched_by: array<int, string>, source_duplicate_of_row: int|null, message: string}
     */
    private function matchRow(array $row, int $index, array $indexes, array $sourceKeys): array
    {
        $fields = $this->rowFields($row);
        $matches = $this->findCustomerMatches($fields, $indexes);
        $duplicateRows = $this->findDuplicateRows($fields, $index, $sourceKeys);

        return $this->buildMatchResult($matches, $duplicateRows);
    }

    /**
     * @param array{customer: array<string, ?string>, primary_contact: array<string, ?string>} $row
     * @return array<string, string>
     */
    private function rowFields(array $row): array
    {
        $customer = $row['customer'];
        $contact = $row['primary_contact'];

        return [
            'name' => $this->normaliseText($customer['name']),
            'registration_number' => $this->normaliseIdentifier($customer['registration_number']),
            'tax_number' => $this->normaliseIdentifier($customer['tax_number']),
            'vat_number' => $this->normaliseIdentifier($customer['vat_number']),
            'email' => $this->normaliseEmail($contact['email']),
            'phone' => $this->normalisePhone($contact['phone']),
        ];
    }

    /**
     * @param array<string, string> $fields
     * @param array<string, array<string, array<int, int>>> $indexes
     * @return array<int, array<int, string>>
     */
    private function findCustomerMatches(array $fields, array $indexes): array
    {
        $matches = [];

        foreach ($fields as $field => $value) {
            if ($value === '') {
                continue;
            }

            foreach ($indexes[$field][$value] ?? [] as $customerId) {
                $matches[$customerId] ??= [];
                $matches[$customerId][] = $field;
            }
        }

        return $matches;
    }

    /**
     * @param array<string, string> $fields
     * @param array<string, array<int, int>> $sourceKeys
     * @return array<int, int>
     */
    private function findDuplicateRows(array $fields, int $index, array $sourceKeys): array
    {
        $duplicateRows = [];

        foreach ($fields as $field => $value) {
            if ($value === '') {
                continue;
            }

            foreach ($sourceKeys[$field . ':' . $value] ?? [] as $rowNumber) {
                if ($rowNumber !== $index + 2) {
                    $duplicateRows[] = $rowNumber;
                }
            }
        }

        return array_values(array_unique($duplicateRows));
    }

    /**
     * @param array<int, array<int, string>> $matches
     * @param array<int, int> $duplicateRows
     * @return array{status: string, customer_id: int|null, matched_by: array<int, string>, source_duplicate_of_row: int|null, message: string}
     */
    private function buildMatchResult(array $matches, array $duplicateRows): array
    {
        $customerIds = array_keys($matches);

        if (count($customerIds) > 1) {
            return [
                'status' => 'ambiguous',
                'customer_id' => null,
                'matched_by' => [],
                'source_duplicate_of_row' => $duplicateRows[0] ?? null,
                'message' => 'The spreadsheet row matches more than one existing customer. Nothing may be imported automatically.',
            ];
        }

        if ($duplicateRows !== []) {
            $hasSingleMatch = count($customerIds) === 1;

            return [
                'status' => 'duplicate',
                'customer_id' => $hasSingleMatch ? (int) $customerIds[0] : null,
                'matched_by' => $hasSingleMatch ? $matches[$customerIds[0]] : [],
                'source_duplicate_of_row' => $duplicateRows[0],
                'message' => 'The same customer information appears on another spreadsheet row. Review the duplicate before importing.',
            ];
        }

        if ($customerIds === []) {
            return [
                'status' => 'new',
                'customer_id' => null,
                'matched_by' => [],
                'source_duplicate_of_row' => null,
                'message' => 'No existing customer matched these details.',
            ];
        }

        $customerId = (int) $customerIds[0];

        return [
            'status' => 'matched',
            'customer_id' => $customerId,
            'matched_by' => $matches[$customerId],
            'source_duplicate_of_row' => null,
            'message' => 'Existing customer matched using exact business record details.',
        ];
    }

    private function normaliseText(?string $value): string
    {
        return preg_replace('/[^a-z0-9]+/i', '', strtolower(trim((string) $value))) ?? '';
    }

    private function normaliseIdentifier(?string $value): string
    {
        return preg_replace('/[^a-z0-9]+/i', '', strtolower(trim((string) $value))) ?? '';
    }

    private function normaliseEmail(?string $value): string
    {
        return strtolower(trim((string) $value));
    }

    private function normalisePhone(?string $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?? '';
    }
}
