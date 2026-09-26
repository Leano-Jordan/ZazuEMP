<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Event;
use App\Models\Quote;
use App\Support\CurrentBusiness;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        $today = now()->startOfDay();

        $eventQuery = Event::query()->where('business_id', $businessId);
        $customerQuery = Customer::query()->where('business_id', $businessId);
        $quoteQuery = Quote::query()
            ->whereHas('event', fn (Builder $query) => $query->where('business_id', $businessId));

        $metrics = [
            'active_work' => (clone $eventQuery)->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'upcoming_work' => (clone $eventQuery)
                ->whereBetween('event_date', [$today, $today->copy()->addDays(14)])
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
            'customers' => (clone $customerQuery)->count(),
            'draft_quotes' => (clone $quoteQuery)->where('status', 'draft')->count(),
        ];

        $upcoming = (clone $eventQuery)
            ->with('customer')
            ->where('event_date', '>=', $today)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('event_date')
            ->orderBy('name')
            ->limit(8)
            ->get();

        return view('dashboard', compact('metrics', 'upcoming'));
    }
}
