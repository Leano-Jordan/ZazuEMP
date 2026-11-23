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

    /**
     * Return every saved business focus, retaining the legacy primary focus
     * for memberships created before multi-focus onboarding was introduced.
     *
     * @return array<int, string>
     */
    public function selectedMany(?User $user = null, ?Business $business = null): array
    {
        $user ??= auth()->user();
        $business ??= app(CurrentBusiness::class)->resolve($user);

        if (! $user || ! $business) {
            return [];
        }

        [$membership, $ownerMembership] = $this->focusMemberships($user, $business);
        $focuses = $this->normaliseSavedFocuses(
            $membership?->pivot?->business_focuses,
            $ownerMembership?->pivot?->business_focuses,
        );
        $primary = $this->primaryFocusWithOwnerFallback($user, $business, $ownerMembership);

        if ($primary && ! in_array($primary, $focuses, true)) {
            array_unshift($focuses, $primary);
        }

        return $focuses ?: [self::DEFAULT];
    }

    /**
     * @return array{0: mixed, 1: mixed}
     */
    private function focusMemberships(User $user, Business $business): array
    {
        return [
            $user->businesses()->whereKey($business->id)->first(),
            $business->users()->wherePivot('role', 'owner')->first(),
        ];
    }

    /**
     * @param array<int, string>|string|null $saved
     * @param array<int, string>|string|null $ownerSaved
     * @return array<int, string>
     */
    private function normaliseSavedFocuses(mixed $saved, mixed $ownerSaved): array
    {
        if (! is_array($saved) && (! is_string($saved) || trim($saved) === '')) {
            $saved = $ownerSaved;
        }

        $decoded = is_array($saved) ? $saved : (is_string($saved) ? json_decode($saved, true) : []);
        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_unique(array_filter(
            $decoded,
            static fn ($focus) => is_string($focus) && array_key_exists($focus, config('zazu.niches', []))
        )));
    }

    private function primaryFocusWithOwnerFallback(User $user, Business $business, mixed $ownerMembership): ?string
    {
        $primary = $this->selected($user, $business);
        if ($primary) {
            return $primary;
        }

        $ownerPrimary = $ownerMembership?->pivot?->primary_niche;

        return is_string($ownerPrimary) && array_key_exists($ownerPrimary, config('zazu.niches', []))
            ? $ownerPrimary
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

        $focuses = array_values(array_filter(
            $this->selectedMany(auth()->user(), $business),
            static fn (string $focus) => $focus !== self::MIXED
        ));
        if ($primaryNiche !== self::MIXED && !in_array($primaryNiche, $focuses, true)) {
            array_unshift($focuses, $primaryNiche);
        }
        foreach ($this->matchingCategories($categories) as $matched) {
            if (!in_array($matched, $focuses, true)) {
                $focuses[] = $matched;
            }
        }

        return $focuses ?: [self::MIXED];
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
