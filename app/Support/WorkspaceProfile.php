<?php

namespace App\Support;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Models\User;

class WorkspaceProfile
{
    public function __construct(private readonly NicheFocus $nicheFocus)
    {
    }

    /**
     * Build a presentation-only profile from the business's existing sources.
     * This profile never grants permissions or changes record visibility.
     *
     * @return array{
     *   focus_labels: array<int, string>,
     *   catalogue_categories: array<int, string>,
     *   active_offerings: int,
     *   active_rentals: int
     * }
     */
    public function for(User $user, Business $business): array
    {
        $activeCapabilities = BusinessCapability::query()
            ->where('business_id', $business->id)
            ->where('is_active', true);

        $categories = (clone $activeCapabilities)
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->filter(fn ($category) => is_string($category) && trim($category) !== '')
            ->values()
            ->all();

        $focuses = $this->nicheFocus->selectedMany($user, $business);
        foreach ($this->nicheFocus->matchingCategories($categories) as $matchedFocus) {
            if (!in_array($matchedFocus, $focuses, true)) {
                $focuses[] = $matchedFocus;
            }
        }

        $focusLabels = array_values(array_unique(array_map(
            fn (string $focus): string => $this->nicheFocus->label($focus),
            $focuses
        )));

        return [
            'focus_labels' => $focusLabels,
            'catalogue_categories' => $categories,
            'active_offerings' => (clone $activeCapabilities)->count(),
            'active_rentals' => (clone $activeCapabilities)->where('capability_type', 'rental')->count(),
        ];
    }
}
