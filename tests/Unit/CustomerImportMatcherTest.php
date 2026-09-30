<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Services\CustomerImportMapper;
use App\Services\CustomerImportMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerImportMatcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_customer_is_not_matched(): void
    {
        $business = $this->business('Matcher Business');
        $rows = $this->rows([
            ['Customer Name' => 'New Catering', 'Contact' => 'Jane Smith', 'Email' => 'jane@example.com'],
        ]);

        $result = app(CustomerImportMatcher::class)->match($business, $rows);

        $this->assertSame('new', $result[0]['match']['status']);
        $this->assertNull($result[0]['match']['customer_id']);
    }

    public function test_exact_customer_name_matches_within_same_business(): void
    {
        $business = $this->business('Matcher Business');
        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'ABC Catering',
        ]);

        $rows = $this->rows([
            ['Customer Name' => 'ABC Catering', 'Contact' => 'John Smith'],
        ]);

        $result = app(CustomerImportMatcher::class)->match($business, $rows);

        $this->assertSame('matched', $result[0]['match']['status']);
        $this->assertSame($customer->id, $result[0]['match']['customer_id']);
        $this->assertContains('name', $result[0]['match']['matched_by']);
    }

    public function test_identifier_and_contact_email_can_match_existing_customer(): void
    {
        $business = $this->business('Matcher Business');
        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Existing Customer',
            'vat_number' => '4123/456/789',
        ]);

        CustomerContact::create([
            'customer_id' => $customer->id,
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'is_primary' => true,
        ]);

        $rows = $this->rows([
            [
                'Customer Name' => 'Different Display Name',
                'Contact' => 'Jane Smith',
                'Email' => 'jane@example.com',
                'VAT Number' => '4123456789',
            ],
        ]);

        $result = app(CustomerImportMatcher::class)->match($business, $rows);

        $this->assertSame('matched', $result[0]['match']['status']);
        $this->assertSame($customer->id, $result[0]['match']['customer_id']);
        $this->assertContains('vat_number', $result[0]['match']['matched_by']);
        $this->assertContains('email', $result[0]['match']['matched_by']);
    }

    public function test_multiple_existing_matches_are_ambiguous(): void
    {
        $business = $this->business('Matcher Business');

        Customer::create(['business_id' => $business->id, 'name' => 'ABC Catering']);
        Customer::create(['business_id' => $business->id, 'name' => 'ABC Catering']);

        $rows = $this->rows([
            ['Customer Name' => 'ABC Catering', 'Contact' => 'John Smith'],
        ]);

        $result = app(CustomerImportMatcher::class)->match($business, $rows);

        $this->assertSame('ambiguous', $result[0]['match']['status']);
        $this->assertNull($result[0]['match']['customer_id']);
    }

    public function test_customer_from_another_business_is_not_a_match(): void
    {
        $business = $this->business('Matcher Business');
        $otherBusiness = $this->business('Other Business');

        Customer::create([
            'business_id' => $otherBusiness->id,
            'name' => 'ABC Catering',
        ]);

        $rows = $this->rows([
            ['Customer Name' => 'ABC Catering', 'Contact' => 'John Smith'],
        ]);

        $result = app(CustomerImportMatcher::class)->match($business, $rows);

        $this->assertSame('new', $result[0]['match']['status']);
    }

    public function test_duplicate_spreadsheet_rows_are_blocked(): void
    {
        $business = $this->business('Matcher Business');
        $rows = $this->rows([
            ['Customer Name' => 'ABC Catering', 'Contact' => 'John Smith', 'Email' => 'john@example.com'],
            ['Customer Name' => 'ABC Catering', 'Contact' => 'John Smith', 'Email' => 'john@example.com'],
        ]);

        $result = app(CustomerImportMatcher::class)->match($business, $rows);

        $this->assertSame('duplicate', $result[0]['match']['status']);
        $this->assertSame('duplicate', $result[1]['match']['status']);
        $this->assertSame(3, $result[0]['match']['source_duplicate_of_row']);
        $this->assertSame(2, $result[1]['match']['source_duplicate_of_row']);
    }

    /**
     * @param array<int, array<string, string>> $sourceRows
     * @return array<int, array{source: array<string, string>, customer: array<string, ?string>, primary_contact: array<string, ?string>, issues: array<int, string>}>
     */
    private function rows(array $sourceRows): array
    {
        return app(CustomerImportMapper::class)->map($sourceRows)['rows'];
    }

    private function business(string $name): Business
    {
        return Business::create([
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)) . '-' . uniqid(),
        ]);
    }
}
