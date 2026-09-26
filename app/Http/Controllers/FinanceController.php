<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\FinanceExpense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quote;
use App\Models\Supplier;
use App\Models\TaxRate;
use App\Support\CurrentBusiness;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(Request $request): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        $invoices = Invoice::where('business_id', $businessId)->with('payments')->latest()->get();
        $payments = Payment::where('business_id', $businessId)->latest('paid_at')->limit(10)->get();
        $expenses = FinanceExpense::where('business_id', $businessId)->latest('expense_date')->limit(10)->get();
        $invoicedCents = $invoices->sum(fn ($invoice) => Money::toCents((string) $invoice->total));
        $paidCents = $payments->sum(fn ($payment) => Money::toCents((string) $payment->amount));
        $expensesCents = $expenses->sum(fn ($expense) => Money::toCents((string) $expense->amount));

        return view('finance.index', [
            'invoices' => $invoices,
            'payments' => $payments,
            'expenses' => $expenses,
            'invoiced' => Money::fromCents($invoicedCents),
            'paid' => Money::fromCents($paidCents),
            'expensesTotal' => Money::fromCents($expensesCents),
        ]);
    }

    public function createInvoice(Request $request): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());

        return view('finance.invoice-create', [
            'quotes' => Quote::whereHas('event', fn ($q) => $q->where('business_id', $businessId))
                ->with(['event.customer', 'latestVersion'])
                ->latest()
                ->get(),
            'events' => Event::where('business_id', $businessId)
                ->whereNotIn('status', ['cancelled'])
                ->orderByDesc('event_date')
                ->get(),
            'taxRates' => $this->activeTaxRates($businessId),
            'defaultTaxRate' => $this->activeTaxRates($businessId)->firstWhere('is_default', true),
        ]);
    }

    public function storeInvoice(Request $request): RedirectResponse
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        $data = $request->validate([
            'quote_id' => ['nullable', 'integer'],
            'event_id' => ['nullable', 'integer'],
            'tax_rate_id' => ['nullable', 'integer'],
            'subtotal' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'required_without:quote_id'],
            'issued_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $quote = $data['quote_id']
            ? Quote::whereHas('event', fn ($q) => $q->where('business_id', $businessId))
                ->with('latestVersion')
                ->findOrFail($data['quote_id'])
            : null;

        $event = !empty($data['event_id'])
            ? Event::where('business_id', $businessId)->findOrFail($data['event_id'])
            : $quote?->event;

        abort_if($quote && $event && (int) $quote->event_id !== (int) $event->id, 422, 'The selected quote and job do not match.');
        abort_unless($quote || $event, 422, 'Select a quote or job for the invoice.');

        if ($quote) {
            abort_unless($quote->status === 'accepted' && $quote->latestVersion?->status === 'accepted', 422, 'Only an accepted quote can be converted into an invoice.');
            abort_unless($quote->latestVersion, 422, 'The selected quote has no version to invoice.');

            $version = $quote->latestVersion;
            $subtotal = (string) $version->subtotal;
            $tax = (string) $version->tax_total;
            $total = (string) $version->total;
            $currency = $quote->currency;
            $taxRateId = $version->tax_rate_id;
            $taxCode = $version->tax_code;
            $taxLabel = $version->tax_label;
            $taxTreatment = $version->tax_treatment;
            $taxRate = (string) ($version->tax_rate ?? '0.00');
        } else {
            $taxRateRecord = $this->resolveTaxRate($businessId, $data['tax_rate_id'] ?? null);
            $subtotal = Money::fromCents(Money::toCents((string) $data['subtotal']));
            $taxCents = $this->calculateTaxCents(Money::toCents($subtotal), (string) ($taxRateRecord?->rate ?? '0.00'));
            $tax = Money::fromCents($taxCents);
            $total = Money::fromCents(Money::toCents($subtotal) + $taxCents);
            $currency = app(CurrentBusiness::class)->model($request->user())->currency ?? 'ZAR';
            $taxRateId = $taxRateRecord?->id;
            $taxCode = $taxRateRecord?->code;
            $taxLabel = $taxRateRecord?->name ?: 'No tax';
            $taxTreatment = $taxRateRecord?->treatment ?: 'out_of_scope';
            $taxRate = (string) ($taxRateRecord?->rate ?? '0.00');
        }

        $invoice = Invoice::create([
            'business_id' => $businessId,
            'event_id' => $event?->id,
            'quote_id' => $quote?->id,
            'number' => 'INV-'.now()->format('Ym').'-'.Str::upper(Str::random(6)),
            'status' => 'issued',
            'currency' => $currency,
            'subtotal' => $subtotal,
            'tax_total' => $tax,
            'total' => $total,
            'issued_at' => $data['issued_at'] ?? now()->toDateString(),
            'due_at' => $data['due_at'] ?? now()->addDays(7)->toDateString(),
            'notes' => $data['notes'] ?? null,
            'tax_rate_id' => $taxRateId,
            'tax_code' => $taxCode,
            'tax_label' => $taxLabel,
            'tax_treatment' => $taxTreatment,
            'tax_rate' => $taxRate,
        ]);

        return redirect()->route('finance.index')->with('success', 'Invoice '.$invoice->number.' created.');
    }

    public function createPayment(Request $request): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());

        return view('finance.payment-create', [
            'invoices' => Invoice::where('business_id', $businessId)
                ->whereNotIn('status', ['paid', 'void'])
                ->with('event.customer')
                ->orderByDesc('issued_at')
                ->get(),
        ]);
    }

    public function storePayment(Request $request): RedirectResponse
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        $data = $request->validate([
            'invoice_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            'method' => ['required', 'in:cash,bank_transfer,card,other'],
            'reference' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data, $businessId): void {
            $invoice = Invoice::where('business_id', $businessId)
                ->whereKey($data['invoice_id'])
                ->with('payments')
                ->lockForUpdate()
                ->firstOrFail();

            abort_if(in_array($invoice->status, ['paid', 'void'], true), 422, 'This invoice cannot accept another payment.');

            $totalCents = Money::toCents((string) $invoice->total);
            $paidCents = $invoice->payments->sum(fn ($payment) => Money::toCents((string) $payment->amount));
            $paymentCents = Money::toCents((string) $data['amount']);

            abort_if($paymentCents > ($totalCents - $paidCents), 422, 'Payment cannot exceed the outstanding invoice balance.');

            Payment::create([
                ...$data,
                'business_id' => $businessId,
                'event_id' => $invoice->event_id,
                'currency' => $invoice->currency,
            ]);

            $newPaidCents = $paidCents + $paymentCents;

            $invoice->update([
                'status' => $newPaidCents >= $totalCents ? 'paid' : 'issued',
            ]);
        });

        return redirect()->route('finance.index')->with('success', 'Payment recorded.');
    }

    public function createExpense(Request $request): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());

        return view('finance.expense-create', [
            'suppliers' => Supplier::where('business_id', $businessId)->orderBy('name')->get(),
            'events' => Event::where('business_id', $businessId)->whereNotIn('status', ['cancelled'])->orderByDesc('event_date')->get(),
        ]);
    }

    public function storeExpense(Request $request): RedirectResponse
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        $currency = app(CurrentBusiness::class)->model($request->user())->currency ?? 'ZAR';
        $data = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            'expense_date' => ['required', 'date'],
            'supplier_id' => ['nullable', 'integer'],
            'event_id' => ['nullable', 'integer'],
            'status' => ['required', 'in:unpaid,paid'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        if (!empty($data['supplier_id'])) {
            abort_unless(Supplier::where('business_id', $businessId)->whereKey($data['supplier_id'])->exists(), 404);
        }

        if (!empty($data['event_id'])) {
            abort_unless(Event::where('business_id', $businessId)->whereKey($data['event_id'])->exists(), 404);
        }

        FinanceExpense::create([...$data, 'business_id' => $businessId, 'currency' => $currency]);

        return redirect()->route('finance.index')->with('success', 'Finance expense recorded.');
    }

    private function activeTaxRates(int $businessId)
    {
        $today = now()->toDateString();

        return TaxRate::query()
            ->where('business_id', $businessId)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $today)
            ->where(fn (Builder $query) => $query
                ->whereNull('effective_to')
                ->orWhereDate('effective_to', '>=', $today))
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    private function resolveTaxRate(int $businessId, $taxRateId): ?TaxRate
    {
        if ($taxRateId === null || $taxRateId === '') {
            return null;
        }

        $today = now()->toDateString();

        return TaxRate::query()
            ->where('business_id', $businessId)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', $today)
            ->where(fn (Builder $query) => $query
                ->whereNull('effective_to')
                ->orWhereDate('effective_to', '>=', $today))
            ->whereKey($taxRateId)
            ->firstOrFail();
    }

    private function calculateTaxCents(int $subtotalCents, string $ratePercent): int
    {
        $ratePercent = trim($ratePercent);

        if ($ratePercent === '' || $ratePercent === '0' || $ratePercent === '0.00') {
            return 0;
        }

        [$whole, $fraction] = array_pad(explode('.', $ratePercent, 2), 2, '0');
        $basisPoints = ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');

        return intdiv(($subtotalCents * $basisPoints) + 5000, 10000);
    }
}
