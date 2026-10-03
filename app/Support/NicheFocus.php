<?php

namespace App\Support;

use App\Models\Business;
use App\Models\User;

class NicheFocus
{
    public const CHAIRS_TENTS = 'chairs_tents';
    public const CATERING_BAKING = 'catering_baking';
    public const SOUND_DJ = 'sound_dj';
    public const MIXED = 'mixed';
    public const DEFAULT = self::MIXED;

    public function selected(?User $user = null, ?Business $business = null): ?string
    {
        $user ??= auth()->user();
        $business ??= app(CurrentBusiness::class)->resolve($user);

        if (!$user || !$business) {
            return null;
        }

        $membership = $user->businesses()->whereKey($business->id)->first();
        $niche = $membership?->pivot?->primary_niche;

        return is_string($niche) && array_key_exists($niche, config('zazu.niches', []))
            ? $niche
            : null;
    }

    public function for(?User $user = null, ?Business $business = null): string
    {
        return $this->selected($user, $business) ?? self::DEFAULT;
    }

    public function label(string $niche): string
    {
        return (string) (config('zazu.niches.'.$niche.'.label') ?? ucfirst(str_replace('_', ' ', $niche)));
    }

    public function definition(string $niche): array
    {
        return (array) (config('zazu.niches.'.$niche) ?? config('zazu.niches.'.self::DEFAULT, []));
    }

    public function options(): array
    {
        return config('zazu.niches', []);
    }
}
