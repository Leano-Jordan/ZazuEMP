<?php

namespace App\Http\Controllers;

use App\Models\BusinessCapability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BusinessCapabilityController extends Controller
{
    public function index(Request $request): View
    {
        $capabilities = BusinessCapability::query()
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%' . $request->string('search') . '%'))
            ->orderByDesc('is_active')
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = BusinessCapability::query()
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
        $validated = $this->validated($request);
        $photoPath = $request->file('image')?->store('catalogue', 'public');

        BusinessCapability::create($validated + [
            'image_path' => $photoPath,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('capabilities.index')
            ->with('success', 'Catalogue item added.');
    }

    public function edit(BusinessCapability $capability): View
    {
        return view('capabilities.edit', [
            'capability' => $capability,
            'serviceCategories' => array_keys(config('zazu.service_categories')),
        ]);
    }

    public function update(Request $request, BusinessCapability $capability): RedirectResponse
    {
        $validated = $this->validated($request);
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

    private function validated(Request $request): array
    {
        $categories = array_keys(config('zazu.service_categories'));

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'capability_type' => ['required', 'in:service,rental,product,package,other'],
            'pricing_basis' => ['required', 'in:custom,fixed,per_unit,per_person,per_hour,per_day'],
            'default_price' => ['nullable', 'numeric', 'min:0'],
            'default_unit' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'category' => ['required', 'string', 'max:100', \Illuminate\Validation\Rule::in($categories)],
        ]);
    }
}
