<?php

namespace App\Http\Controllers;

use App\Models\BusinessCapability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessCapabilityController extends Controller
{
    public function index(): View
    {
        $capabilities = BusinessCapability::query()
            ->orderByDesc('is_active')
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(20);

        return view('capabilities.index', compact('capabilities'));
    }

    public function create(): View
    {
        return view('capabilities.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        BusinessCapability::create($validated + ['is_active' => $request->boolean('is_active')]);

        return redirect()
            ->route('capabilities.index')
            ->with('success', 'Business capability created.');
    }

    public function edit(BusinessCapability $capability): View
    {
        return view('capabilities.edit', compact('capability'));
    }

    public function update(Request $request, BusinessCapability $capability): RedirectResponse
    {
        $validated = $this->validated($request);

        $capability->update($validated + ['is_active' => $request->boolean('is_active')]);

        return redirect()
            ->route('capabilities.index')
            ->with('success', 'Business capability updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'capability_type' => ['required', 'in:service,rental,product,package,other'],
            'pricing_basis' => ['required', 'in:custom,fixed,per_unit,per_person,per_hour,per_day'],
            'default_unit' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);
    }
}
