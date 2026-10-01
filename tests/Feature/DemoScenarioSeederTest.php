<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\User;
use Database\Seeders\DemoScenarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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
        $this->assertTrue($invoice->quoteVersion->matchesRequirements($invoice->event->requirements));
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
        $this->assertDatabaseHas('purchase_orders', [
            'reference' => 'PO-ZAZU-DEMO-002',
            'status' => 'ordered',
            'event_id' => null,
        ]);
        $this->assertDatabaseHas('inventory_items', ['sku' => 'INV-CHAFER-001']);
        $this->assertDatabaseHas('assets', ['asset_tag' => 'AST-TENT-001']);
        $this->assertDatabaseHas('compliance_documents', ['title' => 'Public liability insurance']);
        $this->assertSame(
            now()->subDay()->toDateString(),
            $invoice->event->event_date->toDateString()
        );
    }

    public function test_populated_demo_surfaces_are_operator_readable(): void
    {
        $this->seed(DemoScenarioSeeder::class);

        $owner = User::query()->where('email', env('ZAZU_DEMO_EMAIL', 'demo@zazu.local'))->firstOrFail();
        $this->actingAs($owner);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Open purchase orders')
            ->assertSee('Unpaid invoices');

        $this->get(route('work.index'))
            ->assertOk()
            ->assertSee('Buffet catering')
            ->assertSee('Services &amp; preparation', false)
            ->assertSee('11,500.00');

        $po = PurchaseOrder::query()->where('reference', 'PO-ZAZU-DEMO-001')->firstOrFail();
        $this->get(route('purchasing.show', $po))
            ->assertOk()
            ->assertSee('Job workspace')
            ->assertDontSee('Receive goods');

        $this->get(route('finance.invoices.show', Invoice::query()->where('number', 'INV-ZAZU-DEMO-001')->firstOrFail()))
            ->assertOk()
            ->assertSee('Payment history')
            ->assertSee('DEP-ZAZU-DEMO-001')
            ->assertSee('BAL-ZAZU-DEMO-001');

        $this->get(route('finance.index'))
            ->assertOk()
            ->assertSee('New invoice')
            ->assertSee('Record payment')
            ->assertSee('New expense');

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Financial snapshot')
            ->assertSee('Invoiced')
            ->assertSee('Outstanding')
            ->assertSee('ZAR');

        $this->assertStringNotContainsString("</section>\n\n    </section>", $this->get(route('work.show', $po->event))->getContent());
    }

    public function test_owner_backup_and_restore_round_trip_preserves_populated_business_and_private_file(): void
    {
        $this->seed(DemoScenarioSeeder::class);

        $owner = User::query()->where('email', env('ZAZU_DEMO_EMAIL', 'demo@zazu.local'))->firstOrFail();
        $this->actingAs($owner);

        $this->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Backup &amp; recovery', false)
            ->assertSee('Download backup')
            ->assertSee('Restore a backup');

        $privateProbe = storage_path('app/private/director-backup-probe.txt');
        File::ensureDirectoryExists(dirname($privateProbe));
        File::put($privateProbe, 'restore me');

        $output = storage_path('app/director-backup-test');
        File::deleteDirectory($output);

        try {
            $this->artisan('zazu:backup', ['--output' => $output])->assertExitCode(0);
            $archives = File::glob($output.DIRECTORY_SEPARATOR.'zazu-backup-*.zip');
            $this->assertCount(1, $archives);

            $businessId = DB::table('businesses')->where('slug', 'zazu-demo-catering')->value('id');
            DB::table('businesses')->where('id', $businessId)->update(['name' => 'Corrupted Business State']);
            File::put($privateProbe, 'changed after backup');

            $upload = UploadedFile::fake()->createWithContent('zazu-demo-backup.zip', File::get($archives[0]));
            $this->post(route('settings.restore'), ['backup' => $upload])
                ->assertRedirect(route('settings.index'))
                ->assertSessionHas('success');

            DB::purge();
            $this->assertSame('Zazu Demo Catering', DB::table('businesses')->where('slug', 'zazu-demo-catering')->value('name'));
            $this->assertSame('restore me', File::get($privateProbe));
            $this->assertDatabaseHas('invoices', ['number' => 'INV-ZAZU-DEMO-001']);
            $this->assertDatabaseHas('payments', ['idempotency_key' => 'demo-payment-balance-001']);
        } finally {
            File::delete($privateProbe);
            File::deleteDirectory($output);
        }
    }

    public function test_demo_scenario_is_idempotent_when_seeded_again(): void
    {
        $this->seed(DemoScenarioSeeder::class);
        $this->seed(DemoScenarioSeeder::class);

        $this->assertDatabaseCount('businesses', 1);
        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseCount('events', 1);
        $this->assertDatabaseCount('quotes', 1);
        $this->assertDatabaseCount('quote_versions', 1);
        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseCount('payments', 2);
        $this->assertDatabaseCount('purchase_orders', 2);
        $this->assertDatabaseCount('event_costs', 3);
        $this->assertDatabaseCount('event_preparation_items', 3);
        $this->assertDatabaseCount('business_capabilities', 5);
        $this->assertDatabaseCount('inventory_items', 4);
        $this->assertDatabaseCount('inventory_movements', 8);
        $this->assertDatabaseCount('assets', 3);
        $this->assertDatabaseCount('asset_allocations', 1);
        $this->assertDatabaseCount('suppliers', 2);
        $this->assertDatabaseCount('finance_expenses', 3);
        $this->assertDatabaseCount('travel_costs', 1);
        $this->assertDatabaseCount('compliance_documents', 4);
        $this->assertDatabaseCount('purchase_order_receipts', 1);
    }

    public function test_non_owner_cannot_trigger_backup_or_restore(): void
    {
        $this->seed(DemoScenarioSeeder::class);

        $manager = User::query()->where('email', 'operations@zazu.local')->firstOrFail();
        $this->actingAs($manager);

        $this->get(route('settings.backup'))
            ->assertForbidden();

        $upload = UploadedFile::fake()->create('not-a-backup.zip', 1, 'application/zip');

        $this->post(route('settings.restore'), ['backup' => $upload])
            ->assertForbidden();
    }

}
