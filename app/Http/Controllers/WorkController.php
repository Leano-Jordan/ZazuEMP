<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Event;
use App\Models\EventRequirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WorkController extends Controller
{
    public function index(): View
    {
        $events = Event::with(['customer', 'eventDayContact', 'eventNightContact'])
            ->latest('event_date')
            ->latest()
            ->paginate(15);

        return view('work.index', compact('events'));
    }

    public function create(Request $request): View
    {
        $customers = Customer::with('contacts')->orderBy('name')->get();
        $selectedCustomerId = $request->integer('customer_id');
        $customerOptions = $customers->map(fn ($customer) => [
            'id' => $customer->id,
            'contacts' => $customer->contacts->map(fn ($contact) => [
                'id' => $contact->id,
                'name' => $contact->name,
                'phone' => $contact->phone,
                'label' => $contact->label,
            ])->values()->all(),
        ])->values()->all();

        return view('work.create', [
            'customers' => $customers,
            'selectedCustomerId' => $selectedCustomerId,
            'customerOptions' => $customerOptions,
            'jobTypes' => config('zazu.job_types'),
            'serviceCategories' => config('zazu.service_categories'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $customer = Customer::with('primaryContact')->findOrFail($validated['customer_id']);

        $this->validateContactBelongsToCustomer($validated['event_day_contact_id'] ?? null, $customer->id);
        $this->validateContactBelongsToCustomer($validated['event_night_contact_id'] ?? null, $customer->id);

        $services = array_values(array_unique($validated['services'] ?? []));
        if (!empty($validated['other_service'])) {
            $services[] = Str::limit(trim($validated['other_service']), 100, '');
        }

        $event = DB::transaction(function () use ($validated, $customer, $services) {
            $event = Event::create([
                'customer_id' => $customer->id,
                'event_day_contact_id' => $validated['event_day_contact_id'] ?? null,
                'event_night_contact_id' => $validated['event_night_contact_id'] ?? null,
                'reference' => 'ZAZU-' . Str::upper(Str::random(8)),
                'name' => $validated['name'],
                'event_type' => $validated['event_type'],
                'customer_name' => $customer->name,
                'customer_phone' => $customer->primaryContact?->phone,
                'customer_email' => $customer->primaryContact?->email,
                'event_date' => $validated['event_date'],
                'event_address' => $validated['event_address'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
            ]);

            foreach ($services as $service) {
                EventRequirement::create([
                    'event_id' => $event->id,
                    'description' => $service,
                    'category' => $service,
                    'quantity' => 1,
                    'unit' => 'service',
                    'status' => 'open',
                ]);
            }

            return $event;
        });

        return redirect()
            ->route('work.show', $event)
            ->with('success', 'Job created. Start by checking the services below.');
    }

    public function edit(Event $event): View
    {
        $event->load(['customer', 'eventDayContact', 'eventNightContact']);
        $customers = Customer::with('contacts')->orderBy('name')->get();
        $hasQuotes = $event->quotes()->exists();

        return view('work.edit', compact('event', 'customers', 'hasQuotes'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate($this->rules() + [
            'status' => ['required', 'in:draft,confirmed,in_progress,completed,cancelled'],
        ]);

        $customer = Customer::with('primaryContact')->findOrFail($validated['customer_id']);

        if ((int) $event->customer_id !== (int) $customer->id && $event->quotes()->exists()) {
            return redirect()
                ->route('work.edit', $event)
                ->with('error', 'The customer cannot be changed after a quote exists for this job. Create a new job for a different customer so the quote history stays correct.');
        }

        $this->validateContactBelongsToCustomer($validated['event_day_contact_id'] ?? null, $customer->id);
        $this->validateContactBelongsToCustomer($validated['event_night_contact_id'] ?? null, $customer->id);

        $event->update([
            'customer_id' => $customer->id,
            'event_day_contact_id' => $validated['event_day_contact_id'] ?? null,
            'event_night_contact_id' => $validated['event_night_contact_id'] ?? null,
            'name' => $validated['name'],
            'event_type' => $validated['event_type'],
            'customer_name' => $customer->name,
            'customer_phone' => $customer->primaryContact?->phone,
            'customer_email' => $customer->primaryContact?->email,
            'event_date' => $validated['event_date'],
            'event_address' => $validated['event_address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('work.show', $event)
            ->with('success', 'Job details updated.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()
            ->route('work.index')
            ->with('success', 'Job removed from active work. Historical records remain retained.');
    }

    public function show(Event $event): View
    {
        $event->load(['customer.contacts', 'eventDayContact', 'eventNightContact', 'requirements.capability', 'quotes.latestVersion']);

        return view('work.show', compact('event'));
    }

    private function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'event_day_contact_id' => ['nullable', 'exists:customer_contacts,id'],
            'event_night_contact_id' => ['nullable', 'exists:customer_contacts,id'],
            'name' => ['required', 'string', 'max:255'],
            'event_type' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'event_address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'services' => ['nullable', 'array'],
            'services.*' => ['string', 'max:100'],
            'other_service' => ['nullable', 'string', 'max:100'],
        ];
    }

    private function validateContactBelongsToCustomer(?int $contactId, int $customerId): void
    {
        if ($contactId === null) {
            return;
        }

        $valid = $customerId === (int) CustomerContact::query()
            ->whereKey($contactId)
            ->value('customer_id');

        abort_unless($valid, 422, 'The selected contact does not belong to the selected customer.');
    }
}
