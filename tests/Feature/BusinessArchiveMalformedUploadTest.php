<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Services\BusinessArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BusinessArchiveMalformedUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_malformed_business_archive_is_returned_as_validation_error_without_mutation(): void
    {
        $this->signInAsOwner();
        $business = auth()->user()->businesses()->firstOrFail();
        $archivePath = tempnam(sys_get_temp_dir(), 'zazu-invalid-archive-');
        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($archivePath, \ZipArchive::OVERWRITE) === true);
        $zip->addFromString('manifest.json', '{invalid json');
        $zip->addFromString('data.json', '{}');
        $zip->close();

        try {
            $upload = UploadedFile::fake()->createWithContent(
                'damaged.zazu',
                (string) file_get_contents($archivePath)
            );

            Log::spy();
            $response = $this->post(route('exports.archive.restore'), ['archive' => $upload]);

            $response->assertRedirect();
            $response->assertSessionHasErrors('archive');
            Log::shouldHaveReceived('warning')->once()->with(
                'Business archive restore failed while decoding archive data.',
                \Mockery::on(fn (array $context): bool => $context['stage'] === 'archive_envelope_validation'
                    && $context['business_id'] === (int) $business->id
                    && is_string($context['memory_limit'])
                    && ! array_key_exists('archive_password', $context)
                    && ! array_key_exists('exception_message', $context))
            );
            $error = session('errors')->first('archive');
            $this->assertStringStartsWith('The uploaded archive is damaged or contains invalid data.', $error);
            $this->assertStringContainsString('Reference: ', $error);
        } finally {
            @unlink($archivePath);
        }

        $this->assertDatabaseCount('customers', 0);
        $this->assertSame($business->id, auth()->user()->businesses()->firstOrFail()->id);
    }

    public function test_restore_rejects_undeclared_zip_entries_before_changing_the_target_workspace(): void
    {
        Storage::fake('private');
        Storage::fake('public');
        $this->signInAsOwner();
        $source = auth()->user()->businesses()->firstOrFail();

        $target = Business::create([
            'name' => 'Strict Archive Target',
            'slug' => 'strict-archive-target',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $target->users()->attach(auth()->id(), ['role' => 'owner']);

        $archivePath = app(BusinessArchiveService::class)->download($source);
        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($archivePath) === true);
        $zip->addFromString('untrusted/readme.txt', 'This entry is not part of the Zazu archive contract.');
        $zip->close();

        try {
            try {
                app(BusinessArchiveService::class)->restore($archivePath, $target);
                $this->fail('Unexpected ZIP entries must be rejected.');
            } catch (\RuntimeException $exception) {
                $this->assertSame(
                    'The business archive contains an unexpected ZIP entry.',
                    $exception->getMessage()
                );
            }
        } finally {
            @unlink($archivePath);
        }

        $this->assertDatabaseMissing('customers', [
            'business_id' => $target->id,
        ]);
        $this->assertDatabaseMissing('events', [
            'business_id' => $target->id,
        ]);
    }

    public function test_archive_file_manifest_cannot_override_server_staging_metadata(): void
    {
        Storage::fake('private');
        Storage::fake('public');
        $this->signInAsOwner();
        $business = auth()->user()->businesses()->firstOrFail();
        $path = 'profile-photos/customers/manifest-guard.jpg';
        Storage::disk('private')->put($path, 'private customer image');
        Customer::create([
            'business_id' => $business->id,
            'name' => 'Manifest Guard Customer',
            'profile_photo_path' => $path,
        ]);

        $archivePath = app(BusinessArchiveService::class)->download($business);
        $sentinelPath = tempnam(sys_get_temp_dir(), 'zazu-restore-sentinel-');
        $this->assertNotFalse($sentinelPath);
        file_put_contents($sentinelPath, 'sentinel must survive');

        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($archivePath) === true);
        $manifest = json_decode((string) $zip->getFromName('manifest.json'), true, 512, JSON_THROW_ON_ERROR);
        $manifest['files'][0]['staged_path'] = $sentinelPath;
        $manifest['files'][0]['payload_bytes'] = -1;
        $this->assertTrue($zip->deleteName('manifest.json'));
        $this->assertTrue($zip->addFromString('manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR)));
        $this->assertTrue($zip->close());

        try {
            try {
                app(BusinessArchiveService::class)->verify($archivePath);
                $this->fail('Untrusted manifest fields must not override server staging metadata.');
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString('invalid business file reference', $exception->getMessage());
            }

            $this->assertFileExists($sentinelPath);
            $this->assertSame('sentinel must survive', file_get_contents($sentinelPath));
        } finally {
            @unlink($archivePath);
            @unlink($sentinelPath);
        }
    }

    public function test_populated_restore_target_is_rejected_before_attachment_staging(): void
    {
        Storage::fake('private');
        Storage::fake('public');
        $this->signInAsOwner();
        $source = auth()->user()->businesses()->firstOrFail();
        $sourcePath = 'profile-photos/customers/preflight-source.jpg';
        Storage::disk('private')->put($sourcePath, 'source image');
        Customer::create([
            'business_id' => $source->id,
            'name' => 'Preflight Source Customer',
            'profile_photo_path' => $sourcePath,
        ]);

        $target = \App\Models\Business::create([
            'name' => 'Occupied Restore Target',
            'slug' => 'occupied-restore-target',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        DB::table('customers')->insert([
            'business_id' => $target->id,
            'name' => 'Existing Target Customer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $archivePath = app(BusinessArchiveService::class)->download($source);
        $stages = [];

        try {
            try {
                app(BusinessArchiveService::class)->restore(
                    $archivePath,
                    $target,
                    null,
                    static function (string $stage) use (&$stages): void {
                        $stages[] = $stage;
                    }
                );
                $this->fail('Restore must not import into a populated workspace.');
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString('already contains business records', $exception->getMessage());
            }

            $this->assertContains('target_workspace_preflight', $stages);
            $this->assertNotContains('attachment_payload_staging', $stages);
            $this->assertDatabaseMissing('customers', [
                'business_id' => $target->id,
                'name' => 'Preflight Source Customer',
            ]);
        } finally {
            @unlink($archivePath);
        }
    }

    public function test_populated_workspace_restore_error_is_visible_and_actionable(): void
    {
        Storage::fake('private');
        Storage::fake('public');
        $this->signInAsOwner();
        $target = auth()->user()->businesses()->firstOrFail();
        Customer::create([
            'business_id' => $target->id,
            'name' => 'Existing Demo Workspace Customer',
        ]);

        $source = Business::create([
            'name' => 'Rosscore Archive Source',
            'slug' => 'rosscore-archive-source',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        DB::table('customers')->insert([
            'business_id' => $source->id,
            'name' => 'Rosscore Archive Customer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $archivePath = app(BusinessArchiveService::class)->download($source);

        try {
            $response = $this->from(route('exports.index'))->followingRedirects()->post(
                route('exports.archive.restore'),
                [
                    'archive' => UploadedFile::fake()->createWithContent(
                        'rosscore-business.zazu',
                        (string) file_get_contents($archivePath)
                    ),
                ]
            );

            $response->assertOk();
            $response->assertSee('already contains business records');
            $response->assertSee('zazu-field-error', false);
            $this->assertDatabaseMissing('customers', [
                'business_id' => $target->id,
                'name' => 'Rosscore Archive Customer',
            ]);
            $this->assertDatabaseHas('customers', [
                'business_id' => $target->id,
                'name' => 'Existing Demo Workspace Customer',
            ]);
        } finally {
            @unlink($archivePath);
        }
    }

    public function test_archive_can_restore_into_a_new_workspace_without_mutating_the_demo_workspace(): void
    {
        Storage::fake('private');
        Storage::fake('public');
        $this->signInAsOwner();
        $originalWorkspace = auth()->user()->businesses()->firstOrFail();
        Customer::create([
            'business_id' => $originalWorkspace->id,
            'name' => 'Demo Data Must Remain',
        ]);

        $source = Business::create([
            'name' => 'Rosscore Archive Source',
            'slug' => 'rosscore-new-workspace-source',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        DB::table('customers')->insert([
            'business_id' => $source->id,
            'name' => 'Imported Rosscore Customer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $archivePath = app(BusinessArchiveService::class)->download($source);

        try {
            $response = $this->from(route('exports.index'))->followingRedirects()->post(
                route('exports.archive.restore'),
                [
                    'archive' => UploadedFile::fake()->createWithContent(
                        'rosscore-business.zazu',
                        (string) file_get_contents($archivePath)
                    ),
                    'restore_as_new_workspace' => '1',
                ]
            );

            $response->assertOk();
            $response->assertSee('Business archive restored:');
            $restoredWorkspace = auth()->user()->businesses()
                ->where('businesses.name', 'Rosscore Archive Source')
                ->firstOrFail();

            $this->assertSame(
                (int) $restoredWorkspace->id,
                app(\App\Support\CurrentBusiness::class)->id(auth()->user())
            );
            $this->assertDatabaseHas('customers', [
                'business_id' => $restoredWorkspace->id,
                'name' => 'Imported Rosscore Customer',
            ]);
            $this->assertDatabaseHas('customers', [
                'business_id' => $originalWorkspace->id,
                'name' => 'Demo Data Must Remain',
            ]);
            $this->assertDatabaseMissing('customers', [
                'business_id' => $originalWorkspace->id,
                'name' => 'Imported Rosscore Customer',
            ]);
        } finally {
            @unlink($archivePath);
        }
    }

    public function test_non_zip_upload_reports_the_specific_libzip_failure_code(): void
    {
        $this->signInAsOwner();
        $business = auth()->user()->businesses()->firstOrFail();
        Log::spy();

        $response = $this->post(route('exports.archive.restore'), [
            'archive' => UploadedFile::fake()->createWithContent(
                'not-a-zip.zazu',
                'This is not a ZIP archive.'
            ),
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('archive');
        $message = session('errors')->getBag('default')->first('archive');

        $this->assertStringContainsString('libzip status '.\ZipArchive::ER_NOZIP, $message);
        $this->assertStringContainsString('file is not a ZIP archive', $message);
        Log::shouldHaveReceived('warning')->once()->with(
            'Business archive restore failed.',
            \Mockery::on(fn (array $context): bool => $context['stage'] === 'archive_open'
                && $context['exception_code'] === (string) \ZipArchive::ER_NOZIP
                && $context['business_id'] === (int) $business->id)
        );
        $this->assertDatabaseCount('customers', 0);
    }

}
