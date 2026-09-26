<?php

namespace AppHttpControllers;

use AppModelsBusiness;
use AppModelsCustomer;
use AppModelsEvent;
use AppModelsQuote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now()->startOfDay();
        $business = request()->user()?->businesses()->first() ?? Business::first();

        $eventQuery = Event::query();
        $customerQuery = Customer::query();
        $quoteQuery = Quote::query();

        // When a business context exists, every dashboard signal must come from it.
        // The fallback keeps the foundation usable before authentication/business context
        // is fully enforced across the application.
        if ($business) {
            $eventQuery->where('business_id', $business->id);
            $customerQuery->where('business_id', $business->id);
            $quoteQuery->whereHas('event', fn (Builder $query) => $query->where('business_id', $business->id));
        }

        $metrics = [
            'active_work' => (clone $eventQuery)->count(),
            'upcoming_work' => (clone $eventQuery)
                ->whereBetween('event_date', [$today, $today->copy()->addDays(14)])
                ->count(),
            'customers' => (clone $customerQuery)->count(),
            'draft_quotes' => (clone $quoteQuery)->where('status', 'draft')->count(),
        ];

        $upcoming = (clone $eventQuery)
            ->with('customer')
            ->where('event_date', '>=', $today)
            ->orderBy('event_date')
            ->orderBy('name')
            ->limit(8)
            ->get();

        return view('dashboard', compact('metrics', 'upcoming', 'business'));
    }
}
