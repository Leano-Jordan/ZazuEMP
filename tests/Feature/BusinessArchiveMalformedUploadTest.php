<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Services\BusinessArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BusinessArchiveMalformedUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_malformed_business_archive_is_returned_as_validation_error_without_mutation(): void
    {
        $this->signInAsOwner();
        $business = auth()->user()->businesses()->firstOrFail();
        $archivePath = tempnam(sys_get_temp_dir(), 'zazu-invalid-archive-');
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($archivePath, \ZipArchive::OVERWRITE) === true);
        $zip->addFromString('manifest.json', '{invalid json');
        $zip->addFromString('data.json', '{}');
        $zip->close();

        try {
            $upload = UploadedFile::fake()->createWithContent(
                'damaged.zazu',
                (string) file_get_contents($archivePath)
            );

            $response = $this->post(route('exports.archive.restore'), ['archive' => $upload]);

            $response->assertRedirect();
            $response->assertSessionHasErrors('archive');
            $this->assertSame(
                'The uploaded archive is damaged or contains invalid data.',
                session('errors')->first('archive')
            );
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
        $zip = new \\ZipArchive();
        $this->assertTrue($zip->open($archivePath) === true);
        $zip->addFromString('untrusted/readme.txt', 'This entry is not part of the Zazu archive contract.');
        $zip->close();

        try {
            try {
                app(BusinessArchiveService::class)->restore($archivePath, $target);
                $this->fail('Unexpected ZIP entries must be rejected.');
            } catch (\\RuntimeException $exception) {
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

}
