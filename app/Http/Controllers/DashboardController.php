<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Event;
use App\Models\Quote;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now()->startOfDay();
        $business = request()->user()?->businesses()->first();

        $metrics = [
            'active_work' => Event::query()->count(),
            'upcoming_work' => Event::query()
                ->whereBetween('event_date', [$today, $today->copy()->addDays(14)])
                ->count(),
            'customers' => Customer::query()->count(),
            'draft_quotes' => Quote::query()->where('status', 'draft')->count(),
        ];

        $upcoming = Event::query()
            ->with('customer')
            ->where('event_date', '>=', $today)
            ->orderBy('event_date')
            ->orderBy('name')
            ->limit(8)
            ->get();

        return view('dashboard', compact('metrics', 'upcoming', 'business'));
    }
}
