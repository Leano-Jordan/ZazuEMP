<?php

namespace Tests\Feature;

use App\Models\Business;
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
