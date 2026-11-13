<?php

namespace App\Support;

use App\Models\Business;
use App\Models\User;

class ExperienceLevel
{
    public const BASIC = 'basic';
    public const INTERMEDIATE = 'intermediate';
    public const ADVANCED = 'advanced';
    public const DEFAULT = self::INTERMEDIATE;

    public function selected(?User $user = null, ?Business $business = null): ?string
    {
        $user ??= auth()->user();
        $business ??= app(CurrentBusiness::class)->resolve($user);

        if (!$user || !$business) {
            return null;
        }

        $membership = $user->businesses()
            ->whereKey($business->id)
            ->first();

        $level = $membership?->pivot?->experience_level;

        return is_string($level) && array_key_exists($level, config('zazu.experience_levels', []))
            ? $level
            : null;
    }

    public function for(?User $user = null, ?Business $business = null, ?DateTimeInterface $at = null): string
    {
        $selected = $this->selected($user, $business) ?? self::DEFAULT;

        return $business
            ? $this->entitlements->clamp($selected, $this->entitlements->currentTier($business, $at))
            : $selected;
    }

    public function maximumFor(Business $business, ?DateTimeInterface $at = null): string
    {
        return $this->entitlements->currentTier($business, $at);
    }

    public function canSelect(Business $business, string $level, ?DateTimeInterface $at = null): bool
    {
        return $this->entitlements->canSelect($business, $level, $at);
    }

    /** @return list<string> */
    public function availableLevels(Business $business, ?DateTimeInterface $at = null): array
    {
        return $this->entitlements->availableTiers($business, $at);
    }

    public function isTrial(Business $business, ?DateTimeInterface $at = null): bool
    {
        return $this->entitlements->isTrial($business, $at);
    }

    public function trialEndsAt(Business $business): ?\\Illuminate\\Support\\Carbon
    {
        return $this->entitlements->trialEndsAt($business);
    }

    public function label(string $level): string
    {
        return (string) (config('zazu.experience_levels.'.$level.'.label') ?? ucfirst($level));
    }

    public function options(): array
    {
        return config('zazu.experience_levels', []);
    }
}
