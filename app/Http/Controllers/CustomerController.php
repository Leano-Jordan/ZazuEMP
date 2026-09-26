<?php

namespace App\Http\Controllers;

use App\Models\Business;
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
    public function index(Request $request): View
    {
        $business = $this->business($request);

        $customers = Customer::query()
            ->where('business_id', $business->id)
            ->with('primaryContact')
            ->withCount('events')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $business = $this->business($request);
        $validated = $this->validated($request, null, true, $business->id);
        $photoPath = $request->file('profile_photo')?->store('profile-photos/customers', 'public');

        try {
            DB::transaction(function () use ($validated, $photoPath, $business): void {
                $customer = Customer::create([
                    'business_id' => $business->id,
                    'name' => trim($validated['name']),
                    'profile_photo_path' => $photoPath,
                    'notes' => $validated['notes'] ?? null,
                ]);

                $customer->contacts()->create([
                    'name' => trim($validated['primary_contact_name']),
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

    public function show(Request $request, Customer $customer): View
    {
        $business = $this->business($request);
        $this->ensureBusiness($customer, $business);

        $customer->load([
            'contacts',
            'events' => fn ($query) => $query
                ->withTrashed()
                ->where('business_id', $business->id)
                ->latest('event_date')
                ->latest(),
        ]);

        return view('customers.show', compact('customer'));
    }

    public function edit(Request $request, Customer $customer): View
    {
        $business = $this->business($request);
        $this->ensureBusiness($customer, $business);

        $customer->load('primaryContact');

        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $business = $this->business($request);
        $this->ensureBusiness($customer, $business);

        $validated = $this->validated($request, $customer, false, $business->id);
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
                    'name' => trim($validated['name']),
                    'profile_photo_path' => $validated['profile_photo_path'] ?? $customer->profile_photo_path,
                    'notes' => $validated['notes'] ?? null,
                ]);

                $primary = $customer->primaryContact()->first();

                if ($primary) {
                    $primary->update([
                        'name' => trim($validated['primary_contact_name']),
                        'phone' => $validated['primary_contact_phone'] ?? null,
                        'email' => $validated['primary_contact_email'] ?? null,
                    ]);
                } else {
                    $customer->contacts()->create([
                        'name' => trim($validated['primary_contact_name']),
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

    private function business(Request $request): Business
    {
        return app(CurrentBusiness::class)->model($request->user());
    }

    private function ensureBusiness(Customer $customer, Business $business): void
    {
        abort_unless((int) $customer->business_id === (int) $business->id, 404);
    }

    private function validated(Request $request, ?Customer $customer, bool $creating, int $businessId): array
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('customers', 'name')
                    ->where(fn ($query) => $query->where('business_id', $businessId))
                    ->ignore($customer?->id),
            ],
            'notes' => ['nullable', 'string'],
            'primary_contact_name' => ['required', 'string', 'max:255'],
            'primary_contact_phone' => ['nullable', 'string', 'max:50'],
            'primary_contact_email' => ['nullable', 'email', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_profile_photo' => ['nullable', 'boolean'],
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
            'name' => trim($name),
            'phone' => $phone,
            'email' => $email,
            'label' => Str::ucfirst($period),
            'is_primary' => false,
        ]);
    }
}
