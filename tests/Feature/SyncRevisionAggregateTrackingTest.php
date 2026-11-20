<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Event;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\QuoteVersion;
use App\Models\SyncDevice;
use App\Services\QuoteService;
use App\Support\Offline\SyncBootstrapPayloadBuilder;
use App\Support\Offline\SyncEntityIdentityRegistry;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class SyncRevisionAggregateTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_bootstrap_reads_aggregate_data_and_revisions_inside_one_business_transaction(): void
    {
        [$business, $customer, $device] = $this->customerFixtureWithDevice();
        $baselineLevel = DB::connection()->transactionLevel();
        $businessReadLevel = null;

        DB::listen(function (QueryExecuted $query) use (&$businessReadLevel): void {
            if ($businessReadLevel !== null || ! str_contains(strtolower($query->sql), 'businesses')) {
                return;
            }

            $businessReadLevel = $query->connection->transactionLevel();
        });

        app(SyncBootstrapPayloadBuilder::class)->build($device);

        $this->assertNotNull($businessReadLevel);
        $this->assertGreaterThan($baselineLevel, $businessReadLevel);
    }

    public function test_nested_quote_item_and_inventory_movement_writes_advance_parent_revisions(): void
    {
        [$business, $customer] = $this->customerFixture();
        $event = Event::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'reference' => 'REV-NESTED-001',
            'name' => 'Revision Nested Work',
            'status' => 'confirmed',
        ]);
        $quote = Quote::create([
            'event_id' => $event->id,
            'reference' => 'REV-QUOTE-001',
            'status' => 'draft',
            'currency' => 'ZAR',
        ]);
        $version = QuoteVersion::create([
            'quote_id' => $quote->id,
            'version' => 1,
            'status' => 'draft',
            'subtotal' => '0.00',
            'tax_total' => '0.00',
            'total' => '0.00',
            'deposit_percent' => '0.00',
            'deposit_amount' => '0.00',
        ]);
        $inventory = InventoryItem::create([
            'business_id' => $business->id,
            'name' => 'Revision Stock',
            'unit' => 'unit',
            'reorder_level' => '1.00',
        ]);

        $registry = app(SyncEntityIdentityRegistry::class);
        $quoteIdentity = $registry->identify($quote, 'quote');
        $inventoryIdentity = $registry->identify($inventory, 'inventory_item');

        QuoteItem::create([
            'quote_version_id' => $version->id,
            'description' => 'Nested quote line',
            'quantity' => '1.00',
            'unit' => 'unit',
            'unit_price' => '0.00',
            'line_total' => '0.00',
        ]);
        InventoryMovement::create([
            'business_id' => $business->id,
            'inventory_item_id' => $inventory->id,
            'idempotency_key' => (string) Str::uuid(),
            'type' => 'receipt',
            'quantity' => '2.00',
            'unit_cost' => '10.00',
            'movement_date' => now()->toDateString(),
        ]);

        $this->assertSame(2, (int) $quoteIdentity->fresh()->revision);
        $this->assertSame(2, (int) $inventoryIdentity->fresh()->revision);

        app(QuoteService::class)->updateDraft(
            $quote,
            $version,
            collect(),
            [],
            null,
            false,
            null,
            '0.00',
        );

        // Removing the last zero-value line does not change quote totals. The
        // child delete itself must still invalidate the parent snapshot.
        $this->assertSame(3, (int) $quoteIdentity->fresh()->revision);
    }


    private function customerFixture(): array
    {
        $business = Business::create([
            'name' => 'Revision Tracking Business',
            'slug' => 'revision-tracking-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Revision Tracking Customer',
        ]);

        return [$business, $customer];
    }

    private function customerFixtureWithDevice(): array
    {
        [$business, $customer] = $this->customerFixture();
        $device = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
            'device_name' => 'Revision tracking phone',
            'device_type' => 'phone',
            'metadata' => ['sync_token_hash' => hash('sha256', 'revision-tracking-token')],
            'token_expires_at' => now()->addDays(SyncDevice::SYNC_TOKEN_IDLE_EXPIRY_DAYS),
        ]);
        app(SyncEntityIdentityRegistry::class)->identify($customer, 'customer');

        return [$business, $customer, $device];
    }
}
