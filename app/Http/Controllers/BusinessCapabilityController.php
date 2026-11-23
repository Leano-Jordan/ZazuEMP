<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Support\CurrentBusiness;
use App\Support\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BusinessCapabilityController extends Controller
{
    public function index(Request $request): View
    {
        $business = $this->business($request);
        $isOwner = app(CurrentBusiness::class)->hasRole('owner', $request->user(), $business);

        // Owners retain visibility of disabled entries so they can restore them.
        // Operational members only see offerings that can actually be selected.
        $base = BusinessCapability::query()
            ->where('business_id', $business->id)
            ->when(!$isOwner, fn ($query) => $query->where('is_active', true))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%' . $request->string('search') . '%'));

        $capabilities = (clone $base)
            ->orderByDesc('is_active')
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $equipment = (clone $base)
            ->where('capability_type', 'rental')
            ->withCount([
                'assets',
                'assets as available_assets_count' => fn ($query) => $query->whereNotIn('status', ['maintenance', 'retired']),
            ])
            ->orderByDesc('is_active')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        // Keep the catalogue useful for every business type: non-rental capabilities
        // include DJ, decor, photography, catering, products and packaged services.
        $services = (clone $base)
            ->whereIn('capability_type', ['package', 'service', 'product', 'other'])
            ->orderByDesc('is_active')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $categories = BusinessCapability::query()
            ->where('business_id', $business->id)
            ->when(!$isOwner, fn ($query) => $query->where('is_active', true))
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $canViewAssets = app(PermissionService::class)->allows('assets.view', $request->user(), $business);

        return view('capabilities.index', compact('capabilities', 'categories', 'isOwner', 'equipment', 'services', 'canViewAssets'));
    }

    public function create(Request $request): View
    {
        $business = $this->business($request);

        return view('capabilities.create', [
            'serviceCategories' => array_keys(config('zazu.service_categories')),
            'currencies' => config('zazu.currencies'),
            'defaultCurrency' => $business->currency ?? 'ZAR',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $business = $this->business($request);
        $request->merge(['currency' => strtoupper((string) $request->input('currency', $business->currency ?? 'ZAR'))]);
        $validated = $this->validated($request);

        $photoPath = $request->file('image')?->store('catalogue', 'public');

        try {
            DB::transaction(function () use ($validated, $business, $photoPath, $request): void {
                Business::query()->whereKey($business->id)->lockForUpdate()->firstOrFail();

                BusinessCapability::create($validated + [
                    'business_id' => $business->id,
                    'image_path' => $photoPath,
                    'is_active' => $request->boolean('is_active', true),
                ]);
            });
        } catch (\Throwable $e) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $e;
        }

        return redirect()
            ->route('capabilities.index')
            ->with('success', 'Catalogue item added.');
    }

    public function edit(Request $request, BusinessCapability $capability): View
    {
        $business = $this->business($request);
        $this->ensureBusiness($capability, $business);

        return view('capabilities.edit', [
            'capability' => $capability,
            'serviceCategories' => array_keys(config('zazu.service_categories')),
            'currencies' => config('zazu.currencies'),
            'defaultCurrency' => $business->currency ?? 'ZAR',
        ]);
    }

    public function update(Request $request, BusinessCapability $capability): RedirectResponse
    {
        $business = $this->business($request);
        $this->ensureBusiness($capability, $business);

        $request->merge(['currency' => strtoupper((string) $request->input('currency', $capability->currency ?? $business->currency ?? 'ZAR'))]);
        $validated = $this->validated($request, $capability->category);
        $oldImagePath = null;
        $newImagePath = $request->file('image')?->store('catalogue', 'public');

        try {
            DB::transaction(function () use ($business, $capability, $validated, $newImagePath, $request, &$oldImagePath): void {
                Business::query()->whereKey($business->id)->lockForUpdate()->firstOrFail();

                $lockedCapability = BusinessCapability::query()
                    ->where('business_id', $business->id)
                    ->whereKey($capability->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $oldImagePath = $lockedCapability->image_path;
                $lockedCapability->update($validated + [
                    'image_path' => $newImagePath ?: $lockedCapability->image_path,
                    'is_active' => $request->boolean('is_active'),
                ]);
            });
        } catch (\Throwable $e) {
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            throw $e;
        }

        if ($newImagePath && $oldImagePath) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return redirect()
            ->route('capabilities.index')
            ->with('success', 'Catalogue item updated.');
    }

    private function business(Request $request): Business
    {
        return app(CurrentBusiness::class)->model($request->user());
    }

    private function ensureBusiness(BusinessCapability $capability, Business $business): void
    {
        abort_unless((int) $capability->business_id === (int) $business->id, 404);
    }

    private function validated(Request $request, ?string $currentCategory = null): array
    {
        $categories = array_values(array_unique(array_filter([...array_keys(config('zazu.service_categories')), $currentCategory])));

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'capability_type' => ['required', 'in:service,rental,product,package,other'],
            'pricing_basis' => ['required', 'in:custom,fixed,per_unit,per_person,per_hour,per_day'],
            'default_price' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
            'currency' => ['required', Rule::in(array_keys(config('zazu.currencies')))],
            'default_unit' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'category' => ['required', 'string', 'max:100', 'in:' . implode(',', $categories)],
        ]);
    }
}
