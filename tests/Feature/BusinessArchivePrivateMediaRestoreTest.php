<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Services\BusinessArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class BusinessArchivePrivateMediaRestoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_restore_keeps_private_tenant_media_private_even_when_archive_claims_public_source_disk(): void
    {
        Storage::fake('private');
        Storage::fake('public');
        $this->signInAsOwner();

        $source = auth()->user()->businesses()->firstOrFail();
        $logoPath = 'business-branding/source-logo.webp';
        $photoPath = 'profile-photos/customers/source-photo.jpg';

        Storage::disk('private')->put($logoPath, 'private business logo bytes');
        Storage::disk('private')->put($photoPath, 'private customer photo bytes');
        $source->update(['logo_path' => $logoPath]);

        Customer::create([
            'business_id' => $source->id,
            'name' => 'Archive Private Photo Customer',
            'profile_photo_path' => $photoPath,
        ]);

        $service = app(BusinessArchiveService::class);
        $archivePath = $service->download($source);
        $tamperedPath = tempnam(sys_get_temp_dir(), 'zazu-archive-private-test-');
        $this->assertNotFalse($tamperedPath);

        try {
            $sourceZip = new ZipArchive();
            $this->assertTrue($sourceZip->open($archivePath) === true);

            $targetZip = new ZipArchive();
            $this->assertTrue($targetZip->open($tamperedPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true);

            for ($index = 0; $index < $sourceZip->numFiles; $index++) {
                $entry = $sourceZip->statIndex($index);
                $this->assertIsArray($entry);
                $name = $entry['name'];

                if ($name === 'manifest.json') {
                    $manifest = json_decode((string) $sourceZip->getFromIndex($index), true, 512, JSON_THROW_ON_ERROR);
                    foreach ($manifest['files'] as &$file) {
                        if (in_array($file['table'] ?? null, ['businesses', 'customers'], true)) {
                            // A restore must not trust this attacker-controlled storage destination.
                            $file['source_disk'] = 'public';
                        }
                    }
                    unset($file);
                    $targetZip->addFromString($name, json_encode($manifest, JSON_THROW_ON_ERROR));
                    continue;
                }

                $bytes = $sourceZip->getFromIndex($index);
                $this->assertNotFalse($bytes);
                $targetZip->addFromString($name, $bytes);
            }

            $sourceZip->close();
            $targetZip->close();

            $target = Business::create([
                'name' => 'Archive Private Media Target',
                'slug' => 'archive-private-media-target',
                'status' => 'active',
                'currency' => 'ZAR',
            ]);
            $target->users()->attach(auth()->id(), ['role' => 'owner']);

            $result = $service->restore($tamperedPath, $target);
            $this->assertSame(2, $result['files']);

            $target->refresh();
            $restoredCustomer = Customer::query()
                ->where('business_id', $target->id)
                ->where('name', 'Archive Private Photo Customer')
                ->firstOrFail();

            $this->assertStringStartsWith('business-branding/', $target->logo_path);
            $this->assertStringStartsWith('profile-photos/customers/', $restoredCustomer->profile_photo_path);
            $this->assertSame('private business logo bytes', Storage::disk('private')->get($target->logo_path));
            $this->assertSame('private customer photo bytes', Storage::disk('private')->get($restoredCustomer->profile_photo_path));
            Storage::disk('public')->assertMissing($target->logo_path);
            Storage::disk('public')->assertMissing($restoredCustomer->profile_photo_path);
        } finally {
            if (is_string($archivePath) && is_file($archivePath)) {
                @unlink($archivePath);
            }
            if (is_string($tamperedPath) && is_file($tamperedPath)) {
                @unlink($tamperedPath);
            }
        }
    }
}
