<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Services\BusinessArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class BusinessArchiveRestoreRollbackTest extends TestCase
{
    use RefreshDatabase;

    /** @SuppressWarnings(PHPMD.ExcessiveMethodLength) */
    public function test_restore_rolls_back_rows_and_removes_written_files_after_a_mid_restore_write_failure(): void
    {
        Storage::fake('private');
        Storage::fake('public');

        $source = Business::create([
            'name' => 'Rollback Archive Source',
            'slug' => 'rollback-archive-source',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $sourceFiles = [
            'profile-photos/customers/source-photo-one.jpg' => 'first private photo bytes',
            'profile-photos/customers/source-photo-two.jpg' => 'second private photo bytes',
        ];
        foreach ($sourceFiles as $sourcePath => $bytes) {
            Storage::disk('private')->put($sourcePath, $bytes);
            Customer::create([
                'business_id' => $source->id,
                'name' => basename($sourcePath),
                'profile_photo_path' => $sourcePath,
            ]);
        }

        $service = app(BusinessArchiveService::class);
        $archive = $service->download($source);
        $target = Business::create([
            'name' => 'Rollback Restore Target',
            'slug' => 'rollback-restore-target',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $writtenFiles = [];
        $putCalls = 0;
        $privateDisk = \Mockery::mock();
        $privateDisk->shouldReceive('put')
            ->twice()
            ->andReturnUsing(function (string $path, string $bytes) use (&$writtenFiles, &$putCalls): bool {
                $putCalls++;
                if ($putCalls === 2) {
                    return false;
                }

                $writtenFiles[$path] = $bytes;
                return true;
            });
        $privateDisk->shouldReceive('delete')
            ->once()
            ->andReturnUsing(function (string $path) use (&$writtenFiles): bool {
                unset($writtenFiles[$path]);
                return true;
            });
        Storage::shouldReceive('disk')
            ->with('private')
            ->times(3)
            ->andReturn($privateDisk);

        try {
            try {
                $service->restore($archive, $target);
                $this->fail('A failed private-file write must roll back the complete business restore.');
            } catch (RuntimeException $exception) {
                $this->assertStringContainsString('a business file could not be written', $exception->getMessage());
            }
        } finally {
            @unlink($archive);
        }

        $this->assertSame('Rollback Restore Target', $target->fresh()->name);
        $this->assertSame(0, Customer::query()->where('business_id', $target->id)->count());
        $this->assertSame([], $writtenFiles);
        $this->assertSame(2, $putCalls);
    }
}
