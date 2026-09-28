<?php

namespace App\Http\Controllers;

use App\Services\WorkspaceSearchService;
use App\Support\CurrentBusiness;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request, WorkspaceSearchService $search): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'in:customer,event,service,supplier,purchase_order,quote,invoice'],
            'status' => ['nullable', 'string', 'max:50'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $business = app(CurrentBusiness::class)->model($request->user());
        $filters = [
            'q' => $validated['q'] ?? '',
            'type' => $validated['type'] ?? '',
            'status' => $validated['status'] ?? '',
            'from' => $validated['from'] ?? null,
            'to' => $validated['to'] ?? null,
        ];

        $results = $search->search($business, $request->user(), $filters);

        return view('search.index', [
            'filters' => $filters,
            'results' => $results,
            'types' => WorkspaceSearchService::TYPES,
            'statuses' => [
                'draft', 'confirmed', 'in_progress', 'completed', 'cancelled',
                'sent', 'accepted', 'declined', 'expired', 'ordered', 'received',
            ],
        ]);
    }
}
