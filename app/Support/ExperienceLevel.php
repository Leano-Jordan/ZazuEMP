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

    public function for(?User $user = null, ?Business $business = null): string
    {
        return $this->selected($user, $business) ?? self::DEFAULT;
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
