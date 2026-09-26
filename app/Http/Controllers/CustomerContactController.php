<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerContactController extends Controller
{
    public function create(Customer $customer): View
    {
        return view('customers.contacts.create', compact('customer'));
    }

    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $this->validated($request);

        DB::transaction(function () use ($customer, $validated, $request): void {
            $makePrimary = $request->boolean('is_primary');

            if ($makePrimary) {
                $customer->contacts()->update(['is_primary' => false]);
            }

            $customer->contacts()->create([
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'label' => $validated['label'] ?? null,
                'is_primary' => $makePrimary,
            ]);
        });

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Contact added successfully.');
    }

    public function edit(Customer $customer, CustomerContact $contact): View
    {
        $this->ensureBelongsToCustomer($customer, $contact);

        return view('customers.contacts.edit', compact('customer', 'contact'));
    }

    public function update(Request $request, Customer $customer, CustomerContact $contact): RedirectResponse
    {
        $this->ensureBelongsToCustomer($customer, $contact);
        $validated = $this->validated($request);

        DB::transaction(function () use ($customer, $contact, $validated, $request): void {
            $makePrimary = $request->boolean('is_primary');

            if ($makePrimary) {
                $customer->contacts()
                    ->where('id', '!=', $contact->id)
                    ->update(['is_primary' => false]);
            }

            $contact->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'label' => $validated['label'] ?? null,
                'is_primary' => $makePrimary || $contact->is_primary,
            ]);
        });

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Contact updated successfully.');
    }

    public function destroy(Customer $customer, CustomerContact $contact): RedirectResponse
    {
        $this->ensureBelongsToCustomer($customer, $contact);

        if ($contact->is_primary) {
            return redirect()
                ->route('customers.show', $customer)
                ->with('error', 'The primary contact cannot be removed. Make another contact primary first.');
        }

        $contact->delete();

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Contact removed from the active customer record.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'label' => ['nullable', 'string', 'max:50'],
            'is_primary' => ['nullable', 'boolean'],
        ]);
    }

    private function ensureBelongsToCustomer(Customer $customer, CustomerContact $contact): void
    {
        abort_unless((int) $contact->customer_id === (int) $customer->id, 404);
    }
}
