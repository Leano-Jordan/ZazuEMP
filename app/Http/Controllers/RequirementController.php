<?php

namespace App\Http\Controllers;

use App\Models\BusinessCapability;
use App\Models\Event;
use App\Models\EventRequirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RequirementController extends Controller
{
    public function index(Event $event): View
    {
        $event->load('customer');
        $requirements = $event->requirements()->with('capability')->orderBy('status')->orderBy('created_at')->get();

        return view('requirements.index', compact('event', 'requirements'));
    }

    public function create(Event $event): View
    {
        $event->load('customer');
        $capabilities = BusinessCapability::query()
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
        $validated = $request->validate([
            'capability_id' => [
                'nullable',
                Rule::exists('business_capabilities', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'description' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100', \Illuminate\Validation\Rule::in(array_keys(config('zazu.service_categories')))],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $event->requirements()->create($validated + ['status' => 'open']);

        return redirect()
            ->route('work.show', $event)
            ->with('success', 'Requirement added to the workspace.');
    }
}
