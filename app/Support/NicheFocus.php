<?php

namespace App\Support;

use App\Models\Business;
use App\Models\BusinessCapability;
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

    /**
     * Detect additional business capabilities that naturally overlap the
     * selected focus. These are informational only and never hide data.
     *
     * @return array<int, string>
     */
    public function supporting(Business $business, string $primaryNiche): array
    {
        $matched = [];

        foreach (config('zazu.niches', []) as $key => $definition) {
            if ($key === self::MIXED || $key === $primaryNiche) {
                continue;
            }

            $categories = $definition['capability_categories'] ?? [];
            if (!$categories) {
                continue;
            }

            $hasCapability = BusinessCapability::query()
                ->where('business_id', $business->id)
                ->where('is_active', true)
                ->whereIn('category', $categories)
                ->exists();

            if ($hasCapability) {
                $matched[] = $this->label($key);
            }
        }

        return $matched;
    }
}
