<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\User;
use Database\Seeders\DemoScenarioSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PopulatedBackupRestoreTest extends TestCase
{
public function test_owner_backup_and_restore_round_trip_preserves_populated_business_and_private_file(): void
{
    if (!class_exists(\ZipArchive::class)) {
        $this->markTestSkipped('PHP Zip extension is required for backup/restore tests.');
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

        try {
            $this->artisan('zazu:backup', ['--output' => $output])->assertExitCode(0);
            $archives = File::glob($output.DIRECTORY_SEPARATOR.'zazu-backup-*.zip');
            $this->assertCount(1, $archives);

            $businessId = DB::table('businesses')->where('slug', 'zazu-demo-catering')->value('id');
            DB::table('businesses')->where('id', $businessId)->update(['name' => 'Corrupted Business State']);
            File::put($privateProbe, 'changed after backup');

            $upload = UploadedFile::fake()->createWithContent('zazu-demo-backup.zip', File::get($archives[0]));
            $response = $this->post(route('settings.restore'), ['backup' => $upload])
                ->assertRedirect(route('settings.index'));

            $this->assertSame(
                'Backup restored successfully. Refresh Zazu if another browser tab was open during recovery.',
                $response->getSession()->get('success'),
                (string) $response->getSession()->get('error'),
            );

            DB::purge('sqlite');
            $this->assertSame('Zazu Demo Catering', DB::table('businesses')->where('slug', 'zazu-demo-catering')->value('name'));
            $this->assertSame('restore me', File::get($privateProbe));
            $this->assertDatabaseHas('invoices', ['number' => 'INV-ZAZU-DEMO-001']);
            $this->assertDatabaseHas('payments', ['idempotency_key' => '00000000-0000-4000-8000-000000000002']);
        } finally {
            File::delete($privateProbe);
        }
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

}
