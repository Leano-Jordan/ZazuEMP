<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Event;
use App\Models\Quote;
use App\Support\CurrentBusiness;
use App\Support\PermissionService;
use App\Support\ExperienceLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $business = app(CurrentBusiness::class)->model($request->user());
        $businessId = $business->id;
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

        $experienceLevel = app(ExperienceLevel::class)->for($request->user(), $business);

        $permissionService = app(PermissionService::class);
        $workspaceTools = [
            'services' => $permissionService->allows('capabilities.view', $request->user(), $business),
            'work' => $permissionService->allows('work.view', $request->user(), $business),
            'calendar' => $permissionService->allows('calendar.view', $request->user(), $business),
            'customers' => $permissionService->allows('customers.view', $request->user(), $business),
            'quotes' => $permissionService->allows('quotes.view', $request->user(), $business),
            'finance' => $permissionService->allows('finance.view', $request->user(), $business),
            'purchasing' => $permissionService->allows('purchasing.view', $request->user(), $business),
            'suppliers' => $permissionService->allows('suppliers.view', $request->user(), $business),
            'inventory' => $permissionService->allows('inventory.view', $request->user(), $business),
            'assets' => $permissionService->allows('assets.view', $request->user(), $business),
            'reports' => $permissionService->allows('reports.view', $request->user(), $business),
        ];

        $isOwner = app(CurrentBusiness::class)->hasRole('owner', $request->user(), $business);

        return view('dashboard', compact(
            'metrics',
            'upcoming',
            'business',
            'isOwner',
            'workspaceTools',
            'experienceLevel'
        ));
    }
}
