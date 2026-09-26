<?php

namespace App\Http\Controllers;

use App\Models\BusinessCapability;
use App\Models\Event;
use App\Models\EventRequirement;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RequirementController extends Controller
{
    public function index(Request $request, Event $event): View
    {
        $this->ensureBusiness($event, $request);

        $event->load('customer');
        $requirements = $event->requirements()
            ->with('capability')
            ->orderBy('status')
            ->orderBy('created_at')
            ->get();

        return view('requirements.index', compact('event', 'requirements'));
    }

    public function create(Request $request, Event $event): View
    {
        $this->ensureBusiness($event, $request);
        $event->load('customer');

        $capabilities = BusinessCapability::query()
            ->where('business_id', $event->business_id)
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('requirements.create', [
            'event' => $event,
            'capabilities' => $capabilities,
            'serviceCategories' => config('zazu.service_categories'),
        ]);
    }

    public function store(Request $request, Event $event): RedirectResponse
    {
        $this->ensureBusiness($event, $request);

        $validated = $request->validate([
            'capability_id' => [
                'nullable',
                Rule::exists('business_capabilities', 'id')
                    ->where(fn ($query) => $query
                        ->where('business_id', $event->business_id)
                        ->where('is_active', true)),
            ],
            'description' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100', Rule::in(array_keys(config('zazu.service_categories')))],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $event->requirements()->create($validated + ['status' => 'open']);

        return redirect()
            ->route('work.show', $event)
            ->with('success', 'Requirement added to the workspace.');
    }

    private function ensureBusiness(Event $event, Request $request): void
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        abort_unless((int) $event->business_id === $businessId, 404);
    }
}
