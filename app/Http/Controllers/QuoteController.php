<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Quote;
use App\Models\QuoteVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function index(): View
    {
        $quotes = Quote::query()
            ->with(['event.customer', 'latestVersion'])
            ->latest()
            ->paginate(20);

        return view('quotes.index', compact('quotes'));
    }

    public function eventIndex(Event $event): View
    {
        $event->load('customer');
        $quotes = $event->quotes()
            ->with('latestVersion')
            ->latest()
            ->get();

        return view('quotes.event-index', compact('event', 'quotes'));
    }

    public function create(Event $event): View
    {
        $event->load(['customer', 'requirements.capability']);

        if ($event->requirements->isEmpty()) {
            return redirect()
                ->route('work.requirements.create', $event)
                ->with('error', 'Add at least one requirement before creating a quote.');
        }

        return view('quotes.create', compact('event'));
    }

    public function store(Request $request, Event $event): RedirectResponse
    {
        $event->load(['customer', 'requirements.capability']);

        $validated = $request->validate([
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
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

        $result = DB::transaction(function () use ($validated, $requirementsById, $event): Quote {
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

            $subtotal = 0;

            foreach ($requirementsById as $requirement) {
                $unitPrice = (float) $validated['unit_price'][$requirement->id];
                $quantity = (float) $requirement->quantity;
                $lineTotal = round($quantity * $unitPrice, 2);
                $subtotal += $lineTotal;

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

            $version->update([
                'subtotal' => $subtotal,
                'tax_total' => 0,
                'total' => $subtotal,
            ]);

            return $quote;
        });

        return redirect()
            ->route('quotes.show', $result)
            ->with('success', 'Draft quote created.');
    }

    public function show(Quote $quote): View
    {
        $quote->load([
            'event.customer',
            'versions.items',
        ]);

        $version = $quote->versions->sortByDesc('version')->first();

        return view('quotes.show', compact('quote', 'version'));
    }

    public function createVersion(Quote $quote): RedirectResponse
    {
        $newVersion = DB::transaction(function () use ($quote): QuoteVersion {
            $lockedQuote = Quote::query()
                ->lockForUpdate()
                ->findOrFail($quote->id);

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
}
