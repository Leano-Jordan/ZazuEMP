<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Models\EventRequirement;
use App\Support\CurrentBusiness;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResourceOverviewController extends Controller
{
    public function suppliers(Request $request): View
    {
        $business = $this->business($request);

        $resourceRequirements = $this->resourceRequirements($business)
            ->whereHas('capability', fn (Builder $query) => $query->whereIn('capability_type', ['product', 'rental']))
            ->get()
            ->sortBy(fn (EventRequirement $requirement) => $requirement->event?->event_date?->timestamp ?? PHP_INT_MAX)
            ->values();

        $resourceCatalogue = BusinessCapability::query()
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->whereIn('capability_type', ['product', 'rental'])
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('suppliers.index', [
            'resourceRequirements' => $resourceRequirements,
            'resourceCatalogue' => $resourceCatalogue,
            'resourceRequirementCount' => $resourceRequirements->count(),
            'resourceCatalogueCount' => $resourceCatalogue->count(),
        ]);
    }

    public function inventory(Request $request): View
    {
        $business = $this->business($request);

        $products = BusinessCapability::query()
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->where('capability_type', 'product')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $demand = $this->resourceRequirements($business)
            ->whereHas('capability', fn (Builder $query) => $query->where('capability_type', 'product'))
            ->get()
            ->sortBy(fn (EventRequirement $requirement) => $requirement->event?->event_date?->timestamp ?? PHP_INT_MAX)
            ->values();

        $plannedUnits = $demand->sum(fn (EventRequirement $requirement) => (float) $requirement->quantity);

        return view('inventory.index', [
            'products' => $products,
            'demand' => $demand,
            'plannedUnits' => $plannedUnits,
        ]);
    }

    public function assets(Request $request): View
    {
        $business = $this->business($request);

        $rentalCapabilities = BusinessCapability::query()
            ->where('business_id', $business->id)
            ->where('is_active', true)
            ->where('capability_type', 'rental')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $demand = $this->resourceRequirements($business)
            ->whereHas('capability', fn (Builder $query) => $query->where('capability_type', 'rental'))
            ->get()
            ->sortBy(fn (EventRequirement $requirement) => $requirement->event?->event_date?->timestamp ?? PHP_INT_MAX)
            ->values();

        return view('assets.index', [
            'rentalCapabilities' => $rentalCapabilities,
            'demand' => $demand,
            'demandJobs' => $demand->pluck('event_id')->unique()->count(),
        ]);
    }

    private function business(Request $request): Business
    {
        return app(CurrentBusiness::class)->model($request->user());
    }

    private function resourceRequirements(Business $business): Builder
    {
        return EventRequirement::query()
            ->whereHas('event', fn (Builder $query) => $query
                ->where('business_id', $business->id)
                ->whereNotIn('status', ['completed', 'cancelled']))
            ->with(['event.customer', 'capability'])
            ->orderBy('created_at');
    }
}
