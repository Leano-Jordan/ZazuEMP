<?php

namespace Tests\Unit;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Customer;
use App\Services\CustomerImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class CustomerImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_explicitly_approved_new_rows_are_created(): void
    {
        $business = $this->business('Import Business');

        Customer::create([
            'business_id' => $business->id,
            'name' => 'Existing Customer',
        ]);

        $result = app(CustomerImportService::class)->import(
            $business,
            [
                ['Customer Name' => 'New Customer', 'Contact' => 'John Smith', 'Email' => 'john@example.com'],
                ['Customer Name' => 'Existing Customer', 'Contact' => 'Jane Smith'],
            ],
            [2 => 'create'],
        );

        $this->assertSame(['created' => 1, 'skipped' => 0], $result);
        $this->assertDatabaseHas('customers', [
            'business_id' => $business->id,
            'name' => 'New Customer',
        ]);
        $this->assertDatabaseHas('customer_contacts', [
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'is_primary' => 1,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $business->id,
            'action' => 'customers.imported',
        ]);
    }

    public function test_existing_rows_can_be_explicitly_skipped_without_creating_duplicates(): void
    {
        $business = $this->business('Existing Business');

        $existing = Customer::create([
            'business_id' => $business->id,
            'name' => 'Existing Customer',
        ]);

        $result = app(CustomerImportService::class)->import(
            $business,
            [
                ['Customer Name' => 'Existing Customer', 'Contact' => 'Jane Smith'],
            ],
            [2 => 'skip'],
        );

        $this->assertSame(['created' => 0, 'skipped' => 1], $result);
        $this->assertSame(1, Customer::where('business_id', $business->id)->count());
        $this->assertDatabaseHas('customers', [
            'id' => $existing->id,
            'name' => 'Existing Customer',
        ]);
    }

    public function test_duplicate_or_needs_review_rows_are_blocked_before_any_write(): void
    {
        $business = $this->business('Blocked Business');

        Customer::create([
            'business_id' => $business->id,
            'name' => 'Ambiguous Customer',
        ]);
        Customer::create([
            'business_id' => $business->id,
            'name' => 'Ambiguous Customer',
        ]);

        try {
            app(CustomerImportService::class)->import(
                $business,
                [
                    ['Customer Name' => 'Safe New Customer', 'Contact' => 'John Smith'],
                    ['Customer Name' => 'Ambiguous Customer', 'Contact' => 'Jane Smith'],
                ],
                [2 => 'create'],
            );

            $this->fail('Expected the ambiguous row to block the import.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('requires resolution', $e->getMessage());
        }

        $this->assertDatabaseMissing('customers', [
            'business_id' => $business->id,
            'name' => 'Safe New Customer',
        ]);
    }

    public function test_existing_match_cannot_be_created_even_when_create_is_requested(): void
    {
        $business = $this->business('Race Business');

        Customer::create([
            'business_id' => $business->id,
            'name' => 'Race Customer',
        ]);

        try {
            app(CustomerImportService::class)->import(
                $business,
                [
                    ['Customer Name' => 'Race Customer', 'Contact' => 'John Smith'],
                ],
                [2 => 'create'],
            );

            $this->fail('Expected an existing customer match to block creation.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('is not a new customer', $e->getMessage());
        }

        $this->assertSame(1, Customer::where('business_id', $business->id)->count());
    }

    private function business(string $name): Business
    {
        return Business::create([
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)) . '-' . uniqid(),
        ]);
    }
}
