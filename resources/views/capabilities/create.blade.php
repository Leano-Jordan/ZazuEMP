<x-app-layout>
    <x-slot:title>Add capability</x-slot:title>
    <x-slot:heading>Add business capability</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('capabilities.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-900">← Capabilities</a>
    </x-slot:headerAction>

    <form method="POST" action="{{ route('capabilities.store') }}" class="space-y-6">
        @csrf
        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="mb-5">
                <h2 class="font-semibold text-slate-950">Capability definition</h2>
                <p class="mt-1 text-sm text-slate-500">Keep this reusable. Pricing details can become richer when the quote layer is built.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
    <label class="block sm:col-span-2">
        <span class="text-sm font-medium text-slate-700">Name</span>
        <input name="name" value="{{ old('name', $capability->name ?? '') }}" required class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="e.g. Wedding catering">
        @error('name')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
    </label>

    <label class="block">
        <span class="text-sm font-medium text-slate-700">Category</span>
        <input name="category" value="{{ old('category', $capability->category ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="Catering, hire, decor...">
        @error('category')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
    </label>

    <label class="block">
        <span class="text-sm font-medium text-slate-700">Capability type</span>
        <select name="capability_type" required class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
            @foreach (['service' => 'Service', 'rental' => 'Rental', 'product' => 'Product', 'package' => 'Package', 'other' => 'Other'] as $value => $label)
                <option value="{{ $value }}" @selected(old('capability_type', $capability->capability_type ?? 'service') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('capability_type')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
    </label>

    <label class="block">
        <span class="text-sm font-medium text-slate-700">Pricing basis</span>
        <select name="pricing_basis" required class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
            @foreach (['custom' => 'Custom', 'fixed' => 'Fixed', 'per_unit' => 'Per unit', 'per_person' => 'Per person', 'per_hour' => 'Per hour', 'per_day' => 'Per day'] as $value => $label)
                <option value="{{ $value }}" @selected(old('pricing_basis', $capability->pricing_basis ?? 'custom') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('pricing_basis')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
    </label>

    <label class="block">
        <span class="text-sm font-medium text-slate-700">Default unit</span>
        <input name="default_unit" value="{{ old('default_unit', $capability->default_unit ?? '') }}" class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="Guests, chairs, hours...">
        @error('default_unit')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
    </label>

    <label class="block sm:col-span-2">
        <span class="text-sm font-medium text-slate-700">Description</span>
        <textarea name="description" rows="4" class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="What does this capability cover?">{{ old('description', $capability->description ?? '') }}</textarea>
        @error('description')<span class="mt-1 block text-xs text-rose-600">{{ $message }}</span>@enderror
    </label>

    <label class="flex items-center gap-3 sm:col-span-2">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $capability->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
        <span>
            <span class="block text-sm font-medium text-slate-700">Active capability</span>
            <span class="block text-xs text-slate-400">Available for future Work and quote selection.</span>
        </span>
    </label>
</div>
        </section>
        <div class="flex justify-end gap-3">
            <a href="{{ route('capabilities.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Cancel</a>
            <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Save capability</button>
        </div>
    </form>
</x-app-layout>