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
     * Resolve configured service focus keys against the exact saved category taxonomy.
     *
     * @param array<int, string|null> $categories
     * @return array<int, string>
     */
    public function matchingCategories(array $categories): array
    {
        $categories = array_values(array_unique(array_filter(
            array_map(static fn ($category) => is_string($category) ? trim($category) : '', $categories)
        )));

        if (!$categories) {
            return [];
        }

        $matched = [];
        foreach (config('zazu.niches', []) as $key => $definition) {
            if ($key === self::MIXED) {
                continue;
            }

            $focusCategories = $definition['capability_categories'] ?? [];
            if ($focusCategories && array_intersect($focusCategories, $categories)) {
                $matched[] = $key;
            }
        }

        return $matched;
    }

    /**
     * Combine a member's explicit primary focus with additional active catalogue
     * categories. This informs guidance only; it does not grant permissions or
     * hide/reconfigure modules.
     *
     * @return array<int, string>
     */
    public function activeFor(Business $business, string $primaryNiche): array
    {
        $categories = BusinessCapability::query()
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->whereNotNull('category')
            ->pluck('category')
            ->all();

        $focuses = $primaryNiche !== self::MIXED ? [$primaryNiche] : [];
        foreach ($this->matchingCategories($categories) as $matched) {
            if (!in_array($matched, $focuses, true)) {
                $focuses[] = $matched;
            }
        }

        return array_slice($focuses ?: [self::MIXED], 0, 3);
    }

    /**
     * Detect additional business capabilities that naturally overlap the
     * selected focus. These are informational only and never hide data.
     *
     * @return array<int, string>
     */
    public function supporting(Business $business, string $primaryNiche): array
    {
        $categoriesInUse = BusinessCapability::query()
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->pluck('category')
            ->filter()
            ->unique()
            ->values()
            ->all();

        return array_values(array_map(
            fn (string $niche) => $this->label($niche),
            array_filter(
                $this->matchingCategories($categoriesInUse),
                fn (string $niche) => $niche !== $primaryNiche
            )
        ));
    }
}
