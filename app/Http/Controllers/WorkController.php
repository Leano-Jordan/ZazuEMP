<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Event;
use App\Models\EventPreparationItem;
use App\Models\EventRequirement;
use App\Support\Audit;
use App\Services\EventLifecycleService;
use App\Services\EventScheduleValidator;
use App\Services\EventScheduleConflictDetector;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WorkController extends Controller
{
    public function index(Request $request): View
    {
        $business = $this->business($request);
        $today = now()->startOfDay();

        $baseQuery = Event::query()
            ->where('business_id', $business->id);

        $activeQuery = (clone $baseQuery)
            ->whereNotIn('status', Event::TERMINAL_STATUSES);

        $workload = [
            'today' => (clone $activeQuery)->whereDate('event_date', $today)->count(),
            'next_7_days' => (clone $activeQuery)->whereBetween('event_date', [$today, $today->copy()->addDays(6)])->count(),
            'in_progress' => (clone $activeQuery)->where('status', 'in_progress')->count(),
            'draft' => (clone $activeQuery)->where('status', 'draft')->count(),
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
            ->with([
                'customer',
                'eventDayContact',
                'eventNightContact',
                'requirements.capability',
                'quotes.latestVersion',
                'preparationItems',
                'purchaseOrders',
                'invoices.payments',
                'assetAllocations.asset',
            ])
            ->when($filter === 'today', fn ($query) => $query->whereDate('event_date', $today))
            ->when($filter === 'next_7_days', fn ($query) => $query->whereBetween('event_date', [$today, $today->copy()->addDays(6)]))
            ->when($filter === 'in_progress', fn ($query) => $query->where('status', 'in_progress'))
            ->when($filter === 'draft', fn ($query) => $query->where('status', 'draft'))
            ->when($filter === 'overdue', fn ($query) => $query
                ->whereNotIn('status', Event::TERMINAL_STATUSES)
                ->whereHas('preparationItems', fn ($prep) => $prep
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
            'serviceCategories' => BusinessCapability::query()
                ->where('business_id', $business->id)
                ->where('is_active', true)
                ->orderBy('category')
                ->orderBy('name')
                ->get()
                ->groupBy(fn (BusinessCapability $capability) => $capability->category ?: 'Other'),
        ]);
    }

    public function store(Request $request, EventScheduleConflictDetector $conflictDetector, EventScheduleValidator $scheduleValidator): RedirectResponse
    {
        $business = $this->business($request);
        $validated = $request->validate($this->rules(), $this->validationMessages());
        $scheduleValidator->validate($validated);

        $catalogueServices = array_values(array_unique($validated['services'] ?? []));
        $services = $catalogueServices;
        if (!empty($validated['other_service'])) {
            $customService = Str::limit(trim($validated['other_service']), 100, '');
            if (! in_array($customService, $services, true)) {
                $services[] = $customService;
            }
        }

        $event = DB::transaction(function () use ($validated, $services, $catalogueServices, $business) {
            // Serialize customer/contact selection against concurrent customer
            // edits before taking the snapshot copied into the new Work record.
            DB::table('businesses')->where('id', $business->id)->lockForUpdate()->first();

            $capabilities = BusinessCapability::query()
                ->where('business_id', $business->id)
                ->where('is_active', true)
                ->whereIn('name', $catalogueServices)
                ->lockForUpdate()
                ->get()
                ->groupBy('name');

            foreach ($catalogueServices as $service) {
                if ($capabilities->get($service)?->count() !== 1) {
                    throw ValidationException::withMessages([
                        'services' => 'Choose services from your active business catalogue. Refresh the form if your catalogue has changed.',
                    ]);
                }
            }

            $customer = Customer::query()
                ->where('business_id', $business->id)
                ->whereKey($validated['customer_id'])
                ->with('primaryContact')
                ->lockForUpdate()
                ->firstOrFail();

            $this->validateContactBelongsToCustomer($validated['event_day_contact_id'] ?? null, $customer->id);
            $this->validateContactBelongsToCustomer($validated['event_night_contact_id'] ?? null, $customer->id);

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
                'event_end_date' => $validated['event_end_date'] ?? null,
                'event_start_time' => $validated['event_start_time'] ?? null,
                'event_end_time' => $validated['event_end_time'] ?? null,
                'setup_date' => $validated['setup_date'] ?? null,
                'collection_date' => $validated['collection_date'] ?? null,
                'event_address' => $validated['event_address'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
            ]);

            foreach ($services as $service) {
                $capability = $capabilities->get($service)?->first();
                EventRequirement::create([
                    'event_id' => $event->id,
                    'capability_id' => $capability?->id,
                    'description' => $capability?->name ?? $service,
                    'category' => $capability?->category ?: 'Other',
                    'quantity' => 1,
                    'unit' => $capability?->default_unit ?: 'service',
                    'status' => 'open',
                ]);
            }

            return $event;
        });

        $scheduleConflicts = $conflictDetector->conflicts($business->id, $validated, $event->id);
        $scheduleWarning = $scheduleConflicts->isNotEmpty()
            ? 'This job clashes with other scheduled work: '. $scheduleConflicts->pluck('name')->implode(', ').'. Check the calendar before you confirm it.'
            : null;

        if ($request->string('start_intent')->toString() === 'quote') {
            return redirect()
                ->route('work.quotes.create', $event)
                ->with('success', 'Job created. Zazu opened the quote workflow from your starting point.')
                ->with('schedule_warning', $scheduleWarning);
        }

        return redirect()
            ->route('work.show', $event)
            ->with('success', 'Job created. Start by checking the services below.')
            ->with('schedule_warning', $scheduleWarning);
    }

    public function edit(Request $request, Event $event): View
    {
        $business = $this->business($request);
        $this->ensureBusiness($event, $business);
        abort_if($event->isClosed(), 422, 'Closed work cannot be edited.');

        $event->load(['customer', 'eventDayContact', 'eventNightContact']);
        $customers = Customer::query()
            ->where('business_id', $business->id)
            ->with('contacts')
            ->orderBy('name')
            ->get();

        $customerOptions = $customers->map(fn ($customer) => [
            'id' => $customer->id,
            'contacts' => $customer->contacts->map(fn ($contact) => [
                'id' => $contact->id,
                'name' => $contact->name,
                'phone' => $contact->phone,
                'label' => $contact->label,
            ])->values()->all(),
        ])->values()->all();

        $hasQuotes = $event->quotes()->exists();

        return view('work.edit', compact('event', 'customers', 'customerOptions', 'hasQuotes'));
    }

    public function update(Request $request, Event $event, EventLifecycleService $lifecycle, EventScheduleConflictDetector $conflictDetector, EventScheduleValidator $scheduleValidator): RedirectResponse
    {
        $business = $this->business($request);
        $validated = $request->validate($this->rules() + [
            'status' => ['required', 'in:draft,confirmed,in_progress,completed,cancelled'],
        ], $this->validationMessages());
        $scheduleValidator->validate($validated);

        try {
            DB::transaction(function () use ($event, $validated, $business, $lifecycle): void {
                $lockedEvent = $lifecycle->lock($business->id, $event->id);

                // The business lock is already held by lifecycle->lock(). Reload
                // the selected customer and contacts inside that same boundary;
                // pre-transaction form validation is not a concurrency guarantee.
                $customer = Customer::query()
                    ->where('business_id', $business->id)
                    ->whereKey($validated['customer_id'])
                    ->with('primaryContact')
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->validateContactBelongsToCustomer($validated['event_day_contact_id'] ?? null, $customer->id);
                $this->validateContactBelongsToCustomer($validated['event_night_contact_id'] ?? null, $customer->id);

                abort_if($lockedEvent->isClosed(), 422, 'Closed work cannot be edited.');

                if (
                    (int) $lockedEvent->customer_id !== (int) $customer->id
                    && $lockedEvent->quotes()->exists()
                ) {
                    throw ValidationException::withMessages([
                        'customer_id' => 'The customer cannot be changed after a quote exists for this job. Create a new job for a different customer so the quote history stays correct.',
                    ]);
                }

                $lifecycle->assertTransitionAllowed($lockedEvent, $validated['status']);

                $previousStatus = $lockedEvent->status;

                $lockedEvent->update([
                    'customer_id' => $customer->id,
                    'event_day_contact_id' => $validated['event_day_contact_id'] ?? null,
                    'event_night_contact_id' => $validated['event_night_contact_id'] ?? null,
                    'name' => trim($validated['name']),
                    'event_type' => $validated['event_type'],
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->primaryContact?->phone,
                    'customer_email' => $customer->primaryContact?->email,
                    'event_date' => $validated['event_date'],
                    'event_end_date' => $validated['event_end_date'] ?? null,
                    'event_start_time' => $validated['event_start_time'] ?? null,
                    'event_end_time' => $validated['event_end_time'] ?? null,
                    'setup_date' => $validated['setup_date'] ?? null,
                    'collection_date' => $validated['collection_date'] ?? null,
                    'event_address' => $validated['event_address'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'status' => $validated['status'],
                ]);

                if ($previousStatus !== $lockedEvent->status) {
                    Audit::record('work.status_changed', $lockedEvent, [
                        'from' => $previousStatus,
                        'to' => $lockedEvent->status,
                    ], $business->id);
                }
            });
        } catch (ValidationException $exception) {
            return redirect()
                ->route('work.edit', $event)
                ->withInput()
                ->withErrors($exception->errors());
        }

        $scheduleConflicts = $conflictDetector->conflicts($business->id, $validated, $event->id);
        $scheduleWarning = $scheduleConflicts->isNotEmpty()
            ? 'This job clashes with other scheduled work: '. $scheduleConflicts->pluck('name')->implode(', ').'. Check the calendar before you confirm it.'
            : null;

        return redirect()
            ->route('work.show', $event)
            ->with('success', 'Job details updated.')
            ->with('schedule_warning', $scheduleWarning);
    }

    public function destroy(Request $request, Event $event, EventLifecycleService $lifecycle): RedirectResponse
    {
        $business = $this->business($request);

        DB::transaction(function () use ($event, $business, $lifecycle): void {
            $lockedEvent = $lifecycle->lock($business->id, $event->id);

            abort_if($lockedEvent->isClosed(), 422, 'Closed work cannot be deleted.');

            abort_if(
                $lockedEvent->hasHistoricalRecords(),
                422,
                'This job cannot be deleted because it already has historical records. Keep it for audit and operational history.'
            );

            $lockedEvent->delete();
        });

        return redirect()
            ->route('work.index')
            ->with('success', 'Job removed from active work. Historical records remain retained.');
    }

    public function show(Request $request, Event $event): View
    {
        $business = $this->business($request);
        $this->ensureBusiness($event, $business);

        $event->load([
            'customer.contacts',
            'eventDayContact',
            'eventNightContact',
            'requirements.capability',
            'quotes.latestVersion.items',
            'attachments',
            'preparationItems',
            'purchaseOrders',
            'costs',
            'invoices.payments',
        ]);

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

    private function validationMessages(): array
    {
        return [
            'event_date.required' => 'Enter the first day of the job.',
            'event_date.date' => 'Enter a valid job start date.',
            'event_end_date.date' => 'Enter a valid job end date.',
            'event_start_time.date_format' => 'Enter a valid event start time.',
            'event_end_time.date_format' => 'Enter a valid event end time.',
            'setup_date.date' => 'Enter a valid setup date.',
            'collection_date.date' => 'Enter a valid equipment collection date.',
        ];
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
            'event_end_date' => ['nullable', 'date'],
            'event_start_time' => ['nullable', 'date_format:H:i'],
            'event_end_time' => ['nullable', 'date_format:H:i'],
            'setup_date' => ['nullable', 'date'],
            'collection_date' => ['nullable', 'date'],
            'event_address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'services' => ['nullable', 'array'],
            'services.*' => ['string', 'max:255'],
            'other_service' => ['nullable', 'string', 'max:100'],
        ];
    }
}
