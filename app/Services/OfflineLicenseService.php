<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessLicense;
use Illuminate\Support\Str;

class OfflineLicenseService
{
    public function current(Business $business): ?BusinessLicense
    {
        return $business->license;
    }

    public function isValid(Business $business): bool
    {
        return $this->current($business)?->isActive() ?? false;
    }

    /**
     * Import a Rosscore-issued Ed25519 licence token without a network request.
     * Token format: base64url(payload JSON).base64url(detached signature).
     * The configured value is a public key only; never place a signing key in this app.
     *
     * @throws \InvalidArgumentException When the token is malformed, untrusted or for another business.
     */
    public function importSignedLicense(Business $business, string $token): BusinessLicense
    {
        if (! function_exists('sodium_crypto_sign_verify_detached')) {
            throw new \InvalidArgumentException('Signed licence verification is unavailable on this PHP installation.');
        }

        $publicKeyEncoded = (string) config('zazu.licensing.public_key', '');
        $publicKey = base64_decode($publicKeyEncoded, true);

        if ($publicKey === false || strlen($publicKey) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            throw new \InvalidArgumentException('Signed licence verification is not configured.');
        }

        $token = trim($token);
        if ($token === '' || strlen($token) > 16384) {
            throw new \\InvalidArgumentException('The licence token is malformed.');
        }

        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            throw new \InvalidArgumentException('The licence token is malformed.');
        }

        $decode = static function (string $value): string|false {
            if ($value === '' || preg_match('/[^A-Za-z0-9_-]/', $value)) {
                return false;
            }

            $padding = (4 - strlen($value) % 4) % 4;

            return base64_decode(strtr($value.str_repeat('=', $padding), '-_', '+/'), true);
        };

        $payloadBytes = $decode($parts[0]);
        $signature = $decode($parts[1]);

        if ($payloadBytes === false || $signature === false || strlen($signature) !== SODIUM_CRYPTO_SIGN_BYTES
            || ! sodium_crypto_sign_verify_detached($signature, $payloadBytes, $publicKey)) {
            throw new \InvalidArgumentException('The licence signature is invalid.');
        }

        try {
            $payload = json_decode($payloadBytes, true, 32, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new \InvalidArgumentException('The signed licence payload is malformed.');
        }

        if (! is_array($payload)
            || ($payload['version'] ?? null) !== 1
            || ! is_int($payload['business_id'] ?? null)
            || $payload['business_id'] !== (int) $business->id
            || ! is_string($payload['license_id'] ?? null)
            || ! Str::isUuid($payload['license_id'])
            || ! is_string($payload['plan'] ?? null)
            || ! preg_match('/^[a-z][a-z0-9_-]{0,39}$/', $payload['plan'])
            || ! in_array($payload['status'] ?? null, ['active', 'revoked'], true)
            || ! is_string($payload['starts_at'] ?? null)
            || ! is_string($payload['issued_at'] ?? null)
            || ! array_key_exists('expires_at', $payload)
            || ! (is_string($payload['expires_at']) || $payload['expires_at'] === null)
            || ! array_key_exists('features', $payload)
            || ! is_array($payload['features'])) {
            throw new \InvalidArgumentException('The signed licence payload has invalid fields or belongs to another business.');
        }

        try {
            $startsAt = \Illuminate\Support\Carbon::parse($payload['starts_at']);
            $expiresAt = $payload['expires_at'] === null
                ? null
                : \Illuminate\Support\Carbon::parse($payload['expires_at']);
        } catch (\Throwable) {
            throw new \InvalidArgumentException('The signed licence dates are invalid.');
        }

        if ($expiresAt !== null && $expiresAt->lt($startsAt)) {
            throw new \InvalidArgumentException('The signed licence expiry precedes its start date.');
        }

        $existing = $business->license;
        $existingIssuedAt = $existing?->metadata['issued_at'] ?? null;
        if ($existing?->metadata['signed'] === true
            && is_string($existingIssuedAt)
            && $existing->license_id !== $payload['license_id']
            && $issuedAt->lte(\\Illuminate\\Support\\Carbon::parse($existingIssuedAt))) {
            throw new \\InvalidArgumentException('This licence is older than the entitlement already installed.');
        }

        return BusinessLicense::updateOrCreate(
            ['business_id' => $business->id],
            [
                'license_id' => $payload['license_id'],
                'plan' => $payload['plan'],
                'status' => $payload['status'],
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt,
                'features' => $payload['features'] ?: null,
                'metadata' => ['signed' => true, 'format_version' => 1, 'issued_at' => $issuedAt->toIso8601String()],
                'activated_at' => now(),
                'last_local_check_at' => now(),
            ],
        );
    }

    public function markLocalCheck(BusinessLicense $license): void
    {
        $license->forceFill(['last_local_check_at' => now()])->save();
    }
}