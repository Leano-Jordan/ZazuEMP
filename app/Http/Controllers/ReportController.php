<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Event;
use App\Models\EventCost;
use App\Models\EventPreparationItem;
use App\Models\Quote;
use App\Support\CurrentBusiness;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __invoke(Request $request): View
    {
        $business = app(CurrentBusiness::class)->model($request->user());
        $businessId = $business->id;
        $today = now()->startOfDay();

        $events = Event::query()->where('business_id', $businessId);

        $metrics = [
            'active_jobs' => (clone $events)->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'upcoming_jobs' => (clone $events)
                ->whereBetween('event_date', [$today, $today->copy()->addDays(14)])
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
            'customers' => $business->customers()->count(),
            'outstanding_preparation' => EventPreparationItem::query()
                ->where('business_id', $businessId)
                ->whereIn('status', ['open', 'blocked'])
                ->count(),
        ];

        $jobsByStatus = (clone $events)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->pluck('total', 'status');

        $latestQuotes = Quote::query()
            ->whereHas('event', fn (Builder $query) => $query->where('business_id', $businessId))
            ->with('latestVersion')
            ->get();

        $quoteTotalsByCurrency = $latestQuotes
            ->filter(fn (Quote $quote) => $quote->latestVersion)
            ->groupBy('currency')
            ->map(fn ($quotes) => [
                'count' => $quotes->count(),
                'total' => (float) $quotes->sum(fn (Quote $quote) => (float) $quote->latestVersion->total),
            ]);

        $costTotalsByCurrency = EventCost::query()
            ->where('business_id', $businessId)
            ->whereNotIn('status', ['cancelled'])
            ->get()
            ->groupBy('currency')
            ->map(fn ($costs) => [
                'projected' => (float) $costs->sum(fn (EventCost $cost) => (float) $cost->projected_amount),
                'actual' => (float) $costs->sum(fn (EventCost $cost) => (float) ($cost->actual_amount ?? 0)),
            ]);

        return view('reports.index', compact(
            'metrics',
            'jobsByStatus',
            'quoteTotalsByCurrency',
            'costTotalsByCurrency'
        ));
    }
}
