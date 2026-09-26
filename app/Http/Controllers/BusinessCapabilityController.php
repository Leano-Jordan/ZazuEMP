<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BusinessCapabilityController extends Controller
{
    public function index(Request $request): View
    {
        $business = $this->business($request);

        $capabilities = BusinessCapability::query()
            ->where('business_id', $business->id)
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%' . $request->string('search') . '%'))
            ->orderByDesc('is_active')
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = BusinessCapability::query()
            ->where('business_id', $business->id)
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('capabilities.index', compact('capabilities', 'categories'));
    }

    public function create(): View
    {
        return view('capabilities.create', [
            'serviceCategories' => array_keys(config('zazu.service_categories')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $business = $this->business($request);
        $validated = $this->validated($request);

        $photoPath = $request->file('image')?->store('catalogue', 'public');

        BusinessCapability::create($validated + [
            'business_id' => $business->id,
            'image_path' => $photoPath,
            'is_active' => $request->boolean('is_active', true),
        ]);

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
        ]);
    }

    public function update(Request $request, BusinessCapability $capability): RedirectResponse
    {
        $business = $this->business($request);
        $this->ensureBusiness($capability, $business);

        $validated = $this->validated($request, $capability->category);
        $oldImagePath = $capability->image_path;
        $newImagePath = $request->file('image')?->store('catalogue', 'public');

        $capability->update($validated + [
            'image_path' => $newImagePath ?: $capability->image_path,
            'is_active' => $request->boolean('is_active'),
        ]);

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
            'default_price' => ['nullable', 'numeric', 'min:0'],
            'default_unit' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'category' => ['required', 'string', 'max:100', 'in:' . implode(',', $categories)],
        ]);
    }
}
