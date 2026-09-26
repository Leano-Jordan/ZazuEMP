<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        $customers = Customer::with('primaryContact')
            ->withCount('events')
            ->latest()
            ->paginate(15);

        return view('customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:customers,name'],
            'notes' => ['nullable', 'string'],
            'primary_contact_name' => ['required', 'string', 'max:255'],
            'primary_contact_phone' => ['nullable', 'string', 'max:50'],
            'primary_contact_email' => ['nullable', 'email', 'max:255'],
        ]);

        $customer = Customer::create([
            'name' => $validated['name'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $customer->contacts()->create([
            'name' => $validated['primary_contact_name'],
            'phone' => $validated['primary_contact_phone'] ?? null,
            'email' => $validated['primary_contact_email'] ?? null,
            'label' => 'Primary',
            'is_primary' => true,
        ]);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer): View
    {
        $customer->load([
            'contacts',
            'events' => fn ($query) => $query->latest('event_date')->latest(),
        ]);

        return view('customers.show', compact('customer'));
    }
}
