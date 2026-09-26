<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventCost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EventCostController extends Controller
{
    public function index(Event $event): View
    {
        $costs = $event->costs()->latest()->get();

        $totalsByCurrency = $costs->groupBy('currency')->map(function ($currencyCosts) {
            return [
                'projected' => (float) $currencyCosts->sum(fn (EventCost $cost) => (float) $cost->projected_amount),
                'actual' => (float) $currencyCosts->sum(fn (EventCost $cost) => (float) ($cost->actual_amount ?? 0)),
            ];
        });

        return view('costs.index', compact('event', 'costs', 'totalsByCurrency'));
    }

    public function create(Event $event): View
    {
        return view('costs.create', compact('event'));
    }

    public function store(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
            'projected_amount' => ['required', 'numeric', 'min:0'],
            'actual_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:planned,incurred,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $event): void {
            EventCost::query()->create([
                ...$validated,
                'business_id' => $event->business_id,
                'event_id' => $event->id,
                'currency' => strtoupper($validated['currency']),
            ]);
        });

        return redirect()
            ->route('work.costs.index', $event)
            ->with('success', 'Cost record saved.');
    }
}
