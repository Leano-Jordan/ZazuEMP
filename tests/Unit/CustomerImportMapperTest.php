<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Services\CustomerImportMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerImportMapperTest extends TestCase
{
    use RefreshDatabase;

    public function test_common_customer_spreadsheet_columns_map_to_existing_zazu_customer_shape(): void
    {
        $result = app(CustomerImportMapper::class)->map([
            ['Customer Name' => 'ABC Catering', 'Contact Person' => 'John Smith', 'Mobile Number' => '0821234567', 'Email Address' => 'john@example.com', 'Billing Address' => 'Pretoria', 'VAT Number' => '4123456789'],
        ]);

        $this->assertSame('ABC Catering', $result['rows'][0]['customer']['name']);
        $this->assertSame('John Smith', $result['rows'][0]['primary_contact']['name']);
        $this->assertSame([], $result['rows'][0]['issues']);
    }

    public function test_aliases_and_header_punctuation_are_normalised(): void
    {
        $result = app(CustomerImportMapper::class)->map([
            ['CLIENT' => 'Mokone Events', 'Contact Name' => 'Thabo Mokone', 'Cell No.' => '0712345678', 'E-mail' => 'thabo@example.com', 'Reg. No.' => '2026/123456/07'],
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
            ['Customer Name' => '', 'Email' => 'not-an-email'],
        ]);

        $this->assertNull($result['rows'][0]['customer']['name']);
        $this->assertNull($result['rows'][0]['primary_contact']['name']);
        $this->assertSame(['Customer name is required.', 'Primary contact name is required.', 'Primary contact email is not valid.'], $result['rows'][0]['issues']);
    }

    public function test_preview_exposes_new_existing_duplicate_and_needs_review_states(): void
    {
        $business = $this->business('Preview Business');

        Customer::create(['business_id' => $business->id, 'name' => 'Existing Customer']);
        Customer::create(['business_id' => $business->id, 'name' => 'Ambiguous Customer']);
        Customer::create(['business_id' => $business->id, 'name' => 'Ambiguous Customer']);

        $result = app(CustomerImportMapper::class)->preview([
            ['Customer Name' => 'New Customer', 'Contact' => 'John Smith'],
            ['Customer Name' => 'Existing Customer', 'Contact' => 'Jane Smith'],
            ['Customer Name' => 'Duplicate Customer', 'Contact' => 'Paul Smith'],
            ['Customer Name' => 'Duplicate Customer', 'Contact' => 'Paul Smith'],
            ['Customer Name' => 'Ambiguous Customer', 'Contact' => 'Anne Smith'],
        ], $business);

        $this->assertSame([
            'total' => 5,
            'ready' => 5,
            'needs_attention' => 0,
            'new' => 1,
            'existing' => 1,
            'duplicate' => 2,
            'needs_review' => 1,
        ], $result['summary']);

        $this->assertSame('new', $result['rows'][0]['match']['status']);
        $this->assertSame('existing', $result['rows'][1]['match']['status']);
        $this->assertSame('duplicate', $result['rows'][2]['match']['status']);
        $this->assertSame(4, $result['rows'][2]['match']['source_duplicate_of_row']);
        $this->assertSame('needs_review', $result['rows'][4]['match']['status']);
        $this->assertNull($result['rows'][4]['match']['customer_id']);
    }

    public function test_preview_remains_read_only_and_preserves_source_values(): void
    {
        $business = $this->business('Preview Business');

        $result = app(CustomerImportMapper::class)->preview([
            ['Client' => 'ABC Catering', 'Contact' => 'John Smith', 'Mobile' => '0821234567'],
        ], $business);

        $this->assertSame('ABC Catering', $result['rows'][0]['source']['Client']);
        $this->assertSame('John Smith', $result['rows'][0]['primary_contact']['name']);
        $this->assertSame('0821234567', $result['rows'][0]['primary_contact']['phone']);
        $this->assertSame('new', $result['rows'][0]['match']['status']);
    }

    public function test_mapper_does_not_write_to_the_database(): void
    {
        $result = app(CustomerImportMapper::class)->map([
            ['Client' => 'ABC Catering', 'Contact' => 'John Smith'],
        ]);

        $this->assertSame('ABC Catering', $result['rows'][0]['customer']['name']);
        $this->assertSame('John Smith', $result['rows'][0]['primary_contact']['name']);
        $this->assertSame([], $result['rows'][0]['issues']);
    }

    private function business(string $name): Business
    {
        return Business::create([
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)) . '-' . uniqid(),
        ]);
    }
}
