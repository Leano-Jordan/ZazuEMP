<?php

namespace App\Support;

use App\Models\Business;
use App\Models\BusinessLicense;
use DateTimeInterface;
use Illuminate\Support\Carbon;

class BusinessEntitlement
{
    public const BASIC = 'basic';
    public const INTERMEDIATE = 'intermediate';
    public const ADVANCED = 'advanced';

    private const RANK = [
        self::BASIC => 1,
        self::INTERMEDIATE => 2,
        self::ADVANCED => 3,
    ];

    public function currentTier(Business $business, ?DateTimeInterface $at = null): string
    {
        $instant = $at ? Carbon::instance($at) : now();
        $license = $business->license()->first();

        // An installed licence always supersedes a trial, including when expired or revoked.
        if ($license instanceof BusinessLicense) {
            if (! $license->isActive($instant)) {
                return self::BASIC;
            }

            return $this->canonicalPlan($license->plan) ?? self::BASIC;
        }

        $createdAt = $business->created_at;
        if (! $createdAt) {
            return self::BASIC;
        }

        $trialEndsAt = $this->trialEndsAt($business);
        return $trialEndsAt !== null
            && $createdAt->lte($instant)
            && $instant->lt($trialEndsAt)
                ? self::ADVANCED
                : self::BASIC;
    }

    public function trialEndsAt(Business $business): ?Carbon
    {
        if (! $business->created_at || $business->license()->exists()) {
            return null;
        }

        return $business->created_at->copy()->addDays(
            max(0, (int) config('zazu.licensing.trial_days', 30))
        );
    }

    public function isTrial(Business $business, ?DateTimeInterface $at = null): bool
    {
        if (! $business->created_at || $business->license()->exists()) {
            return false;
        }

        $instant = $at ? Carbon::instance($at) : now();
        $endsAt = $this->trialEndsAt($business);

        return $endsAt !== null
            && $business->created_at->lte($instant)
            && $instant->lt($endsAt);
    }

    public function canSelect(Business $business, string $tier, ?DateTimeInterface $at = null): bool
    {
        $maximum = $this->currentTier($business, $at);

        return isset(self::RANK[$tier])
            && self::RANK[$tier] <= self::RANK[$maximum];
    }

    /** @return list<string> */
    public function availableTiers(Business $business, ?DateTimeInterface $at = null): array
    {
        $maximumRank = self::RANK[$this->currentTier($business, $at)];

        return array_values(array_filter(
            array_keys(self::RANK),
            static fn (string $tier): bool => self::RANK[$tier] <= $maximumRank
        ));
    }

    public function clamp(string $selected, string $maximum): string
    {
        if (! isset(self::RANK[$selected], self::RANK[$maximum])) {
            return self::BASIC;
        }

        return self::RANK[$selected] <= self::RANK[$maximum] ? $selected : $maximum;
    }

    private function canonicalPlan(string $plan): ?string
    {
        return match ($plan) {
            self::BASIC => self::BASIC,
            self::INTERMEDIATE => self::INTERMEDIATE,
            self::ADVANCED, 'solo' => self::ADVANCED,
            default => null,
        };
    }
}
