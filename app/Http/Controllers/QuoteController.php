<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Quote;
use App\Models\QuoteVersion;
use App\Models\TaxRate;
use App\Services\QuoteService;
use App\Services\EventLifecycleService;
use App\Support\CurrentBusiness;
use App\Support\Audit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** @SuppressWarnings(PHPMD.TooManyPublicMethods) */
class QuoteController extends Controller
{


    public function index(Request $request): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());

        $quotes = Quote::query()
            ->whereHas('event', fn (Builder $query) => $query->where('business_id', $businessId))
            ->with(['event.customer', 'latestVersion'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('quotes.index', compact('quotes'));
    }

    public function eventIndex(Request $request, Event $event): View
    {
        $this->ensureBusiness($event, $request);
        $event->load('customer');

        $quotes = $event->quotes()
            ->with('latestVersion')
            ->latest()
            ->get();

        return view('quotes.event-index', compact('event', 'quotes'));
    }

    public function create(Request $request, Event $event): View|RedirectResponse
    {
        $this->ensureBusiness($event, $request);
        abort_if($event->isClosed(), 422, 'Closed work cannot receive new quotes.');
        $event->load(['customer', 'requirements.capability']);

        if ($event->requirements->isEmpty()) {
            return redirect()
                ->route('work.requirements.create', $event)
                ->with('error', 'Add at least one requirement before creating a quote.');
        }

        $businessId = app(CurrentBusiness::class)->id($request->user());
        $taxRates = $this->activeTaxRates($businessId);
        $defaultTaxRate = $taxRates->firstWhere('is_default', true);

        return view('quotes.create', [
            'event' => $event,
            'currencies' => config('zazu.currencies'),
            'defaultCurrency' => app(CurrentBusiness::class)->model($request->user())->currency ?? 'ZAR',
            'taxRates' => $taxRates,
            'defaultTaxRateId' => $defaultTaxRate?->id,
        ]);
    }

    public function store(Request $request, Event $event, QuoteService $quoteService): RedirectResponse
    {
        $this->ensureBusiness($event, $request);
        abort_if($event->isClosed(), 422, 'Closed work cannot receive new quotes.');
        $event->load(['customer', 'requirements.capability']);

        $businessId = app(CurrentBusiness::class)->id($request->user());
        $request->merge(['currency' => strtoupper((string) $request->input('currency'))]);

        $validated = $request->validate([
            'currency' => ['required', Rule::in(array_keys(config('zazu.currencies')))],
            'tax_rate_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
            'deposit_percent' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:100'],
            'unit_price' => ['required', 'array', 'min:1'],
            'unit_price.*' => ['required', 'numeric', 'decimal:0,2', 'min:0'],
        ]);

        $taxRate = $this->resolveTaxRate($businessId, $validated['tax_rate_id'] ?? null);

        $requirementsById = $event->requirements->keyBy('id');
        $submittedRequirementIds = array_map('intval', array_keys($validated['unit_price']));
        $currentRequirementIds = $requirementsById->keys()->map(fn ($id) => (int) $id)->all();

        sort($submittedRequirementIds);
        sort($currentRequirementIds);

        if ($submittedRequirementIds !== $currentRequirementIds) {
            return back()
                ->withErrors(['unit_price' => 'The quote lines changed. Refresh the page and try again.'])
                ->withInput();
        }

        $quote = $quoteService->createFromRequirements(
            $event,
            $event->requirements,
            $validated['unit_price'],
            strtoupper($validated['currency']),
            $taxRate,
            $validated['notes'] ?? null,
            (string) ($validated['deposit_percent'] ?? '0.00')
        );

        return redirect()
            ->route('quotes.show', $quote)
            ->with('success', 'Draft quote created.');
    }

    public function show(Request $request, Quote $quote): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());

        $quote->load([
            'event.customer',
            'event.requirements.capability',
            'versions.items',
            'versions.taxRateRecord',
        ]);
        abort_unless($quote->event && (int) $quote->event->business_id === $businessId, 404);

        $version = $quote->versions->sortByDesc('version')->first();
        $quoteNeedsRevision = $version
            ? !$version->matchesRequirements($quote->event->requirements)
            : true;

        $customerUrl = in_array($quote->status, ['sent', 'accepted'], true)
            ? URL::temporarySignedRoute('quotes.public', now()->addDays(30), ['quote' => $quote])
            : null;

        return view('quotes.show', compact('quote', 'version', 'quoteNeedsRevision', 'customerUrl'));
    }

    public function publicShow(Request $request, Quote $quote): View
    {
        abort_unless(in_array($quote->status, ['sent', 'accepted'], true), 404);

        $quote->load([
            'event.customer',
            'versions.items',
        ]);

        $version = $quote->versions->sortByDesc('version')->first();

        abort_unless(
            $version && $version->status === $quote->status,
            404
        );

        $acceptUrl = $quote->status === 'sent'
            ? URL::temporarySignedRoute('quotes.public.accept', now()->addDays(30), ['quote' => $quote])
            : null;

        return view('quotes.public', compact('quote', 'version', 'acceptUrl'));
    }

    public function publicAccept(
        Request $request,
        Quote $quote,
        EventLifecycleService $lifecycle
    ): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'acceptance' => ['accepted'],
        ]);

        DB::transaction(function () use ($quote, $validated, $lifecycle): void {
            $eventId = Quote::query()
                ->whereKey($quote->id)
                ->value('event_id');

            abort_unless($eventId, 404);

            $businessId = (int) Event::query()
                ->whereKey($eventId)
                ->value('business_id');

            abort_unless($businessId > 0, 404);

            // Use the same business -> event -> child lock ordering as the
            // authenticated commercial mutation paths. This prevents a public
            // quote acceptance racing an event cancellation or closure.
            $lockedEvent = $lifecycle->lock($businessId, (int) $eventId);
            $lifecycle->assertOperational($lockedEvent);

            $lockedQuote = Quote::query()
                ->whereKey($quote->id)
                ->where('event_id', $lockedEvent->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($lockedQuote->status === 'sent', 422, 'This quote is no longer awaiting customer acceptance.');

            $version = $lockedQuote->versions()
                ->orderByDesc('version')
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($version->status === 'sent', 422, 'This quote version is no longer awaiting customer acceptance.');

            $version->update(['status' => 'accepted']);
            $lockedQuote->update(['status' => 'accepted']);

            Audit::record('quote.customer.accepted', $lockedQuote, [
                'customer_name' => trim($validated['customer_name']),
                'quote_version' => $version->version,
            ], $businessId);
        });

        return redirect()->to(
            URL::temporarySignedRoute('quotes.public', now()->addDays(30), ['quote' => $quote])
        );
    }

    public function updateStatus(
        Request $request,
        Quote $quote,
        EventLifecycleService $lifecycle
    ): RedirectResponse
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());

        $quote->loadMissing(['event', 'latestVersion']);
        abort_unless($quote->event && (int) $quote->event->business_id === $businessId, 404);
        abort_if($quote->event->isClosed(), 422, 'Closed work cannot change quote status.');

        $validated = $request->validate([
            'status' => ['required', Rule::in(['sent', 'accepted', 'declined', 'expired'])],
        ]);

        $newStatus = $validated['status'];
        $currentStatus = $quote->status ?: 'draft';

        abort_unless(
            in_array($newStatus, Quote::STATUS_TRANSITIONS[$currentStatus] ?? [], true),
            422,
            'That quote status change is not allowed.'
        );

        abort_unless($quote->latestVersion, 422, 'A quote must have a version before its status can change.');

        DB::transaction(function () use ($quote, $newStatus, $businessId, $lifecycle): void {
            $eventId = Quote::query()
                ->whereKey($quote->id)
                ->value('event_id');

            abort_unless($eventId, 404);

            $lockedEvent = $lifecycle->lock($businessId, (int) $eventId);
            $lifecycle->assertOperational($lockedEvent);

            $lockedQuote = Quote::query()
                ->whereKey($quote->id)
                ->where('event_id', $lockedEvent->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedCurrentStatus = $lockedQuote->status ?: 'draft';

            abort_unless(
                $lockedQuote->canTransitionTo($newStatus),
                422,
                'That quote status change is no longer allowed.'
            );

            $version = $lockedQuote->versions()
                ->orderByDesc('version')
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $version->status === $lockedCurrentStatus,
                422,
                'The quote changed while this action was being processed. Refresh and try again.'
            );

            $version->update([
                'status' => $newStatus,
            ]);

            $lockedQuote->update([
                'status' => $newStatus,
            ]);
        });

        return redirect()
            ->route('quotes.show', $quote)
            ->with('success', 'Quote marked as '.str_replace('_', ' ', $newStatus).'.');
    }

    public function createVersion(Request $request, Quote $quote, QuoteService $quoteService): RedirectResponse
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        $quote->loadMissing(['event', 'event.requirements.capability']);

        abort_unless(
            $quote->event && (int) $quote->event->business_id === $businessId,
            404
        );
        abort_if($quote->event->isClosed(), 422, 'Closed work cannot receive new quote revisions.');

        $version = $quoteService->createRevision($quote, $quote->event->requirements);

        return redirect()
            ->route('quotes.versions.edit', [$quote, $version])
            ->with('success', 'Quote revision v' . $version->version . ' is ready to review.');
    }

    public function editVersion(Request $request, Quote $quote, QuoteVersion $version): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());

        $quote->loadMissing(['event.customer', 'event.requirements.capability']);
        abort_unless(
            $quote->event && (int) $quote->event->business_id === $businessId,
            404
        );
        abort_unless((int) $version->quote_id === (int) $quote->id, 404);
        abort_if($quote->event->isClosed(), 422, 'Closed work cannot receive quote revisions.');
        abort_unless($version->status === 'draft', 422, 'Only draft quote revisions can be edited.');

        $version->load('items');

        return view('quotes.edit', [
            'quote' => $quote,
            'version' => $version,
            'taxRates' => $this->activeTaxRates($businessId),
        ]);
    }

    public function updateVersion(Request $request, Quote $quote, QuoteVersion $version, QuoteService $quoteService): RedirectResponse
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());

        $quote->loadMissing(['event', 'event.requirements.capability']);
        abort_unless(
            $quote->event && (int) $quote->event->business_id === $businessId,
            404
        );
        abort_unless((int) $version->quote_id === (int) $quote->id, 404);
        abort_if($quote->event->isClosed(), 422, 'Closed work cannot receive quote revisions.');
        abort_unless($version->status === 'draft', 422, 'Only draft quote revisions can be edited.');

        $validated = $request->validate([
            'tax_rate_id' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'deposit_percent' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:100'],
            'unit_price' => ['required', 'array', 'min:1'],
            'unit_price.*' => ['required', 'numeric', 'decimal:0,2', 'min:0'],
        ]);

        $requirementsById = $quote->event->requirements->keyBy('id');
        $submittedRequirementIds = array_map('intval', array_keys($validated['unit_price']));
        $currentRequirementIds = $requirementsById->keys()->map(fn ($id) => (int) $id)->all();

        sort($submittedRequirementIds);
        sort($currentRequirementIds);

        if ($submittedRequirementIds !== $currentRequirementIds) {
            return back()
                ->withErrors(['unit_price' => 'The quote lines changed. Refresh the page and try again.'])
                ->withInput();
        }

        $taxSelection = $validated['tax_rate_id'] ?? null;
        $replaceTax = $taxSelection !== null && $taxSelection !== '';
        $taxRate = $taxSelection === 'none'
            ? null
            : ($replaceTax ? $this->resolveTaxRate($businessId, $taxSelection) : null);

        $quoteService->updateDraft(
            $quote,
            $version,
            $quote->event->requirements,
            $validated['unit_price'],
            $taxRate,
            $replaceTax,
            $validated['notes'] ?? null,
            (string) ($validated['deposit_percent'] ?? '0.00')
        );

        return redirect()
            ->route('quotes.show', $quote)
            ->with('success', 'Quote v' . $version->version . ' saved.');
    }

    private function activeTaxRates(int $businessId)
    {
        $today = now()->toDateString();

        return TaxRate::query()
            ->where('business_id', $businessId)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $today)
            ->where(fn (Builder $query) => $query
                ->whereNull('effective_to')
                ->orWhereDate('effective_to', '>=', $today))
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    private function resolveTaxRate(int $businessId, $taxRateId): ?TaxRate
    {
        if ($taxRateId === null || $taxRateId === '') {
            return null;
        }

        $today = now()->toDateString();

        return TaxRate::query()
            ->where('business_id', $businessId)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $today)
            ->where(fn (Builder $query) => $query
                ->whereNull('effective_to')
                ->orWhereDate('effective_to', '>=', $today))
            ->whereKey($taxRateId)
            ->firstOrFail();
    }

    private function ensureBusiness(Event $event, Request $request): void
    {
        abort_unless((int) $event->business_id === app(CurrentBusiness::class)->id($request->user()), 404);
    }
}
