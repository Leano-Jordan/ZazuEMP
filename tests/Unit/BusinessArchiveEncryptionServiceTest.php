<?php

namespace Tests\Unit;

use App\Services\BusinessArchiveEncryptionService;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class BusinessArchiveEncryptionServiceTest extends TestCase
{
    public function test_encrypted_archive_stream_round_trip_handles_multiple_chunks(): void
    {
        $service = app(BusinessArchiveEncryptionService::class);
        $source = tempnam(sys_get_temp_dir(), 'zazu-encryption-source-');
        $payload = random_bytes(2 * 1024 * 1024 + 37);
        file_put_contents($source, $payload);

        $encrypted = null;
        $decrypted = null;
        try {
            $encrypted = $service->encryptFile($source, 'correct horse battery staple');
            $this->assertTrue($service->isEncrypted($encrypted));
            $this->assertStringNotContainsString(substr($payload, 0, 128), (string) file_get_contents($encrypted));

            $decrypted = $service->decryptFile($encrypted, 'correct horse battery staple');
            $this->assertSame($payload, file_get_contents($decrypted));
            if (PHP_OS_FAMILY !== 'Windows') {
                $this->assertSame(0600, fileperms($encrypted) & 0777);
                $this->assertSame(0600, fileperms($decrypted) & 0777);
            }
        } finally {
            @unlink($source);
            if (is_string($encrypted)) {
                @unlink($encrypted);
            }
            if (is_string($decrypted)) {
                @unlink($decrypted);
            }
        }
    }

    public function test_wrong_passphrase_and_tampered_ciphertext_are_rejected(): void
    {
        $service = app(BusinessArchiveEncryptionService::class);
        $source = tempnam(sys_get_temp_dir(), 'zazu-encryption-source-');
        file_put_contents($source, Str::random(2048));

        $encrypted = null;
        try {
            $encrypted = $service->encryptFile($source, 'correct horse battery staple');

            try {
                $service->decryptFile($encrypted, 'incorrect horse battery staple');
                $this->fail('A wrong passphrase must not decrypt a business archive.');
            } catch (RuntimeException $exception) {
                $this->assertStringContainsString('incorrect or the encrypted archive is damaged', $exception->getMessage());
            }

            $bytes = file_get_contents($encrypted);
            $bytes[strlen($bytes) - 1] = chr(ord($bytes[strlen($bytes) - 1]) ^ 1);
            file_put_contents($encrypted, $bytes);

            try {
                $service->decryptFile($encrypted, 'correct horse battery staple');
                $this->fail('Tampered ciphertext must not decrypt.');
            } catch (RuntimeException $exception) {
                $this->assertStringContainsString('incorrect or the encrypted archive is damaged', $exception->getMessage());
            }
        } finally {
            @unlink($source);
            if (is_string($encrypted)) {
                @unlink($encrypted);
            }
        }
    }

    public function test_plain_legacy_zip_is_detected_as_unencrypted_for_backward_compatibility(): void
    {
        $service = app(BusinessArchiveEncryptionService::class);
        $legacy = tempnam(sys_get_temp_dir(), 'zazu-legacy-archive-');

        try {
            file_put_contents($legacy, "PK\x03\x04legacy archive");
            $this->assertFalse($service->isEncrypted($legacy));
        } finally {
            @unlink($legacy);
        }
    }
}
