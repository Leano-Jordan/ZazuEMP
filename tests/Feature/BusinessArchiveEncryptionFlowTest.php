<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Services\BusinessArchiveEncryptionService;
use App\Services\BusinessArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    public function test_restore_audit_failure_rolls_back_imported_records_and_private_files(): void
    {
        Storage::fake('private');
        Storage::fake('public');

        $source = Business::create([
            'name' => 'Audit Failure Archive Source',
            'slug' => 'audit-failure-archive-source',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $sourcePath = 'profile-photos/customers/audit-failure-source.jpg';
        Storage::disk('private')->put($sourcePath, 'private source image');
        Customer::create([
            'business_id' => $source->id,
            'name' => 'Must Roll Back',
            'profile_photo_path' => $sourcePath,
        ]);

        $target = Business::create([
            'name' => 'Audit Failure Restore Target',
            'slug' => 'audit-failure-restore-target',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $this->signInAsOwner($target);

        $plain = app(BusinessArchiveService::class)->download($source);
        $encrypted = app(BusinessArchiveEncryptionService::class)->encryptFile(
            $plain,
            'correct horse battery staple'
        );
        $trigger = 'fail_business_archive_restore_audit';

        DB::unprepared("CREATE TRIGGER {$trigger} BEFORE INSERT ON audit_logs
            WHEN NEW.action = 'business.archive_restored'
            BEGIN SELECT RAISE(ABORT, 'simulated audit write failure'); END;");

        try {
            $response = $this->post(route('exports.archive.restore'), [
                'archive' => UploadedFile::fake()->createWithContent(
                    'business.zazu',
                    (string) file_get_contents($encrypted)
                ),
                'archive_password' => 'correct horse battery staple',
            ]);

            $response->assertRedirect();
            $response->assertSessionHasErrors('archive');
            $this->assertDatabaseMissing('customers', [
                'business_id' => $target->id,
                'name' => 'Must Roll Back',
            ]);
            $this->assertSame([$sourcePath], Storage::disk('private')->allFiles());
        } finally {
            DB::unprepared("DROP TRIGGER IF EXISTS {$trigger}");
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

    public function test_unexpected_restore_failure_is_logged_with_stage_and_safe_reference(): void
    {
        Storage::fake('private');
        Storage::fake('public');

        $source = Business::create([
            'name' => 'Unexpected Restore Source',
            'slug' => 'unexpected-restore-source',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $sourcePath = 'profile-photos/customers/unexpected-restore-source.jpg';
        Storage::disk('private')->put($sourcePath, 'private source image');
        Customer::create([
            'business_id' => $source->id,
            'name' => 'Unexpected Restore Customer',
            'profile_photo_path' => $sourcePath,
        ]);

        $target = Business::create([
            'name' => 'Unexpected Restore Target',
            'slug' => 'unexpected-restore-target',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $this->signInAsOwner($target);

        $archive = app(BusinessArchiveService::class)->download($source);
        Storage::shouldReceive('disk')
            ->once()
            ->with('private')
            ->andReturn(new class
            {
                public function put(string $path, string $contents): bool
                {
                    throw new \Error('internal diagnostic detail must not reach the user');
                }
            });

        Log::spy();

        try {
            $response = $this->post(route('exports.archive.restore'), [
                'archive' => UploadedFile::fake()->createWithContent(
                    'business.zip',
                    (string) file_get_contents($archive)
                ),
            ]);

            $response->assertRedirect();
            $response->assertSessionHasErrors('archive');
            Log::shouldHaveReceived('error')->once()->with('Business archive restore failed unexpectedly.', \Mockery::on(
                fn (array $context): bool => $context['stage'] === 'archive_validation_and_restore'
                    && $context['business_id'] === (int) $target->id
                    && $context['exception_class'] === \Error::class
                    && ! array_key_exists('exception_message', $context)
            ));
            $error = session('errors')->getBag('default')->first('archive');
            $this->assertStringContainsString('Zazu could not complete this business archive restore.', $error);
            $this->assertStringContainsString('Reference:', $error);
            $this->assertStringNotContainsString('internal diagnostic detail', $error);
            $this->assertDatabaseMissing('customers', [
                'business_id' => $target->id,
                'name' => 'Unexpected Restore Customer',
            ]);
        } finally {
            @unlink($archive);
        }
    }

    public function test_restore_validation_rejection_returns_reference_without_flashing_passphrase(): void
    {
        $business = Business::create([
            'name' => 'Restore Validation Diagnostics',
            'slug' => 'restore-validation-diagnostics',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $this->signInAsOwner($business);

        $response = $this->post(route('exports.archive.restore'), [
            'archive_password' => 'correct horse battery staple',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('archive');
        $error = session('errors')->getBag('default')->first('archive');

        $this->assertStringContainsString('Restore request reference:', $error);
        $this->assertNull(session()->getOldInput('archive_password'));
    }
    public function test_restore_rate_limit_returns_a_recovery_specific_message(): void
    {
        $business = Business::create([
            'name' => 'Restore Rate Limit Business',
            'slug' => 'restore-rate-limit-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $owner = $this->signInAsOwner($business);
        RateLimiter::clear('business-archive-restore:'.$owner->getAuthIdentifier());

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $this->post(route('exports.archive.restore'), [])
                ->assertRedirect()
                ->assertSessionHasErrors('archive');
        }

        $blocked = $this->post(route('exports.archive.restore'), [])
            ->assertRedirect(route('exports.index'))
            ->assertSessionHasErrors('archive');

        $message = session('errors')->getBag('default')->first('archive');
        $this->assertStringContainsString('Too many restore attempts.', $message);
        $this->assertStringContainsString('Wait ', $message);
        $this->assertStringContainsString('blocked before restore processing', $message);
        $this->assertStringContainsString('no data was imported', $message);
    }

}
