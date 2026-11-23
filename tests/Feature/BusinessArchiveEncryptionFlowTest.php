<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Services\BusinessArchiveEncryptionService;
use App\Services\BusinessArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BusinessArchiveEncryptionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_downloads_a_password_protected_business_archive(): void
    {
        $business = Business::create([
            'name' => 'Encrypted Export Business',
            'slug' => 'encrypted-export-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $this->signInAsOwner($business);

        $response = $this->post(route('exports.archive.download'), [
            'archive_password' => 'correct horse battery staple',
            'archive_password_confirmation' => 'correct horse battery staple',
        ]);

        $response->assertDownload();
        $path = $response->baseResponse->getFile()->getPathname();
        $this->assertTrue(app(BusinessArchiveEncryptionService::class)->isEncrypted($path));
        $this->assertStringStartsWith('ZAZUENC2', file_get_contents($path, false, null, 0, 8));
        @unlink($path);
    }

    public function test_export_validation_never_flashes_archive_passphrases_to_the_session(): void
    {
        $business = Business::create([
            'name' => 'Passphrase Flash Guard',
            'slug' => 'passphrase-flash-guard',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $this->signInAsOwner($business);

        $response = $this->post(route('exports.archive.download'), [
            'archive_password' => 'correct horse battery staple',
            'archive_password_confirmation' => 'different horse battery staple',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('archive_password');
        $this->assertNull(session()->getOldInput('archive_password'));
        $this->assertNull(session()->getOldInput('archive_password_confirmation'));
    }

    public function test_encrypted_business_archive_requires_the_correct_passphrase_before_restore(): void
    {
        Storage::fake('private');
        Storage::fake('public');

        $source = Business::create([
            'name' => 'Encrypted Restore Source',
            'slug' => 'encrypted-restore-source',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        Customer::create(['business_id' => $source->id, 'name' => 'Encrypted Archive Customer']);

        $target = Business::create([
            'name' => 'Encrypted Restore Target',
            'slug' => 'encrypted-restore-target',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $this->signInAsOwner($target);

        $plain = app(BusinessArchiveService::class)->download($source);
        $encrypted = app(BusinessArchiveEncryptionService::class)->encryptFile(
            $plain,
            'correct horse battery staple'
        );

        try {
            $missingPasswordUpload = UploadedFile::fake()->createWithContent(
                'business.zazu',
                (string) file_get_contents($encrypted)
            );
            $missingPasswordResponse = $this->post(route('exports.archive.restore'), [
                'archive' => $missingPasswordUpload,
            ]);
            $missingPasswordResponse->assertRedirect();
            $missingPasswordResponse->assertSessionHasErrors('archive_password');
            $this->assertDatabaseMissing('customers', [
                'business_id' => $target->id,
                'name' => 'Encrypted Archive Customer',
            ]);

            $wrongPasswordUpload = UploadedFile::fake()->createWithContent(
                'business.zazu',
                (string) file_get_contents($encrypted)
            );
            $wrongPasswordResponse = $this->post(route('exports.archive.restore'), [
                'archive' => $wrongPasswordUpload,
                'archive_password' => 'incorrect horse battery staple',
            ]);
            $wrongPasswordResponse->assertRedirect();
            $wrongPasswordResponse->assertSessionHasErrors('archive');
            $this->assertDatabaseMissing('customers', [
                'business_id' => $target->id,
                'name' => 'Encrypted Archive Customer',
            ]);

            $correctPasswordUpload = UploadedFile::fake()->createWithContent(
                'business.zazu',
                (string) file_get_contents($encrypted)
            );
            $restoreResponse = $this->post(route('exports.archive.restore'), [
                'archive' => $correctPasswordUpload,
                'archive_password' => 'correct horse battery staple',
            ]);

            $restoreResponse->assertRedirect(route('exports.index'));
            $restoreResponse->assertSessionHas('success');
            $this->assertDatabaseHas('customers', [
                'business_id' => $target->id,
                'name' => 'Encrypted Archive Customer',
            ]);
        } finally {
            @unlink($plain);
            @unlink($encrypted);
        }
    }

    public function test_legacy_plaintext_business_archive_remains_restorable(): void
    {
        Storage::fake('private');
        Storage::fake('public');

        $source = Business::create([
            'name' => 'Legacy Archive Source',
            'slug' => 'legacy-archive-source',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        Customer::create(['business_id' => $source->id, 'name' => 'Legacy Archive Customer']);

        $target = Business::create([
            'name' => 'Legacy Archive Target',
            'slug' => 'legacy-archive-target',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $this->signInAsOwner($target);

        $legacyArchive = app(BusinessArchiveService::class)->download($source);

        try {
            $this->assertFalse(app(BusinessArchiveEncryptionService::class)->isEncrypted($legacyArchive));

            $response = $this->post(route('exports.archive.restore'), [
                'archive' => UploadedFile::fake()->createWithContent(
                    'legacy-business.zip',
                    (string) file_get_contents($legacyArchive)
                ),
            ]);

            $response->assertRedirect(route('exports.index'));
            $response->assertSessionHas('success');
            $this->assertDatabaseHas('customers', [
                'business_id' => $target->id,
                'name' => 'Legacy Archive Customer',
            ]);
        } finally {
            @unlink($legacyArchive);
        }
    }

}
