<?php

namespace Tests\Unit;

use App\Services\CustomerImportMapper;
use Tests\TestCase;

class CustomerImportMapperTest extends TestCase
{
    public function test_common_customer_spreadsheet_columns_map_to_existing_zazu_customer_shape(): void
    {
        $result = app(CustomerImportMapper::class)->map([
            [
                'Customer Name' => 'ABC Catering',
                'Contact Person' => 'John Smith',
                'Mobile Number' => '0821234567',
                'Email Address' => 'john@example.com',
                'Billing Address' => 'Pretoria',
                'VAT Number' => '4123456789',
            ],
        ]);

        $this->assertSame([
            'name' => 'ABC Catering',
            'legal_name' => null,
            'registration_number' => null,
            'tax_number' => null,
            'vat_number' => '4123456789',
            'billing_address' => 'Pretoria',
            'notes' => null,
        ], $result['rows'][0]['customer']);

        $this->assertSame([
            'name' => 'John Smith',
            'phone' => '0821234567',
            'email' => 'john@example.com',
        ], $result['rows'][0]['primary_contact']);

        $this->assertSame([], $result['rows'][0]['issues']);
    }

    public function test_aliases_and_header_punctuation_are_normalised(): void
    {
        $result = app(CustomerImportMapper::class)->map([
            [
                'CLIENT' => 'Mokone Events',
                'Contact Name' => 'Thabo Mokone',
                'Cell No.' => '0712345678',
                'E-mail' => 'thabo@example.com',
                'Reg. No.' => '2026/123456/07',
            ],
        ]);

        $this->assertSame('Mokone Events', $result['rows'][0]['customer']['name']);
        $this->assertSame('Thabo Mokone', $result['rows'][0]['primary_contact']['name']);
        $this->assertSame('0712345678', $result['rows'][0]['primary_contact']['phone']);
        $this->assertSame('thabo@example.com', $result['rows'][0]['primary_contact']['email']);
        $this->assertSame('2026/123456/07', $result['rows'][0]['customer']['registration_number']);
        $this->assertSame([], $result['rows'][0]['issues']);
    }

    public function test_missing_required_values_are_reported_without_inventing_data(): void
    {
        $result = app(CustomerImportMapper::class)->map([
            [
                'Customer Name' => '',
                'Email' => 'not-an-email',
            ],
        ]);

        $this->assertNull($result['rows'][0]['customer']['name']);
        $this->assertNull($result['rows'][0]['primary_contact']['name']);
        $this->assertSame([
            'Customer name is required.',
            'Primary contact name is required.',
            'Primary contact email is not valid.',
        ], $result['rows'][0]['issues']);
    }

    public function test_preview_provides_row_numbers_status_summary_and_structured_issues(): void
    {
        $result = app(CustomerImportMapper::class)->preview([
            [
                'Customer Name' => 'ABC Catering',
                'Contact' => 'John Smith',
                'Email' => 'john@example.com',
            ],
            [
                'Customer Name' => '',
                'Contact' => 'Jane Smith',
                'Email' => 'bad-email',
            ],
        ]);

        $this->assertSame([
            'total' => 2,
            'ready' => 1,
            'needs_attention' => 1,
        ], $result['summary']);

        $this->assertSame(2, $result['rows'][0]['row_number']);
        $this->assertSame('ready', $result['rows'][0]['status']);
        $this->assertSame([], $result['rows'][0]['issues']);

        $this->assertSame(3, $result['rows'][1]['row_number']);
        $this->assertSame('needs_attention', $result['rows'][1]['status']);
        $this->assertSame([
            [
                'field' => 'customer.name',
                'severity' => 'error',
                'message' => 'Customer name is required.',
            ],
            [
                'field' => 'primary_contact.email',
                'severity' => 'error',
                'message' => 'Primary contact email is not valid.',
            ],
        ], $result['rows'][1]['issues']);
    }

    public function test_preview_is_read_only_and_preserves_source_values(): void
    {
        $result = app(CustomerImportMapper::class)->preview([
            [
                'Client' => 'ABC Catering',
                'Contact' => 'John Smith',
                'Mobile' => '0821234567',
            ],
        ]);

        $this->assertSame('ABC Catering', $result['rows'][0]['source']['Client']);
        $this->assertSame('John Smith', $result['rows'][0]['primary_contact']['name']);
        $this->assertSame('0821234567', $result['rows'][0]['primary_contact']['phone']);
    }

    public function test_mapper_does_not_write_to_the_database(): void
    {
        $result = app(CustomerImportMapper::class)->map([
            [
                'Client' => 'ABC Catering',
                'Contact' => 'John Smith',
            ],
        ]);

        $this->assertSame('ABC Catering', $result['rows'][0]['customer']['name']);
        $this->assertSame('John Smith', $result['rows'][0]['primary_contact']['name']);
        $this->assertSame([], $result['rows'][0]['issues']);
    }
}
