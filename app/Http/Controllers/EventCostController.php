<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventCost;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventCostController extends Controller
{
    public function index(Request $request, Event $event): View
    {
        $this->ensureBusiness($event, $request);
        $costs = $event->costs()->latest()->get();

        $totalsByCurrency = $costs->groupBy('currency')->map(function ($currencyCosts) {
            return [
                'projected' => (float) $currencyCosts->sum(fn (EventCost $cost) => (float) $cost->projected_amount),
                'actual' => (float) $currencyCosts->sum(fn (EventCost $cost) => (float) ($cost->actual_amount ?? 0)),
            ];
        });

        return view('costs.index', compact('event', 'costs', 'totalsByCurrency'));
    }

    public function create(Request $request, Event $event): View
    {
        $this->ensureBusiness($event, $request);

        return view('costs.create', [
            'event' => $event,
            'currencies' => config('zazu.currencies'),
            'costCategories' => config('zazu.cost_categories'),
            'defaultCurrency' => app(CurrentBusiness::class)->model($request->user())->currency ?? 'ZAR',
        ]);
    }

    public function store(Request $request, Event $event): RedirectResponse
    {
        $this->ensureBusiness($event, $request);
        $request->merge(['currency' => strtoupper((string) $request->input('currency'))]);
        abort_if($event->isClosed(), 422, 'Closed work cannot receive new cost records.');

        $validated = $request->validate([
            'category' => ['required', Rule::in(array_keys(config('zazu.cost_categories')))],
            'description' => ['required', 'string', 'max:255'],
            'currency' => ['required', Rule::in(array_keys(config('zazu.currencies')))],
            'projected_amount' => ['required', 'numeric', 'min:0'],
            'actual_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:planned,incurred,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);

        abort_if(
            $validated['status'] === 'planned' && array_key_exists('actual_amount', $validated) && $validated['actual_amount'] !== null,
            422,
            'A planned cost cannot have an actual amount yet.'
        );

        abort_if(
            $validated['status'] === 'cancelled' && array_key_exists('actual_amount', $validated) && $validated['actual_amount'] !== null,
            422,
            'A cancelled cost cannot have an actual amount.'
        );

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

    private function ensureBusiness(Event $event, Request $request): void
    {
        abort_unless((int) $event->business_id === app(CurrentBusiness::class)->id($request->user()), 404);
    }
}
