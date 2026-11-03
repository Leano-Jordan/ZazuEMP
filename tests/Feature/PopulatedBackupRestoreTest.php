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
}
