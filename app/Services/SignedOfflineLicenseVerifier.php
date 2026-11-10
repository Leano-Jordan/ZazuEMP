<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SignedOfflineLicenseVerifier
{
    /**
     * @return array{
     *   payload: array<string, mixed>,
     *   dates: array{starts_at: Carbon, issued_at: Carbon, expires_at: ?Carbon}
     * }
     */
    public function verifyForBusiness(string $token, int $businessId): array
    {
        $payloadBytes = $this->verifiedPayloadBytes($token);
        $payload = $this->decodePayload($payloadBytes);
        $this->assertPayloadBelongsToBusiness($payload, $businessId);

        return [
            'payload' => $payload,
            'dates' => $this->parsePayloadDates($payload),
        ];
    }

    private function verifiedPayloadBytes(string $token): string
    {
        if (! function_exists('sodium_crypto_sign_verify_detached')) {
            throw new \InvalidArgumentException('Signed licence verification is unavailable on this PHP installation.');
        }

        $publicKey = base64_decode((string) config('zazu.licensing.public_key', ''), true);
        if ($publicKey === false || strlen($publicKey) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            throw new \InvalidArgumentException('Signed licence verification is not configured.');
        }

        $parts = $this->splitToken($token);
        $payloadBytes = $this->decodeBase64Url($parts[0]);
        $signature = $this->decodeBase64Url($parts[1]);

        if ($payloadBytes === false || $signature === false
            || strlen($signature) !== SODIUM_CRYPTO_SIGN_BYTES
            || ! sodium_crypto_sign_verify_detached($signature, $payloadBytes, $publicKey)) {
            throw new \InvalidArgumentException('The licence signature is invalid.');
        }

        return $payloadBytes;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitToken(string $token): array
    {
        $token = trim($token);
        if ($token === '' || strlen($token) > 16384) {
            throw new \InvalidArgumentException('The licence token is malformed.');
        }

        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            throw new \InvalidArgumentException('The licence token is malformed.');
        }

        return $parts;
    }

    private function decodeBase64Url(string $value): string|false
    {
        if ($value === '' || preg_match('/[^A-Za-z0-9_-]/', $value)) {
            return false;
        }

        $padding = (4 - strlen($value) % 4) % 4;

        return base64_decode(strtr($value.str_repeat('=', $padding), '-_', '+/'), true);
    }

    /**
     * @throws \InvalidArgumentException
     * @return array<string, mixed>
     */
    private function decodePayload(string $payloadBytes): array
    {
        try {
            $payload = json_decode($payloadBytes, true, 32, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new \InvalidArgumentException('The signed licence payload is malformed.');
        }

        if (! is_array($payload)) {
            throw new \InvalidArgumentException('The signed licence payload is malformed.');
        }

        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function assertPayloadBelongsToBusiness(array $payload, int $businessId): void
    {
        if (($payload['version'] ?? null) !== 1
            || ! is_int($payload['business_id'] ?? null)
            || $payload['business_id'] !== $businessId
            || ! $this->hasValidLicenseIdentity($payload)
            || ! $this->hasValidEntitlementFields($payload)) {
            throw new \InvalidArgumentException('The signed licence payload has invalid fields or belongs to another business.');
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function hasValidLicenseIdentity(array $payload): bool
    {
        return is_string($payload['license_id'] ?? null)
            && Str::isUuid($payload['license_id'])
            && is_string($payload['plan'] ?? null)
            && (bool) preg_match('/^[a-z][a-z0-9_-]{0,39}$/', $payload['plan']);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function hasValidEntitlementFields(array $payload): bool
    {
        return in_array($payload['status'] ?? null, ['active', 'revoked'], true)
            && is_string($payload['starts_at'] ?? null)
            && is_string($payload['issued_at'] ?? null)
            && array_key_exists('expires_at', $payload)
            && (is_string($payload['expires_at']) || $payload['expires_at'] === null)
            && array_key_exists('features', $payload)
            && is_array($payload['features']);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{starts_at: Carbon, issued_at: Carbon, expires_at: ?Carbon}
     */
    private function parseExplicitTimezoneDate(string $value): Carbon
    {
        if (! preg_match('/^\\d{4}-\\d{2}-\\d{2}T\\d{2}:\\d{2}:\\d{2}(?:Z|[+-]\\d{2}:\\d{2})$/D', $value)) {
            throw new \InvalidArgumentException('A signed licence timestamp must include seconds and an explicit timezone.');
        }

        $date = Carbon::parse($value);
        $normalizedValue = str_ends_with($value, 'Z')
            ? substr($value, 0, -1).'+00:00'
            : $value;

        if ($date->format('Y-m-d\\TH:i:sP') !== $normalizedValue) {
            throw new \InvalidArgumentException('A signed licence timestamp is not a valid calendar date.');
        }

        return $date;
    }

    private function parsePayloadDates(array $payload): array
    {
        try {
            $startsAt = $this->parseExplicitTimezoneDate($payload['starts_at']);
            $issuedAt = $this->parseExplicitTimezoneDate($payload['issued_at']);
            $expiresAt = $payload['expires_at'] === null
                ? null
                : $this->parseExplicitTimezoneDate($payload['expires_at']);
        } catch (\Throwable) {
            throw new \InvalidArgumentException('The signed licence dates are invalid.');
        }

        if ($expiresAt !== null && $expiresAt->lt($startsAt)) {
            throw new \InvalidArgumentException('The signed licence expiry precedes its start date.');
        }

        return [
            'starts_at' => $startsAt,
            'issued_at' => $issuedAt,
            'expires_at' => $expiresAt,
        ];
    }
}
