<?php

namespace App\Support;

use App\Models\Business;
use App\Models\User;
use DateTimeInterface;

class ExperienceLevel
{
    public const BASIC = 'basic';
    public const INTERMEDIATE = 'intermediate';
    public const ADVANCED = 'advanced';
    public const DEFAULT = self::INTERMEDIATE;

    public function __construct(private readonly BusinessEntitlement $entitlements)
    {
    }

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

        if ($this->isPlatformAdmin($user)) {
            return $selected;
        }

        return $business
            ? $this->entitlements->clamp($selected, $this->entitlements->currentTier($business, $at))
            : $selected;
    }

    public function maximumFor(Business $business, ?DateTimeInterface $at = null, ?User $user = null): string
    {
        return $this->isPlatformAdmin($user)
            ? self::ADVANCED
            : $this->entitlements->currentTier($business, $at);
    }

    public function canSelect(Business $business, string $level, ?DateTimeInterface $at = null, ?User $user = null): bool
    {
        return $this->isPlatformAdmin($user)
            ? array_key_exists($level, config('zazu.experience_levels', []))
            : $this->entitlements->canSelect($business, $level, $at);
    }

    /** @return list<string> */
    public function availableLevels(Business $business, ?DateTimeInterface $at = null, ?User $user = null): array
    {
        return $this->isPlatformAdmin($user)
            ? array_keys(config('zazu.experience_levels', []))
            : $this->entitlements->availableTiers($business, $at);
    }

    public function isPlatformAdmin(?User $user = null): bool
    {
        $user ??= auth()->user();
        $founderEmail = (string) config('zazu.platform_admin_email', '');

        return $user !== null
            && $founderEmail !== ''
            && hash_equals($founderEmail, strtolower(trim((string) $user->email)));
    }

    public function isTrial(Business $business, ?DateTimeInterface $at = null): bool
    {
        return $this->entitlements->isTrial($business, $at);
    }

    public function trialEndsAt(Business $business): ?\Illuminate\Support\Carbon
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
