<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Event;
use App\Models\EventPreparationItem;
use App\Models\EventRequirement;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WorkController extends Controller
{
    public function index(Request $request): View
    {
        $business = $this->business($request);
        $today = now()->startOfDay();

        $baseQuery = Event::query()
            ->where('business_id', $business->id);

        $workload = [
            'today' => (clone $baseQuery)->whereDate('event_date', $today)->count(),
            'next_7_days' => (clone $baseQuery)->whereBetween('event_date', [$today, $today->copy()->addDays(6)])->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'draft' => (clone $baseQuery)->where('status', 'draft')->count(),
            'overdue' => EventPreparationItem::query()
                ->where('business_id', $business->id)
                ->whereIn('status', ['open', 'blocked'])
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', $today)
                ->whereHas('event', fn ($query) => $query->where('business_id', $business->id)->whereNotIn('status', Event::TERMINAL_STATUSES))
                ->count(),
        ];

        $filter = $request->string('filter')->toString();
        $allowedFilters = ['', 'today', 'next_7_days', 'in_progress', 'draft', 'overdue'];

        if (!in_array($filter, $allowedFilters, true)) {
            $filter = '';
        }

        $events = (clone $baseQuery)
            ->with(['customer', 'eventDayContact', 'eventNightContact'])
            ->when($filter === 'today', fn ($query) => $query->whereDate('event_date', $today))
            ->when($filter === 'next_7_days', fn ($query) => $query->whereBetween('event_date', [$today, $today->copy()->addDays(6)]))
            ->when($filter === 'in_progress', fn ($query) => $query->where('status', 'in_progress'))
            ->when($filter === 'draft', fn ($query) => $query->where('status', 'draft'))
            ->when($filter === 'overdue', fn ($query) => $query->whereHas('preparationItems', fn ($prep) => $prep
                ->whereIn('status', ['open', 'blocked'])
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', $today)
            ))
            ->latest('event_date')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('work.index', compact('events', 'workload', 'filter'));
    }

    public function create(Request $request): View
    {
        $business = $this->business($request);

        $customers = Customer::query()
            ->where('business_id', $business->id)
            ->with('contacts')
            ->orderBy('name')
            ->get();

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
        $business = $this->business($request);
        $validated = $request->validate($this->rules());

        $customer = Customer::query()
            ->where('business_id', $business->id)
            ->with('primaryContact')
            ->findOrFail($validated['customer_id']);

        $this->validateContactBelongsToCustomer($validated['event_day_contact_id'] ?? null, $customer->id);
        $this->validateContactBelongsToCustomer($validated['event_night_contact_id'] ?? null, $customer->id);

        $services = array_values(array_unique($validated['services'] ?? []));
        if (!empty($validated['other_service'])) {
            $services[] = Str::limit(trim($validated['other_service']), 100, '');
        }

        $categoryByService = [];
        foreach (config('zazu.service_categories') as $category => $availableServices) {
            foreach ($availableServices as $service) {
                $categoryByService[$service] = $category;
            }
        }

        $event = DB::transaction(function () use ($validated, $customer, $services, $business, $categoryByService) {
            $event = Event::create([
                'business_id' => $business->id,
                'customer_id' => $customer->id,
                'event_day_contact_id' => $validated['event_day_contact_id'] ?? null,
                'event_night_contact_id' => $validated['event_night_contact_id'] ?? null,
                'reference' => 'ZAZU-' . Str::upper(Str::random(8)),
                'name' => trim($validated['name']),
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
                    'category' => $categoryByService[$service] ?? 'Other',
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

    public function edit(Request $request, Event $event): View
    {
        $business = $this->business($request);
        $this->ensureBusiness($event, $business);

        $event->load(['customer', 'eventDayContact', 'eventNightContact']);
        $customers = Customer::query()
            ->where('business_id', $business->id)
            ->with('contacts')
            ->orderBy('name')
            ->get();
        $hasQuotes = $event->quotes()->exists();

        return view('work.edit', compact('event', 'customers', 'hasQuotes'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $business = $this->business($request);
        $this->ensureBusiness($event, $business);

        $validated = $request->validate($this->rules() + [
            'status' => ['required', 'in:draft,confirmed,in_progress,completed,cancelled'],
        ]);

        $customer = Customer::query()
            ->where('business_id', $business->id)
            ->with('primaryContact')
            ->findOrFail($validated['customer_id']);

        if ((int) $event->customer_id !== (int) $customer->id && $event->quotes()->exists()) {
            return redirect()
                ->route('work.edit', $event)
                ->with('error', 'The customer cannot be changed after a quote exists for this job. Create a new job for a different customer so the quote history stays correct.');
        }

        $this->validateStatusTransition($event->status, $validated['status']);
        $this->validateContactBelongsToCustomer($validated['event_day_contact_id'] ?? null, $customer->id);
        $this->validateContactBelongsToCustomer($validated['event_night_contact_id'] ?? null, $customer->id);

        DB::transaction(function () use ($event, $validated, $customer): void {
            $event->update([
                'customer_id' => $customer->id,
                'event_day_contact_id' => $validated['event_day_contact_id'] ?? null,
                'event_night_contact_id' => $validated['event_night_contact_id'] ?? null,
                'name' => trim($validated['name']),
                'event_type' => $validated['event_type'],
                'customer_name' => $customer->name,
                'customer_phone' => $customer->primaryContact?->phone,
                'customer_email' => $customer->primaryContact?->email,
                'event_date' => $validated['event_date'],
                'event_address' => $validated['event_address'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        return redirect()
            ->route('work.show', $event)
            ->with('success', 'Job details updated.');
    }

    public function destroy(Request $request, Event $event): RedirectResponse
    {
        $business = $this->business($request);
        $this->ensureBusiness($event, $business);

        $event->delete();

        return redirect()
            ->route('work.index')
            ->with('success', 'Job removed from active work. Historical records remain retained.');
    }

    public function show(Request $request, Event $event): View
    {
        $business = $this->business($request);
        $this->ensureBusiness($event, $business);

        $event->load(['customer.contacts', 'eventDayContact', 'eventNightContact', 'requirements.capability', 'quotes.latestVersion']);

        return view('work.show', compact('event'));
    }

    private function business(Request $request): Business
    {
        return app(CurrentBusiness::class)->model($request->user());
    }

    private function ensureBusiness(Event $event, Business $business): void
    {
        abort_unless((int) $event->business_id === (int) $business->id, 404);
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

    private function validateStatusTransition(string $current, string $next): void
    {
        $event = new Event(['status' => $current]);

        abort_unless(
            $event->canTransitionTo($next),
            422,
            'That status change is not allowed for this work record.'
        );
    }

    private function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'event_day_contact_id' => ['nullable', 'exists:customer_contacts,id'],
            'event_night_contact_id' => ['nullable', 'exists:customer_contacts,id'],
            'name' => ['required', 'string', 'max:255'],
            'event_type' => ['required', 'string', 'in:' . implode(',', config('zazu.job_types'))],
            'event_date' => ['required', 'date'],
            'event_address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'services' => ['nullable', 'array'],
            'services.*' => ['string', 'max:100'],
            'other_service' => ['nullable', 'string', 'max:100'],
        ];
    }
}
