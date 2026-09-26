<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventPreparationItem;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventPreparationController extends Controller
{
    public function index(Request $request, Event $event): View
    {
        $this->ensureBusiness($event, $request);

        $items = $event->preparationItems()
            ->orderByRaw("CASE status WHEN 'open' THEN 1 WHEN 'blocked' THEN 2 WHEN 'ready' THEN 3 ELSE 4 END")
            ->orderBy('due_date')
            ->orderBy('created_at')
            ->get();

        return view('preparation.index', compact('event', 'items'));
    }

    public function create(Request $request, Event $event): View
    {
        $this->ensureBusiness($event, $request);

        return view('preparation.create', [
            'event' => $event,
            'categories' => config('zazu.readiness_categories'),
            'units' => config('zazu.units'),
        ]);
    }

    public function store(Request $request, Event $event): RedirectResponse
    {
        $this->ensureBusiness($event, $request);
        abort_if($event->isClosed(), 422, 'Closed work cannot receive new preparation records.');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', Rule::in(array_keys(config('zazu.readiness_categories')))],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:open,blocked,ready'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $event): void {
            EventPreparationItem::query()->create([
                ...$validated,
                'business_id' => $event->business_id,
                'event_id' => $event->id,
                'completed_at' => ($validated['status'] ?? 'open') === 'ready' ? now() : null,
            ]);
        });

        return redirect()
            ->route('work.preparation.index', $event)
            ->with('success', 'Preparation item added.');
    }

    public function updateStatus(Request $request, Event $event, EventPreparationItem $item): RedirectResponse
    {
        $this->ensureBusiness($event, $request);
        abort_if($event->isClosed(), 422, 'Closed work cannot have its preparation status changed.');
        abort_unless($item->event_id === $event->id && $item->business_id === $event->business_id, 404);

        $validated = $request->validate([
            'status' => ['required', 'in:open,blocked,ready'],
        ]);

        $item->update([
            'status' => $validated['status'],
            'completed_at' => $validated['status'] === 'ready' ? now() : null,
        ]);

        return redirect()
            ->route('work.preparation.index', $event)
            ->with('success', 'Preparation status updated.');
    }

    private function ensureBusiness(Event $event, Request $request): void
    {
        abort_unless((int) $event->business_id === app(CurrentBusiness::class)->id($request->user()), 404);
    }
}
