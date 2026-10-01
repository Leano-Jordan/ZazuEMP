<?php

namespace Tests\Feature;

use App\Models\Invoice;
use Database\Seeders\DemoScenarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoScenarioSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_scenario_seeds_a_complete_finance_chain(): void
    {
        $this->seed(DemoScenarioSeeder::class);

        $invoice = Invoice::query()
            ->where('number', 'INV-ZAZU-DEMO-001')
            ->with(['quote', 'quoteVersion', 'payments'])
            ->firstOrFail();

        $this->assertSame('accepted', $invoice->quote->status);
        $this->assertSame('accepted', $invoice->quoteVersion->status);
        $this->assertSame('11500.00', (string) $invoice->total);
        $this->assertSame('11500.00', (string) $invoice->paid_amount);
        $this->assertSame('0.00', (string) $invoice->balance);
        $this->assertSame('0.00', (string) $invoice->deposit_balance);
        $this->assertCount(2, $invoice->payments);
        $this->assertDatabaseHas('events', [
            'reference' => 'ZAZU-DEMO-001',
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('purchase_orders', [
            'reference' => 'PO-ZAZU-DEMO-001',
            'status' => 'received',
        ]);
        $this->assertDatabaseHas('events', [
            'reference' => 'ZAZU-DEMO-001',
            'event_date' => now()->subDay()->toDateString(),
        ]);
    }

    public function test_demo_scenario_is_idempotent_when_seeded_again(): void
    {
        $this->seed(DemoScenarioSeeder::class);
        $this->seed(DemoScenarioSeeder::class);

        $this->assertDatabaseCount('businesses', 1);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('events', 1);
        $this->assertDatabaseCount('quotes', 1);
        $this->assertDatabaseCount('quote_versions', 1);
        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseCount('payments', 2);
        $this->assertDatabaseCount('purchase_orders', 1);
        $this->assertDatabaseCount('event_costs', 3);
        $this->assertDatabaseCount('event_preparation_items', 3);
    }
}
