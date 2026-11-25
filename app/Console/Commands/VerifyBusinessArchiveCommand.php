<?php

namespace App\Console\Commands;

use App\Services\BusinessArchiveEncryptionService;
use App\Services\BusinessArchiveService;
use Illuminate\Console\Command;
use Throwable;

class VerifyBusinessArchiveCommand extends Command
{
    protected $signature = 'zazu:verify-business-archive {archive : Path to a .zazu business archive}';

    protected $description = 'Verify a business archive without restoring or changing workspace data';

    public function handle(
        BusinessArchiveService $archives,
        BusinessArchiveEncryptionService $encryption
    ): int {
        $archivePath = $this->argument('archive');
        if (! is_string($archivePath) || ! is_file($archivePath) || ! is_readable($archivePath)) {
            $this->error('The business archive was not found or cannot be read.');

            return self::FAILURE;
        }

        $decryptedPath = null;
        $stage = 'format_detection';

        try {
            if ($encryption->isEncrypted($archivePath)) {
                $passphrase = $this->secret('Business archive passphrase');
                if (! is_string($passphrase) || $passphrase === '') {
                    $this->error('A passphrase is required to verify this encrypted archive.');

                    return self::FAILURE;
                }

                $stage = 'decryption';
                $decryptedPath = $encryption->decryptFile($archivePath, $passphrase);
                $archivePath = $decryptedPath;
            }

            $report = $archives->verify(
                $archivePath,
                static function (string $currentStage) use (&$stage): void {
                    $stage = $currentStage;
                }
            );
        } catch (Throwable $exception) {
            $this->error('Business archive verification failed at ['.$stage.'].');
            $this->line($exception->getMessage());
            $this->comment('Verification is read-only. No business workspace or database records were imported.');

            return self::FAILURE;
        } finally {
            if (is_string($decryptedPath)) {
                @unlink($decryptedPath);
            }
        }

        $this->info('Business archive passed read-only verification.');
        $this->line('No business workspace or database records were changed.');
        $this->table(['Property', 'Value'], [
            ['Business', $report['business_name']],
            ['Archive format', (string) $report['format_version']],
            ['Tables', (string) $report['tables']],
            ['Records', (string) $report['records']],
            ['File payloads', (string) $report['files']],
            ['Expanded size', number_format($report['uncompressed_bytes']).' bytes'],
        ]);

        return self::SUCCESS;
    }
}
