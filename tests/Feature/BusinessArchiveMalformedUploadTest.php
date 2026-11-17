<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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

}
