<?php

namespace App\Http\Controllers;

use App\Models\BusinessCapability;
use App\Models\Event;
use App\Models\EventRequirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return view('requirements.create', compact('event', 'capabilities'));
    }

    public function store(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'capability_id' => ['nullable', 'exists:business_capabilities,id'],
            'description' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $event->requirements()->create($validated + ['status' => 'open']);

        return redirect()
            ->route('work.requirements.index', $event)
            ->with('success', 'Requirement added to the workspace.');
    }
}
