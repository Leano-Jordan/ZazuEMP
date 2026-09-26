<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Quote;
use App\Models\QuoteVersion;
use App\Support\CurrentBusiness;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

    public function store(Request $request, Event $event): RedirectResponse
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        $request->merge(['currency' => strtoupper((string) $request->input('currency'))]);
        $this->ensureBusiness($event, $request);
        abort_if($event->isClosed(), 422, 'Closed work cannot receive new quotes.');
        $event->load(['customer', 'requirements.capability']);

        $validated = $request->validate([
            'currency' => ['required', Rule::in(array_keys(config('zazu.currencies')))],
            'notes' => ['nullable', 'string'],
            'unit_price' => ['required', 'array', 'min:1'],
            'unit_price.*' => ['required', 'numeric', 'min:0'],
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

        $result = DB::transaction(function () use ($validated, $requirementsById, $event, $businessId): Quote {
            $quote = Quote::create([
                'event_id' => $event->id,
                'reference' => 'QUO-' . Str::upper(Str::random(8)),
                'status' => 'draft',
                'currency' => Str::upper($validated['currency']),
            ]);

            $version = $quote->versions()->create([
                'version' => 1,
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
            ]);

            $subtotalCents = 0;

            foreach ($requirementsById as $requirement) {
                $unitPriceCents = (int) round(((float) $validated['unit_price'][$requirement->id]) * 100);
                $quantityHundredths = (int) round(((float) $requirement->quantity) * 100);
                $lineTotalCents = intdiv(($quantityHundredths * $unitPriceCents) + 50, 100);
                $subtotalCents += $lineTotalCents;

                $quantity = number_format($quantityHundredths / 100, 2, '.', '');
                $unitPrice = number_format($unitPriceCents / 100, 2, '.', '');
                $lineTotal = number_format($lineTotalCents / 100, 2, '.', '');

                $version->items()->create([
                    'event_requirement_id' => $requirement->id,
                    'capability_id' => $requirement->capability_id,
                    'description' => $requirement->description,
                    'quantity' => $quantity,
                    'unit' => $requirement->unit,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'pricing_basis' => $requirement->capability?->pricing_basis,
                    'source_snapshot' => [
                        'requirement_id' => $requirement->id,
                        'description' => $requirement->description,
                        'category' => $requirement->category,
                        'quantity' => $quantity,
                        'unit' => $requirement->unit,
                        'notes' => $requirement->notes,
                        'capability_id' => $requirement->capability_id,
                        'capability_name' => $requirement->capability?->name,
                        'pricing_basis' => $requirement->capability?->pricing_basis,
                    ],
                ]);
            }

            $subtotal = number_format($subtotalCents / 100, 2, '.', '');

            $version->update([
                'subtotal' => $subtotal,
                'tax_total' => '0.00',
                'total' => $subtotal,
            ]);

            return $quote;
        });

        return redirect()
            ->route('quotes.show', $result)
            ->with('success', 'Draft quote created.');
    }

    public function show(Request $request, Quote $quote): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());

        $quote->load(['event.customer', 'versions.items']);
        abort_unless($quote->event && (int) $quote->event->business_id === $businessId, 404);

        $version = $quote->versions->sortByDesc('version')->first();

        return view('quotes.show', compact('quote', 'version'));
    }

    public function createVersion(Request $request, Quote $quote): RedirectResponse
    {
        $quote->loadMissing('event');
        abort_if($quote->event?->isClosed(), 422, 'Closed work cannot receive new quote revisions.');

        $businessId = app(CurrentBusiness::class)->id($request->user());

        $newVersion = DB::transaction(function () use ($quote, $businessId): QuoteVersion {
            $lockedQuote = Quote::query()
                ->whereKey($quote->id)
                ->whereHas('event', fn (Builder $query) => $query->where('business_id', $businessId))
                ->lockForUpdate()
                ->firstOrFail();

            $latest = $lockedQuote->versions()
                ->with('items')
                ->orderByDesc('version')
                ->first();

            abort_unless($latest, 404);

            $latest->update(['status' => 'superseded']);

            $newVersion = $lockedQuote->versions()->create([
                'version' => $latest->version + 1,
                'status' => 'draft',
                'subtotal' => $latest->subtotal,
                'tax_total' => $latest->tax_total,
                'total' => $latest->total,
                'notes' => $latest->notes,
            ]);

            foreach ($latest->items as $item) {
                $newVersion->items()->create([
                    'event_requirement_id' => $item->event_requirement_id,
                    'capability_id' => $item->capability_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->line_total,
                    'pricing_basis' => $item->pricing_basis,
                    'source_snapshot' => $item->source_snapshot,
                ]);
            }

            return $newVersion;
        });

        return redirect()
            ->route('quotes.show', $quote)
            ->with('success', 'New quote revision created: v' . $newVersion->version . '.');
    }

    private function ensureBusiness(Event $event, Request $request): void
    {
        abort_unless((int) $event->business_id === app(CurrentBusiness::class)->id($request->user()), 404);
    }
}
