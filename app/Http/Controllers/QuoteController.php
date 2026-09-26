<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Quote;
use App\Models\QuoteVersion;
use App\Services\QuoteService;
use App\Support\CurrentBusiness;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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

        return view('quotes.create', [
            'event' => $event,
            'currencies' => config('zazu.currencies'),
            'defaultCurrency' => app(CurrentBusiness::class)->model($request->user())->currency ?? 'ZAR',
        ]);
    }

    public function store(Request $request, Event $event, QuoteService $quoteService): RedirectResponse
    {
        $this->ensureBusiness($event, $request);
        abort_if($event->isClosed(), 422, 'Closed work cannot receive new quotes.');
        $event->load(['customer', 'requirements.capability']);

        $request->merge(['currency' => strtoupper((string) $request->input('currency'))]);

        $validated = $request->validate([
            'currency' => ['required', Rule::in(array_keys(config('zazu.currencies')))],
            'notes' => ['nullable', 'string'],
            'unit_price' => ['required', 'array', 'min:1'],
            'unit_price.*' => ['required', 'numeric', 'decimal:0,2', 'min:0'],
        ]);

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
            $validated['notes'] ?? null
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
        ]);
        abort_unless($quote->event && (int) $quote->event->business_id === $businessId, 404);

        $version = $quote->versions->sortByDesc('version')->first();
        $quoteNeedsRevision = $version
            ? !$version->matchesRequirements($quote->event->requirements)
            : true;

        return view('quotes.show', compact('quote', 'version', 'quoteNeedsRevision'));
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

        return view('quotes.edit', compact('quote', 'version'));
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
            'notes' => ['nullable', 'string'],
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

        $quoteService->updateDraft(
            $quote,
            $version,
            $quote->event->requirements,
            $validated['unit_price'],
            $validated['notes'] ?? null
        );

        return redirect()
            ->route('quotes.show', $quote)
            ->with('success', 'Quote v' . $version->version . ' saved.');
    }

    private function ensureBusiness(Event $event, Request $request): void
    {
        abort_unless((int) $event->business_id === app(CurrentBusiness::class)->id($request->user()), 404);
    }
}
