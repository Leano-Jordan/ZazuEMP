<?php

namespace Tests\Feature;

use Closure;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class OfflineBootstrapWriteLockTest extends TestCase
{
    use RefreshDatabase;

    private bool $captureQueries = false;

    /** @var array<int, string> */
    private array $queries = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsOwner();

        DB::listen(function (QueryExecuted $query): void {
            if ($this->captureQueries) {
                $this->queries[] = $query->sql;
            }
        });
    }

    public function test_new_syncable_aggregate_writes_take_the_business_lock_before_insert(): void
    {
        $this->assertBusinessLockBeforeInsert('customers', fn () => $this->post(route('customers.store'), [
            'name' => 'Snapshot Customer '.Str::uuid(),
            'primary_contact_name' => 'Primary Contact',
        ])->assertRedirect(route('customers.index')));

        $this->assertBusinessLockBeforeInsert('customers', fn () => $this->postJson(route('customers.quick_from_work'), [
            'name' => 'Snapshot Quick Customer '.Str::uuid(),
            'primary_contact_name' => 'Quick Contact',
        ])->assertCreated());

        $this->assertBusinessLockBeforeInsert('suppliers', fn () => $this->post(route('suppliers.store'), [
            'name' => 'Snapshot Supplier '.Str::uuid(),
        ])->assertRedirect(route('suppliers.index')));

        $this->assertBusinessLockBeforeInsert('business_capabilities', fn () => $this->post(route('capabilities.store'), [
            'name' => 'Snapshot Catalogue Item '.Str::uuid(),
            'category' => 'Catering',
            'capability_type' => 'service',
            'pricing_basis' => 'per_person',
            'default_unit' => 'guest',
            'description' => 'Bootstrap snapshot lock test.',
            'is_active' => '1',
        ])->assertRedirect(route('capabilities.index')));

        $this->assertBusinessLockBeforeInsert('assets', fn () => $this->post(route('assets.store'), [
            'asset_tag' => 'LOCK-'.Str::upper(Str::random(10)),
            'name' => 'Snapshot Asset',
            'condition' => 'good',
            'purchase_cost' => '1000.00',
        ])->assertRedirect(route('assets.index')));

        $this->assertBusinessLockBeforeInsert('inventory_items', fn () => $this->post(route('inventory.store'), [
            'name' => 'Snapshot Inventory Item '.Str::uuid(),
            'unit' => 'unit',
            'reorder_level' => '0.00',
        ])->assertRedirect(route('inventory.index')));
    }

    private function assertBusinessLockBeforeInsert(string $table, Closure $write): void
    {
        $this->queries = [];
        $this->captureQueries = true;

        try {
            $write();
        } finally {
            $this->captureQueries = false;
        }

        $insertIndex = null;
        foreach ($this->queries as $index => $query) {
            if (preg_match('/^\s*insert\s+into\s+["`]?'.preg_quote($table, '/').'["`]?/i', $query)) {
                $insertIndex = $index;
                break;
            }
        }

        $this->assertNotNull($insertIndex, "Expected an insert into {$table}.");
        $insertIndex = (int) $insertIndex;
        $this->assertGreaterThan(0, $insertIndex);
        $lockQuery = strtolower($this->queries[$insertIndex - 1]);
        $this->assertStringContainsString('select', $lockQuery);
        $this->assertStringContainsString('businesses', $lockQuery);
    }
}
