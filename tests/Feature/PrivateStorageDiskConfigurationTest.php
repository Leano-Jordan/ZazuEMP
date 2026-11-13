<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PrivateStorageDiskConfigurationTest extends TestCase
{
    public function test_private_disk_is_registered_and_writes_outside_public_storage(): void
    {
        $privateConfig = config('filesystems.disks.private');

        $this->assertIsArray($privateConfig);
        $this->assertSame('local', $privateConfig['driver']);
        $this->assertSame(storage_path('app/private'), $privateConfig['root']);
        $this->assertNotSame(
            config('filesystems.disks.public.root'),
            $privateConfig['root'],
            'Private business files must never resolve to the public storage root.'
        );

        // Do not use Storage::fake(): that would silently register a missing
        // disk in the test container and hide a production configuration defect.
        $disk = Storage::disk('private');
        $path = 'tests/private-disk-probe/'.Str::uuid().'.txt';

        try {
            $this->assertTrue($disk->put($path, 'private storage configuration probe'));
            $this->assertTrue($disk->exists($path));
            $this->assertSame('private storage configuration probe', $disk->get($path));
        } finally {
            $disk->delete($path);
        }
    }
}
