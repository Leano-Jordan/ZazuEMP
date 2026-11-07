<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessSpreadsheetExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_export_only_their_business_customer_data_as_safe_csv(): void
    {
        $this->signInAsOwner();
        $business = auth()->user()->businesses()->firstOrFail();

        Customer::create([
            'business_id' => $business->id,
            'name' => '=HYPERLINK("https://example.invalid","Open")',
            'legal_name' => 'Export Test Customer',
            'billing_address' => 'Pretoria',
            'notes' => '+SUM(1,1)',
        ]);

        $foreignBusiness = Business::create([
            'name' => 'Foreign Export Business',
            'slug' => 'foreign-export-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        DB::table('customers')->insert([
            'business_id' => $foreignBusiness->id,
            'name' => 'FOREIGN TENANT CUSTOMER',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get(route('exports.download', ['resource' => 'customers']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $cacheControl = (string) $response->headers->get('Cache-Control');
        foreach (['private', 'no-store', 'no-cache', 'must-revalidate', 'max-age=0'] as $directive) {
            $this->assertStringContainsString($directive, $cacheControl);
        }

        $csv = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString("'=HYPERLINK(", $csv);
        $this->assertStringContainsString("'+SUM(1,1)", $csv);
        $this->assertStringContainsString('Export Test Customer', $csv);
        $this->assertStringNotContainsString('FOREIGN TENANT CUSTOMER', $csv);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $business->id,
            'action' => 'business.data_exported',
        ]);
    }

    public function test_owner_export_page_lists_the_available_business_datasets(): void
    {
        $this->signInAsOwner();

        $this->get(route('exports.index'))
            ->assertOk()
            ->assertSee('Export business data')
            ->assertSee('Customers')
            ->assertSee('Events and jobs')
            ->assertSee('Customer contacts')
            ->assertSee('Products and services')
            ->assertSee('Assets and equipment')
            ->assertSee('Event requirements')
            ->assertSee('Event preparation')
            ->assertSee('Quote line items')
            ->assertSee('Invoice line items')
            ->assertSee('Quotes')
            ->assertSee('Invoices')
            ->assertSee('Purchase order lines')
            ->assertSee('Receiving records')
            ->assertSee('Tax rates')
            ->assertSee('Stock movements')
            ->assertSee('Event costs')
            ->assertSee('Travel calculations');
    }

    public function test_every_export_resource_streams_a_csv_for_an_empty_business(): void
    {
        $this->signInAsOwner();

        $resources = [
            'customers',
            'events',
            'customer_contacts',
            'business_capabilities',
            'assets',
            'asset_allocations',
            'event_requirements',
            'event_preparation',
            'quotes',
            'quote_items',
            'invoice_items',
            'invoices',
            'payments',
            'expenses',
            'suppliers',
            'purchase_orders',
            'purchase_order_items',
            'purchase_order_receipts',
            'inventory_items',
            'inventory_movements',
            'event_costs',
            'travel_costs',
            'tax_rates',
        ];

        foreach ($resources as $resource) {
            $response = $this->get(route('exports.download', ['resource' => $resource]));

            $response->assertOk();
            $this->assertStringStartsWith("\xEF\xBB\xBF", $response->streamedContent());
        }
    }

    public function test_non_owner_cannot_open_or_download_business_exports(): void
    {
        $business = Business::create([
            'name' => 'Manager Export Business',
            'slug' => 'manager-export-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $manager = User::factory()->create();
        $business->users()->attach($manager->id, ['role' => 'manager']);

        $this->actingAs($manager)
            ->get(route('exports.index'))
            ->assertForbidden();

        $this->actingAs($manager)
            ->get(route('exports.download', ['resource' => 'customers']))
            ->assertForbidden();
    }

    public function test_unknown_export_resource_is_not_exposed(): void
    {
        $this->signInAsOwner();

        $this->get('/exports/unrecognised-dataset')->assertNotFound();
    }
}
