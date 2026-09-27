<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Customer;
use App\Models\Event;
use App\Models\FinanceExpense;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\Supplier;
use App\Support\CurrentBusiness;
use App\Support\PermissionService;
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

        $permissionService = app(PermissionService::class);
        $workspaceTools = [
            'finance' => $permissionService->allows('finance.view', $request->user(), $business)
                && (
                    Invoice::query()->where('business_id', $businessId)->exists()
                    || Payment::query()->where('business_id', $businessId)->exists()
                    || FinanceExpense::query()->where('business_id', $businessId)->exists()
                ),
            'purchasing' => $permissionService->allows('purchasing.view', $request->user(), $business)
                && (
                    Supplier::query()->where('business_id', $businessId)->exists()
                    || PurchaseOrder::query()->where('business_id', $businessId)->exists()
                ),
            'inventory' => $permissionService->allows('inventory.view', $request->user(), $business)
                && InventoryItem::query()->where('business_id', $businessId)->exists(),
            'assets' => $permissionService->allows('assets.view', $request->user(), $business)
                && Asset::query()->where('business_id', $businessId)->exists(),
            'reports' => $permissionService->allows('reports.view', $request->user(), $business)
                && (
                    (clone $eventQuery)->whereIn('status', ['completed', 'cancelled'])->exists()
                    || Invoice::query()->where('business_id', $businessId)->exists()
                ),
        ];

        $isOwner = app(CurrentBusiness::class)->hasRole('owner', $request->user(), $business);

        return view('dashboard', compact(
            'metrics',
            'upcoming',
            'business',
            'isOwner',
            'workspaceTools'
        ));
    }
}
