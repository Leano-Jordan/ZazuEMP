<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventCost;
use App\Models\EventPreparationItem;
use App\Models\FinanceExpense;
use App\Models\Invoice;
use App\Models\Quote;
use App\Support\CurrentBusiness;
use App\Support\Money;
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
                'total_cents' => $quotes->sum(
                    fn (Quote $quote) => Money::toCents((string) $quote->latestVersion->total)
                ),
            ]);

        $costTotalsByCurrency = EventCost::query()
            ->where('business_id', $businessId)
            ->whereNotIn('status', ['cancelled'])
            ->get()
            ->groupBy('currency')
            ->map(fn ($costs) => [
                'projected_cents' => $costs->sum(
                    fn (EventCost $cost) => Money::toCents((string) $cost->projected_amount)
                ),
                'actual_cents' => $costs->sum(
                    fn (EventCost $cost) => Money::toCents((string) ($cost->actual_amount ?? '0.00'))
                ),
            ]);

        $invoices = Invoice::query()
            ->where('business_id', $businessId)
            ->with('payments')
            ->get();

        $financeTotalsByCurrency = $invoices
            ->groupBy('currency')
            ->map(fn ($currencyInvoices) => [
                'invoiced_cents' => $currencyInvoices->sum(
                    fn (Invoice $invoice) => Money::toCents((string) $invoice->total)
                ),
                'paid_cents' => $currencyInvoices->sum(
                    fn (Invoice $invoice) => Money::toCents((string) $invoice->paid_amount)
                ),
                'outstanding_cents' => $currencyInvoices->sum(
                    fn (Invoice $invoice) => Money::toCents((string) $invoice->balance)
                ),
            ]);

        $expenseTotalsByCurrency = FinanceExpense::query()
            ->where('business_id', $businessId)
            ->whereNotIn('status', ['cancelled'])
            ->get()
            ->groupBy('currency')
            ->map(fn ($expenses) => $expenses->sum(
                fn (FinanceExpense $expense) => Money::toCents((string) $expense->amount)
            ));

        return view('reports.index', compact(
            'metrics',
            'jobsByStatus',
            'quoteTotalsByCurrency',
            'costTotalsByCurrency',
            'financeTotalsByCurrency',
            'expenseTotalsByCurrency'
        ));
    }
}
