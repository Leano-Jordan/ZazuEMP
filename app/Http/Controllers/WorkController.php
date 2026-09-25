<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WorkController extends Controller
{
    public function index(): View
    {
        $events = Event::with(['customer', 'eventDayContact'])
            ->latest('event_date')
            ->latest()
            ->paginate(15);

        return view('work.index', compact('events'));
    }

    public function create(Request $request): View
    {
        $customers = Customer::with('contacts')->orderBy('name')->get();
        $selectedCustomerId = $request->integer('customer_id');

        return view('work.create', compact('customers', 'selectedCustomerId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'event_day_contact_id' => ['nullable', 'exists:customer_contacts,id'],
            'name' => ['required', 'string', 'max:255'],
            'event_type' => ['nullable', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'event_address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $customer = Customer::with('primaryContact')->findOrFail($validated['customer_id']);

        $this->validateContactBelongsToCustomer($validated['event_day_contact_id'] ?? null, $customer->id);

        $event = DB::transaction(function () use ($validated, $customer) {
            return Event::create([
                'customer_id' => $customer->id,
                'event_day_contact_id' => $validated['event_day_contact_id'] ?? null,
                'reference' => 'ZAZU-' . Str::upper(Str::random(8)),
                'name' => $validated['name'],
                'event_type' => $validated['event_type'] ?? null,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->primaryContact?->phone,
                'customer_email' => $customer->primaryContact?->email,
                'event_date' => $validated['event_date'] ?? null,
                'event_address' => $validated['event_address'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
            ]);
        });

        return redirect()->route('work.show', $event)->with('success', 'Work created successfully.');
    }

    public function edit(Event $event): View
    {
        $event->load(['customer', 'eventDayContact']);
        $customers = Customer::with('contacts')->orderBy('name')->get();

        return view('work.edit', compact('event', 'customers'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'event_day_contact_id' => ['nullable', 'exists:customer_contacts,id'],
            'name' => ['required', 'string', 'max:255'],
            'event_type' => ['nullable', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'event_address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,confirmed,in_progress,completed,cancelled'],
        ]);

        $customer = Customer::with('primaryContact')->findOrFail($validated['customer_id']);

        $this->validateContactBelongsToCustomer($validated['event_day_contact_id'] ?? null, $customer->id);

        $event->update([
            'customer_id' => $customer->id,
            'event_day_contact_id' => $validated['event_day_contact_id'] ?? null,
            'name' => $validated['name'],
            'event_type' => $validated['event_type'] ?? null,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->primaryContact?->phone,
            'customer_email' => $customer->primaryContact?->email,
            'event_date' => $validated['event_date'] ?? null,
            'event_address' => $validated['event_address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('work.show', $event)
            ->with('success', 'Work updated successfully.');
    }

    public function show(Event $event): View
    {
        $event->load(['customer.contacts', 'eventDayContact', 'requirements']);

        return view('work.show', compact('event'));
    }

    private function validateContactBelongsToCustomer(?int $contactId, int $customerId): void
    {
        if ($contactId === null) {
            return;
        }

        $valid = $customerId === (int) \App\Models\CustomerContact::query()
            ->whereKey($contactId)
            ->value('customer_id');

        abort_unless($valid, 422, 'The selected event-day contact does not belong to the selected customer.');
    }
}
