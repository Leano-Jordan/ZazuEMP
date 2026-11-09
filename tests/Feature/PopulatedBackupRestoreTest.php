<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoScenarioSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use ZipArchive;

class PopulatedBackupRestoreTest extends TestCase
{
    public function test_business_settings_do_not_expose_installation_wide_backup_or_restore(): void
    {
        if (! class_exists(ZipArchive::class)) {
            $this->markTestSkipped('PHP Zip extension is required for backup tests.');
        }

        $database = storage_path('app/director-populated-backup.sqlite');
        $output = storage_path('app/director-backup-test');
        $originalDefault = config('database.default');
        $originalDatabase = config('database.connections.sqlite.database');

        File::ensureDirectoryExists(dirname($database));
        File::delete($database);
        File::deleteDirectory($output);

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $database,
        ]);

        try {
            DB::purge('sqlite');
            $this->artisan('migrate:fresh', ['--database' => 'sqlite', '--force' => true])
                ->assertExitCode(0);
            $this->seed(DemoScenarioSeeder::class);

            $owner = User::query()
                ->where('email', env('ZAZU_DEMO_EMAIL', 'demo@zazu.local'))
                ->firstOrFail();
            $this->actingAs($owner);

            $this->get(route('settings.index'))
                ->assertOk()
                ->assertSee('Installation-wide backups and restores are managed by the system operator.')
                ->assertDontSee('Download backup')
                ->assertDontSee('Restore a backup');

            $this->assertFalse(Route::has('settings.backup'));
            $this->assertFalse(Route::has('settings.restore'));
            $this->get('/settings/backup')->assertNotFound();
            $this->post('/settings/restore')->assertNotFound();

            $this->artisan('zazu:backup', ['--output' => $output])->assertExitCode(0);
            $archives = File::glob($output.DIRECTORY_SEPARATOR.'zazu-backup-*.zip');
            $this->assertCount(1, $archives);

            $zip = new ZipArchive();
            $this->assertTrue($zip->open($archives[0]) === true);
            $this->assertNotFalse($zip->locateName('database.sqlite'));
            $manifest = json_decode((string) $zip->getFromName('manifest.json'), true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame(['database', 'private_storage'], $manifest['contents'] ?? null);
            $zip->close();
        } finally {
            DB::purge('sqlite');
            File::delete($database);
            File::deleteDirectory($output);
            config([
                'database.default' => $originalDefault,
                'database.connections.sqlite.database' => $originalDatabase,
            ]);
            DB::purge('sqlite');
        }
    }

    public function test_populated_installation_backup_restores_business_workflow_and_private_files(): void
    {
        if (! class_exists(ZipArchive::class)) {
            $this->markTestSkipped('PHP Zip extension is required for backup/restore tests.');
        }

        $database = storage_path('app/director-populated-restore.sqlite');
        $output = storage_path('app/director-populated-restore-backups');
        $privateDirectory = storage_path('app/private/director-populated-restore');
        $privateProbe = $privateDirectory.DIRECTORY_SEPARATOR.'acceptance-proof.txt';
        $originalDefault = config('database.default');
        $originalDatabase = config('database.connections.sqlite.database');
        $tables = [
            'businesses', 'customers', 'events', 'quotes', 'quote_versions',
            'quote_items', 'invoices', 'invoice_items', 'payments', 'suppliers',
            'purchase_orders', 'purchase_order_items', 'event_costs',
            'event_preparation_items',
        ];

        File::ensureDirectoryExists(dirname($database));
        File::delete($database);
        File::deleteDirectory($output);
        File::deleteDirectory($privateDirectory);

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $database,
        ]);

        try {
            DB::purge('sqlite');
            $this->artisan('migrate:fresh', ['--database' => 'sqlite', '--force' => true])
                ->assertExitCode(0);
            $this->seed(DemoScenarioSeeder::class);

            $business = DB::table('businesses')->where('slug', 'zazu-demo-catering')->first();
            $event = DB::table('events')->where('reference', 'ZAZU-DEMO-001')->first();
            $invoice = DB::table('invoices')->where('number', 'INV-ZAZU-DEMO-001')->first();
            $this->assertNotNull($business);
            $this->assertNotNull($event);
            $this->assertNotNull($invoice);

            File::ensureDirectoryExists($privateDirectory);
            File::put($privateProbe, 'private media recovery sentinel');
            $expectedCounts = [];
            foreach ($tables as $table) {
                $expectedCounts[$table] = DB::table($table)->count();
            }

            $this->artisan('zazu:backup', ['--output' => $output])->assertExitCode(0);
            $archives = File::glob($output.DIRECTORY_SEPARATOR.'zazu-backup-*.zip');
            $this->assertCount(1, $archives);

            DB::table('businesses')->where('id', $business->id)->update(['name' => 'Mutated after backup']);
            DB::table('events')->where('id', $event->id)->update(['name' => 'Mutated event']);
            DB::table('invoices')->where('id', $invoice->id)->update(['status' => 'draft']);
            File::delete($privateProbe);

            $this->artisan('zazu:restore', [
                'archive' => $archives[0],
                '--force' => true,
            ])->assertExitCode(0);

            DB::purge('sqlite');
            $this->assertSame('Zazu Demo Catering', DB::table('businesses')->where('id', $business->id)->value('name'));
            $this->assertSame('Mokoena Family Celebration', DB::table('events')->where('id', $event->id)->value('name'));
            $this->assertSame('paid', DB::table('invoices')->where('id', $invoice->id)->value('status'));
            foreach ($expectedCounts as $table => $count) {
                $this->assertSame($count, DB::table($table)->count(), "Restored {$table} count does not match the backup.");
            }
            $this->assertFileExists($privateProbe);
            $this->assertSame('private media recovery sentinel', File::get($privateProbe));
        } finally {
            DB::purge('sqlite');
            File::delete($database);
            File::deleteDirectory($output);
            File::deleteDirectory($privateDirectory);
            config([
                'database.default' => $originalDefault,
                'database.connections.sqlite.database' => $originalDatabase,
            ]);
            DB::purge('sqlite');
        }
    }

}
