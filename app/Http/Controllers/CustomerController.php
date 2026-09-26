<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

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
        $validated = $this->validated($request, null, true);
        $photoPath = $request->file('profile_photo')?->store('profile-photos/customers', 'public');

        try {
            DB::transaction(function () use ($validated, $photoPath): void {
                $customer = Customer::create([
                    'name' => $validated['name'],
                    'profile_photo_path' => $photoPath,
                    'notes' => $validated['notes'] ?? null,
                ]);

                $customer->contacts()->create([
                    'name' => $validated['primary_contact_name'],
                    'phone' => $validated['primary_contact_phone'] ?? null,
                    'email' => $validated['primary_contact_email'] ?? null,
                    'label' => 'Primary',
                    'is_primary' => true,
                ]);

                $this->createOptionalContact($customer, $validated, 'day');
                $this->createOptionalContact($customer, $validated, 'night');
            });
        } catch (Throwable $e) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $e;
        }

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer): View
    {
        $customer->load([
            'contacts',
            'events' => fn ($query) => $query->withTrashed()->latest('event_date')->latest(),
        ]);

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer): View
    {
        $customer->load('primaryContact');

        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $this->validated($request, $customer, false);
        $oldPhotoPath = $customer->profile_photo_path;
        $newPhotoPath = $request->file('profile_photo')?->store('profile-photos/customers', 'public');

        if ($newPhotoPath) {
            $validated['profile_photo_path'] = $newPhotoPath;
        } elseif ($request->boolean('remove_profile_photo')) {
            $validated['profile_photo_path'] = null;
        }

        try {
            DB::transaction(function () use ($customer, $validated): void {
                $customer->update([
                    'name' => $validated['name'],
                    'profile_photo_path' => $validated['profile_photo_path'] ?? $customer->profile_photo_path,
                    'notes' => $validated['notes'] ?? null,
                ]);

                $primary = $customer->primaryContact()->first();

                if ($primary) {
                    $primary->update([
                        'name' => $validated['primary_contact_name'],
                        'phone' => $validated['primary_contact_phone'] ?? null,
                        'email' => $validated['primary_contact_email'] ?? null,
                    ]);
                } else {
                    $customer->contacts()->create([
                        'name' => $validated['primary_contact_name'],
                        'phone' => $validated['primary_contact_phone'] ?? null,
                        'email' => $validated['primary_contact_email'] ?? null,
                        'label' => 'Primary',
                        'is_primary' => true,
                    ]);
                }
            });
        } catch (Throwable $e) {
            if ($newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            throw $e;
        }

        if ($newPhotoPath && $oldPhotoPath) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        if (!$newPhotoPath && $request->boolean('remove_profile_photo') && $oldPhotoPath) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    private function validated(Request $request, ?Customer $customer, bool $creating): array
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('customers', 'name')->ignore($customer?->id),
            ],
            'notes' => ['nullable', 'string'],
            'primary_contact_name' => ['required', 'string', 'max:255'],
            'primary_contact_phone' => ['nullable', 'string', 'max:50'],
            'primary_contact_email' => ['nullable', 'email', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        if ($creating) {
            $rules += [
                'day_contact_name' => ['nullable', 'string', 'max:255'],
                'day_contact_phone' => ['nullable', 'string', 'max:50'],
                'day_contact_email' => ['nullable', 'email', 'max:255'],
                'night_contact_name' => ['nullable', 'string', 'max:255'],
                'night_contact_phone' => ['nullable', 'string', 'max:50'],
                'night_contact_email' => ['nullable', 'email', 'max:255'],
            ];

            $rules['day_contact_name'][] = 'required_with:day_contact_phone,day_contact_email';
            $rules['night_contact_name'][] = 'required_with:night_contact_phone,night_contact_email';
            $rules['remove_profile_photo'] = ['nullable', 'boolean'];
        } else {
            $rules['remove_profile_photo'] = ['nullable', 'boolean'];
        }

        return $request->validate($rules);
    }

    private function createOptionalContact(Customer $customer, array $validated, string $period): void
    {
        $name = $validated[$period . '_contact_name'] ?? null;
        $phone = $validated[$period . '_contact_phone'] ?? null;
        $email = $validated[$period . '_contact_email'] ?? null;

        if (!$name && !$phone && !$email) {
            return;
        }

        $customer->contacts()->create([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'label' => Str::ucfirst($period),
            'is_primary' => false,
        ]);
    }
}
