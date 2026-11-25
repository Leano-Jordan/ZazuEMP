<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Services\BusinessArchiveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class BusinessArchiveRestoreRollbackTest extends TestCase
{
    use RefreshDatabase;

    /** @SuppressWarnings(PHPMD.ExcessiveMethodLength) */
    public function test_restore_preserves_original_write_failure_and_continues_cleanup_after_delete_failure(): void
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
        $deleteCalls = 0;
        $privateDisk = \Mockery::mock();
        $privateDisk->shouldReceive('writeStream')
            ->twice()
            ->andReturnUsing(function (string $path, $stream) use (&$writtenFiles, &$putCalls): bool {
                $putCalls++;
                $bytes = stream_get_contents($stream);
                if (! is_string($bytes)) {
                    return false;
                }

                // Simulate a storage adapter that leaves a partial file on failure.
                $writtenFiles[$path] = $bytes;

                return $putCalls !== 2;
            });
        $privateDisk->shouldReceive('delete')
            ->twice()
            ->andReturnUsing(function (string $path) use (&$writtenFiles, &$deleteCalls): bool {
                $deleteCalls++;
                if ($deleteCalls === 1) {
                    throw new RuntimeException('simulated storage cleanup failure');
                }

                unset($writtenFiles[$path]);

                return true;
            });
        Storage::shouldReceive('disk')
            ->with('private')
            ->times(4)
            ->andReturn($privateDisk);

        Log::spy();

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
        $this->assertCount(1, $writtenFiles, 'The first delete was forced to fail, but cleanup must continue for the second file.');
        $this->assertSame(2, $putCalls);
        $this->assertSame(2, $deleteCalls);
        Log::shouldHaveReceived('error')->once()->with(
            'Business archive rollback could not remove a partially restored file.',
            \Mockery::on(fn (array $context): bool => $context['business_id'] === (int) $target->id
                && $context['disk'] === 'private'
                && $context['exception_class'] === RuntimeException::class
                && ! array_key_exists('exception_message', $context))
        );
    }
}
