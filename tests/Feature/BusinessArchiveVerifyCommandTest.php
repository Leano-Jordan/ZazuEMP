<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Services\BusinessArchiveEncryptionService;
use App\Services\BusinessArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;
use ZipArchive;

class BusinessArchiveVerifyCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_plaintext_business_archive_is_verified_without_database_mutation(): void
    {
        Storage::fake('private');
        Storage::fake('public');

        $business = $this->createBusiness('verify-plain-source');
        $photoPath = 'profile-photos/customers/verify-plain-source.jpg';
        Storage::disk('private')->put($photoPath, 'customer image payload');
        Customer::create([
            'business_id' => $business->id,
            'name' => 'Verification Customer',
            'profile_photo_path' => $photoPath,
        ]);

        $archive = app(BusinessArchiveService::class)->download($business);
        $businessCount = Business::query()->count();
        $customerCount = Customer::query()->count();

        try {
            $this->artisan('zazu:verify-business-archive', ['archive' => $archive])
                ->expectsOutput('Business archive passed read-only verification.')
                ->expectsOutput('No business workspace or database records were changed.')
                ->assertExitCode(0);

            $this->assertSame($businessCount, Business::query()->count());
            $this->assertSame($customerCount, Customer::query()->count());
        } finally {
            @unlink($archive);
        }
    }

    public function test_archive_verification_rejects_manifest_record_count_mismatch_without_importing_data(): void
    {
        Storage::fake('private');
        Storage::fake('public');

        $business = $this->createBusiness('verify-count-mismatch');
        Customer::create([
            'business_id' => $business->id,
            'name' => 'Counted Customer',
        ]);

        $service = app(BusinessArchiveService::class);
        $archive = $service->download($business);
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($archive) === true);

        $manifest = json_decode((string) $zip->getFromName('manifest.json'), true, 512, JSON_THROW_ON_ERROR);
        $manifest['tables']['customers']++;
        $this->assertTrue($zip->deleteName('manifest.json'));
        $this->assertTrue($zip->addFromString('manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR)));
        $this->assertTrue($zip->close());

        $businessCount = Business::query()->count();
        $customerCount = Customer::query()->count();
        $stages = [];

        try {
            try {
                $service->verify($archive, static function (string $stage) use (&$stages): void {
                    $stages[] = $stage;
                });
                $this->fail('An archive with a false table record count must be rejected.');
            } catch (RuntimeException $exception) {
                $this->assertSame(
                    'The business archive record counts do not match its manifest.',
                    $exception->getMessage()
                );
            }

            $this->assertSame(['archive_open', 'archive_envelope_validation'], $stages);
            $this->assertSame($businessCount, Business::query()->count());
            $this->assertSame($customerCount, Customer::query()->count());
        } finally {
            @unlink($archive);
        }
    }

    public function test_encrypted_business_archive_uses_hidden_passphrase_prompt_for_verification(): void
    {
        Storage::fake('private');
        Storage::fake('public');

        $business = $this->createBusiness('verify-encrypted-source');
        $archive = app(BusinessArchiveService::class)->download($business);
        $passphrase = 'correct horse battery staple';
        $encryptedArchive = app(BusinessArchiveEncryptionService::class)->encryptFile($archive, $passphrase);
        $businessCount = Business::query()->count();

        try {
            $this->artisan('zazu:verify-business-archive', ['archive' => $encryptedArchive])
                ->expectsQuestion('Business archive passphrase', $passphrase)
                ->expectsOutput('Business archive passed read-only verification.')
                ->assertExitCode(0);

            $this->assertSame($businessCount, Business::query()->count());
        } finally {
            @unlink($archive);
            @unlink($encryptedArchive);
        }
    }

    private function createBusiness(string $slug): Business
    {
        return Business::create([
            'name' => 'Archive Verification',
            'slug' => $slug,
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
    }
}
