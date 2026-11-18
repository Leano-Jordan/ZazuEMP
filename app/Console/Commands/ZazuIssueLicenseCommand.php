<?php

namespace App\Console\Commands;

use DateTimeImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

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

    public function handle(): int
    {
        try {
            $payload = $this->buildPayload();
            $secretKey = $this->loadSecretKey();
            $payloadBytes = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            $signature = sodium_crypto_sign_detached($payloadBytes, $secretKey);
            $token = $this->base64UrlEncode($payloadBytes).'.'.$this->base64UrlEncode($signature);
        } catch (\InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } catch (\Throwable) {
            $this->error('Zazu could not issue the licence. Check the signing configuration and try again.');

            return self::FAILURE;
        }

        if (strlen($token) > 16384) {
            $this->error('The signed licence exceeds the supported token size.');

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

    /**
     * @return array{
     *   version: int,
     *   business_id: int,
     *   license_id: string,
     *   plan: string,
     *   status: string,
     *   starts_at: string,
     *   issued_at: string,
     *   expires_at: ?string,
     *   features: object
     * }
     */
    private function buildPayload(): array
    {
        $startsAt = $this->startsAt();

        return [
            'version' => 1,
            'business_id' => $this->businessId(),
            'license_id' => $this->licenseId(),
            'plan' => $this->plan(),
            'status' => $this->status(),
            'starts_at' => $startsAt,
            'issued_at' => now('UTC')->format('Y-m-d\TH:i:s\Z'),
            'expires_at' => $this->expiry($startsAt),
            'features' => $this->features(),
        ];
    }

    private function businessId(): int
    {
        $businessId = filter_var($this->argument('business_id'), FILTER_VALIDATE_INT);
        if (! is_int($businessId) || $businessId < 1) {
            throw new \InvalidArgumentException('Business ID must be a positive integer.');
        }

        return $businessId;
    }

    private function plan(): string
    {
        $plan = (string) $this->argument('plan');
        if (! in_array($plan, ['basic', 'intermediate', 'advanced'], true)) {
            throw new \InvalidArgumentException('Plan must be basic, intermediate or advanced.');
        }

        return $plan;
    }

    private function status(): string
    {
        $status = (string) $this->option('status');
        if (! in_array($status, ['active', 'revoked'], true)) {
            throw new \InvalidArgumentException('Status must be active or revoked.');
        }

        return $status;
    }

    private function licenseId(): string
    {
        $providedId = trim((string) $this->option('license-id'));
        $licenseId = $providedId !== '' ? $providedId : (string) Str::uuid();

        if (! Str::isUuid($licenseId)) {
            throw new \InvalidArgumentException('Licence ID must be a valid UUID.');
        }

        if ($this->status() === 'revoked' && $providedId === '') {
            throw new \InvalidArgumentException('Revocation requires the existing licence ID.');
        }

        return $licenseId;
    }

    private function startsAt(): string
    {
        $startsAt = trim((string) ($this->option('starts-at') ?: now('UTC')->format('Y-m-d\TH:i:s\Z')));
        $this->parseTimestamp($startsAt);

        return $startsAt;
    }

    private function expiry(string $startsAt): ?string
    {
        $neverExpires = (bool) $this->option('never-expires');
        $expiresAt = $this->option('expires-at');

        if ($neverExpires === (bool) $expiresAt) {
            throw new \InvalidArgumentException('Supply an expiry timestamp or explicitly select --never-expires, not both.');
        }

        if ($expiresAt === null) {
            return null;
        }

        $expiresAt = trim((string) $expiresAt);
        if ($this->parseTimestamp($expiresAt) < $this->parseTimestamp($startsAt)) {
            throw new \InvalidArgumentException('Licence expiry cannot precede its start date.');
        }

        return $expiresAt;
    }

    private function features(): object
    {
        $featuresJson = (string) ($this->option('features') ?? '{}');
        if (strlen($featuresJson) > 4096) {
            throw new \InvalidArgumentException('Feature metadata exceeds the supported size.');
        }

        try {
            $featuresObject = json_decode($featuresJson, false, 16, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new \InvalidArgumentException('Feature metadata must be a valid JSON object.');
        }

        if (! is_object($featuresObject)) {
            throw new \InvalidArgumentException('Feature metadata must be a JSON object.');
        }

        return $featuresObject;
    }

    private function loadSecretKey(): string
    {
        $this->assertSodiumAvailable();
        $realPath = $this->externalKeyPath();
        $secretKey = $this->decodeSecretKey($realPath);
        $publicKey = $this->configuredPublicKey();

        if (! hash_equals($publicKey, sodium_crypto_sign_publickey_from_secretkey($secretKey))) {
            throw new \InvalidArgumentException('The external signing key does not match the configured Zazu public key.');
        }

        return $secretKey;
    }

    private function assertSodiumAvailable(): void
    {
        if (! function_exists('sodium_crypto_sign_detached')) {
            throw new \InvalidArgumentException('PHP Sodium is required to issue signed Zazu licences.');
        }
    }

    private function externalKeyPath(): string
    {
        $keyFile = trim((string) $this->option('private-key-file'));
        $realPath = $keyFile !== '' ? realpath($keyFile) : false;
        if ($realPath === false || ! is_file($realPath) || ! is_readable($realPath)) {
            throw new \InvalidArgumentException('A readable external signing-key file is required.');
        }

        $projectRoot = realpath(base_path()) ?: base_path();
        $projectPrefix = strtolower(rtrim(str_replace('\\', '/', $projectRoot), '/').'/');
        $keyPath = strtolower(str_replace('\\', '/', $realPath));

        if (str_starts_with($keyPath, $projectPrefix)) {
            throw new \InvalidArgumentException('The signing key must be stored outside the Zazu project.');
        }

        return $realPath;
    }

    private function decodeSecretKey(string $path): string
    {
        $encodedKey = file_get_contents($path);
        $secretKey = is_string($encodedKey) ? base64_decode(trim($encodedKey), true) : false;

        if ($secretKey === false || strlen($secretKey) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
            throw new \InvalidArgumentException('The external signing-key file is not a valid base64 Ed25519 secret key.');
        }

        return $secretKey;
    }

    private function configuredPublicKey(): string
    {
        $configuredPublicKey = base64_decode((string) config('zazu.licensing.public_key', ''), true);

        if ($configuredPublicKey === false || strlen($configuredPublicKey) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            throw new \InvalidArgumentException('The matching ZAZU_LICENSE_PUBLIC_KEY is not configured.');
        }

        return $configuredPublicKey;
    }

    private function parseTimestamp(string $value): DateTimeImmutable
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:Z|[+-]\d{2}:\d{2})$/D', $value)) {
            throw new \InvalidArgumentException('Licence timestamps must include seconds and an explicit timezone.');
        }

        $normalized = str_ends_with($value, 'Z')
            ? substr($value, 0, -1).'+00:00'
            : $value;
        $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i:sP', $normalized);
        $errors = DateTimeImmutable::getLastErrors();

        if ($date === false
            || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $date->format('Y-m-d\TH:i:sP') !== $normalized) {
            throw new \InvalidArgumentException('Licence timestamp is not a valid calendar date.');
        }

        return $date;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
