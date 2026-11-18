<?php

namespace App\Console\Commands;

use App\Services\OfflineLicensePayloadFactory;
use App\Services\OfflineLicenseTokenSigner;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Throwable;

final class ZazuIssueLicenseCommand extends Command
{
    protected $signature = 'zazu:license:issue
        {business_id : Local Zazu business ID}
        {plan : basic, intermediate or advanced}
        {--private-key-file= : Base64 Ed25519 secret-key file outside the Zazu project}
        {--license-id= : Existing UUID when renewing/revoking; otherwise generated}
        {--status=active : active or revoked}
        {--starts-at= : ISO-8601 timestamp with seconds and timezone}
        {--expires-at= : ISO-8601 expiry timestamp with seconds and timezone}
        {--never-expires : Explicitly issue an entitlement without an expiry}
        {--features= : JSON object of signed feature metadata}';

    protected $description = 'Issue a manually signed offline Zazu business licence';

    public function handle(
        OfflineLicensePayloadFactory $payloadFactory,
        OfflineLicenseTokenSigner $tokenSigner,
    ): int {
        try {
            $payload = $payloadFactory->build(
                $this->argument('business_id'),
                $this->argument('plan'),
                [
                    'license_id' => $this->option('license-id'),
                    'status' => $this->option('status'),
                    'starts_at' => $this->option('starts-at'),
                    'expires_at' => $this->option('expires-at'),
                    'never_expires' => $this->option('never-expires'),
                    'features' => $this->option('features'),
                ],
            );
            $token = $tokenSigner->sign($payload, (string) $this->option('private-key-file'));
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } catch (Throwable) {
            $this->error('Zazu could not issue the licence. Check the signing configuration and try again.');

            return self::FAILURE;
        }

        $this->info('Signed Zazu licence created.');
        $this->line('Business ID: '.$payload['business_id']);
        $this->line('Licence ID: '.$payload['license_id']);
        $this->line('Plan: '.$payload['plan']);
        $this->line('Status: '.$payload['status']);
        $this->line('Issued at: '.$payload['issued_at']);
        $this->line('Expires at: '.($payload['expires_at'] ?? 'Never'));
        $this->newLine();
        $this->line('TOKEN: '.$token);
        $this->warn('Send the token to the matching business owner only. Keep the signing key private.');

        return self::SUCCESS;
    }
}
