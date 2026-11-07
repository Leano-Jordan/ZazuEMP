<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Event;
use App\Models\Quote;
use App\Models\Asset;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Support\CurrentBusiness;
use App\Support\PermissionService;
use App\Support\ExperienceLevel;
use App\Support\NicheFocus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, NicheFocus $niches): View
    {
        $business = app(CurrentBusiness::class)->model($request->user());
        $businessId = $business->id;
        $today = now()->startOfDay();

        $eventQuery = Event::query()->where('business_id', $businessId);
        $customerQuery = Customer::query()->where('business_id', $businessId);
        $quoteQuery = Quote::query()
            ->whereHas('event', fn (Builder $query) => $query->where('business_id', $businessId));

        $quoteStatusCounts = (clone $quoteQuery)
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $metrics = [
            'active_work' => (clone $eventQuery)->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'upcoming_work' => (clone $eventQuery)
                ->whereBetween('event_date', [$today, $today->copy()->addDays(14)])
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
            'customers' => (clone $customerQuery)->count(),
            'draft_quotes' => (int) ($quoteStatusCounts['draft'] ?? 0),
            'open_purchase_orders' => PurchaseOrder::query()
                ->where('business_id', $businessId)
                ->whereIn('status', ['draft', 'sent', 'ordered'])
                ->count(),
            'available_assets' => Asset::query()
                ->where('business_id', $businessId)
                ->where('status', 'available')
                ->count(),
        ];

        $experienceLevel = app(ExperienceLevel::class)->for($request->user(), $business);
        $primaryNiche = $niches->for($request->user(), $business);
        $nicheDefinition = $niches->definition($primaryNiche);
        $supportingNiches = $niches->supporting($business, $primaryNiche);

        if ($experienceLevel !== 'basic') {
            $inventory = InventoryItem::query()
                ->where('business_id', $businessId)
                ->with('movements')
                ->get();

            $metrics['low_stock'] = $inventory
                ->filter(fn ($item) => $item->on_hand <= (float) $item->reorder_level)
                ->count();

            $metrics['unpaid_invoices'] = Invoice::query()
                ->where('business_id', $businessId)
                ->where('status', 'issued')
                ->with('payments')
                ->get()
                ->filter(fn ($invoice) => (float) $invoice->balance > 0)
                ->count();
        } else {
            $metrics['low_stock'] = 0;
            $metrics['unpaid_invoices'] = 0;
        }

        $upcoming = (clone $eventQuery)
            ->with('customer')
            ->where('event_date', '>=', $today)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('event_date')
            ->orderBy('name')
            ->limit(5)
            ->get();

        $workspaceTools = $this->workspaceToolsFor($request->user(), $business);

        $isOwner = app(CurrentBusiness::class)->hasRole('owner', $request->user(), $business);

        return view('dashboard', compact(
            'metrics',
            'quoteStatusCounts',
            'upcoming',
            'business',
            'isOwner',
            'workspaceTools',
            'experienceLevel',
            'primaryNiche',
            'nicheDefinition',
            'supportingNiches'
        ));
    }

    private function workspaceToolsFor(\Illuminate\Contracts\Auth\Authenticatable $user, \App\Models\Business $business): array
    {
        $permissions = [
            'services' => 'capabilities.view',
            'work' => 'work.view',
            'calendar' => 'calendar.view',
            'customers' => 'customers.view',
            'quotes' => 'quotes.view',
            'finance' => 'finance.view',
            'purchasing' => 'purchasing.view',
            'suppliers' => 'suppliers.view',
            'inventory' => 'inventory.view',
            'assets' => 'assets.view',
            'reports' => 'reports.view',
        ];

        $permissionService = app(PermissionService::class);

        return array_map(
            fn (string $permission): bool => $permissionService->allows($permission, $user, $business),
            $permissions
        );
    }
}
