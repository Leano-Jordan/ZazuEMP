<?php

namespace App\Services;

use DateTimeImmutable;
use Illuminate\Support\Str;

final class OfflineLicensePayloadFactory
{
    /**
     * @param array<string, mixed> $options
     * @return array{
     *   version: int,
     *   business_uuid: string,
     *   license_id: string,
     *   plan: string,
     *   status: string,
     *   starts_at: string,
     *   issued_at: string,
     *   expires_at: ?string,
     *   features: object
     * }
     */
    public function build(mixed $businessUuidValue, mixed $planValue, array $options): array
    {
        $status = $this->status($options['status'] ?? 'active');
        $startsAt = $this->startsAt($options['starts_at'] ?? null);

        return [
            'version' => 2,
            'business_uuid' => $this->businessUuid($businessUuidValue),
            'license_id' => $this->licenseId($options['license_id'] ?? null, $status),
            'plan' => $this->plan($planValue),
            'status' => $status,
            'starts_at' => $startsAt,
            'issued_at' => now('UTC')->format('Y-m-d\TH:i:s\Z'),
            'expires_at' => $this->expiry($startsAt, $options),
            'features' => $this->features($options['features'] ?? '{}'),
        ];
    }

    private function businessUuid(mixed $value): string
    {
        $businessUuid = strtolower(trim((string) $value));
        if (! Str::isUuid($businessUuid)) {
            throw new \InvalidArgumentException('Business licensing reference must be a valid UUID.');
        }

        return $businessUuid;
    }

    private function plan(mixed $value): string
    {
        $plan = (string) $value;
        if (! in_array($plan, ['basic', 'intermediate', 'advanced'], true)) {
            throw new \InvalidArgumentException('Plan must be basic, intermediate or advanced.');
        }

        return $plan;
    }

    private function status(mixed $value): string
    {
        $status = (string) $value;
        if (! in_array($status, ['active', 'revoked'], true)) {
            throw new \InvalidArgumentException('Status must be active or revoked.');
        }

        return $status;
    }

    private function licenseId(mixed $value, string $status): string
    {
        $providedId = trim((string) $value);
        $licenseId = $providedId !== '' ? $providedId : (string) Str::uuid();

        if (! Str::isUuid($licenseId)) {
            throw new \InvalidArgumentException('Licence ID must be a valid UUID.');
        }

        if ($status === 'revoked' && $providedId === '') {
            throw new \InvalidArgumentException('Revocation requires the existing licence ID.');
        }

        return $licenseId;
    }

    private function startsAt(mixed $value): string
    {
        $startsAt = trim((string) ($value ?: now('UTC')->format('Y-m-d\TH:i:s\Z')));
        $this->parseTimestamp($startsAt);

        return $startsAt;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function expiry(string $startsAt, array $options): ?string
    {
        $neverExpires = (bool) ($options['never_expires'] ?? false);
        $expiresAt = $options['expires_at'] ?? null;

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

    private function features(mixed $value): object
    {
        $featuresJson = (string) ($value ?? '{}');
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
}
