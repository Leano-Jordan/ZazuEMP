<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\FinanceExpense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quote;
use App\Models\Supplier;
use App\Models\TaxRate;
use App\Support\Audit;
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
        $invoicedTotal = Invoice::where('business_id', $businessId)->sum('total');
        $invoicedCents = Money::toCents((string) $invoicedTotal);
        $paidTotal = Payment::where('business_id', $businessId)->sum('amount');
        $expensesTotal = FinanceExpense::where('business_id', $businessId)->sum('amount');
        // DECIMAL values must remain decimal strings. Converting aggregate totals through float can silently lose cents on larger ledgers.
        $paidCents = Money::toCents((string) $paidTotal);
        $expensesCents = Money::toCents((string) $expensesTotal);

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
            'idempotencyKey' => (string) Str::uuid(),
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
        $business = app(CurrentBusiness::class)->model($request->user());
        $business->loadMissing('taxProfile');

        $data = $request->validate([
            'idempotency_key' => ['nullable', 'uuid'],
            'quote_id' => ['nullable', 'integer'],
            'event_id' => ['nullable', 'integer'],
            'tax_rate_id' => ['nullable', 'integer'],
            'lines' => ['nullable', 'array', 'min:1', 'required_without:quote_id'],
            'lines.*.description' => ['required', 'string', 'max:500'],
            'lines.*.quantity' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            'lines.*.unit' => ['nullable', 'string', 'max:100'],
            'lines.*.unit_price' => ['required', 'numeric', 'decimal:0,2', 'min:0'],
            'issued_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'notes' => ['nullable', 'string'],
        ]);

        $businessId = $business->id;
        $data['idempotency_key'] ??= (string) Str::uuid();

        $quote = !empty($data['quote_id'])
            ? Quote::query()
                ->whereHas('event', fn ($q) => $q->where('business_id', $businessId))
                ->with(['latestVersion.items', 'event.customer.primaryContact'])
                ->findOrFail($data['quote_id'])
            : null;

        $event = !empty($data['event_id'])
            ? Event::query()
                ->where('business_id', $businessId)
                ->with('customer.primaryContact')
                ->findOrFail($data['event_id'])
            : $quote?->event;

        abort_if(
            $quote && $event && (int) $quote->event_id !== (int) $event->id,
            422,
            'The selected quote and job do not match.'
        );
        abort_unless($quote || $event, 422, 'Select a quote or job for the invoice.');

        $invoiceLines = [];
        $taxRateId = null;
        $taxCode = null;
        $taxLabel = 'No tax';
        $taxTreatment = 'out_of_scope';
        $taxRate = '0.00';

        if ($quote) {
            abort_unless(
                $quote->status === 'accepted' && $quote->latestVersion?->status === 'accepted',
                422,
                'Only an accepted quote can be converted into an invoice.'
            );
            abort_unless($quote->latestVersion, 422, 'The selected quote has no version to invoice.');

            $version = $quote->latestVersion;

            abort_if(
                Invoice::query()->where('quote_version_id', $version->id)->exists(),
                422,
                'This accepted quote version has already been invoiced.'
            );

            foreach ($version->items as $item) {
                $invoiceLines[] = [
                    'description' => $item->description,
                    'quantity' => (string) $item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => (string) $item->unit_price,
                    'line_total' => (string) $item->line_total,
                    'quote_item_id' => $item->id,
                ];
            }

            $subtotal = (string) $version->subtotal;
            $tax = (string) $version->tax_total;
            $total = (string) $version->total;
            $currency = $quote->currency;
            $taxRateId = $version->tax_rate_id;
            $taxCode = $version->tax_code;
            $taxLabel = $version->tax_label ?: 'No tax';
            $taxTreatment = $version->tax_treatment ?: 'out_of_scope';
            $taxRate = (string) ($version->tax_rate ?? '0.00');
        } else {
            $taxRateRecord = $this->resolveTaxRate($businessId, $data['tax_rate_id'] ?? null);

            $subtotalCents = 0;

            foreach ($data['lines'] as $line) {
                $quantityHundredths = Money::toHundredths((string) $line['quantity']);
                $unitPriceCents = Money::toCents((string) $line['unit_price']);
                $lineTotalCents = Money::multiplyQuantityByPrice($quantityHundredths, $unitPriceCents);

                $invoiceLines[] = [
                    'description' => trim($line['description']),
                    'quantity' => number_format($quantityHundredths / 100, 2, '.', ''),
                    'unit' => $line['unit'] ?? null,
                    'unit_price' => Money::fromCents($unitPriceCents),
                    'line_total' => Money::fromCents($lineTotalCents),
                    'quote_item_id' => null,
                ];

                $subtotalCents += $lineTotalCents;
            }

            $subtotal = Money::fromCents($subtotalCents);
            $taxCents = $this->calculateTaxCents($subtotalCents, (string) ($taxRateRecord?->rate ?? '0.00'));
            $tax = Money::fromCents($taxCents);
            $total = Money::fromCents($subtotalCents + $taxCents);
            $currency = $business->currency ?? 'ZAR';
            $taxRateId = $taxRateRecord?->id;
            $taxCode = $taxRateRecord?->code;
            $taxLabel = $taxRateRecord?->name ?: 'No tax';
            $taxTreatment = $taxRateRecord?->treatment ?: 'out_of_scope';
            $taxRate = (string) ($taxRateRecord?->rate ?? '0.00');
        }

        abort_unless(count($invoiceLines) > 0, 422, 'The invoice must contain at least one line.');

        $customer = $event?->customer;
        $quoteVersionId = $quote?->latestVersion?->id;

        $alreadyProcessed = false;

        $invoice = DB::transaction(function () use (
            $business,
            $businessId,
            $data,
            $event,
            $customer,
            $quoteVersionId,
            $currency,
            $subtotal,
            $tax,
            $total,
            $taxRateId,
            $taxCode,
            $taxLabel,
            $taxTreatment,
            $taxRate,
            $invoiceLines,
            &$alreadyProcessed
        ): Invoice {
            // Serialize the business-level idempotency check and quote-version conversion.
            \App\Models\Business::query()->whereKey($businessId)->lockForUpdate()->firstOrFail();

            $existingInvoice = Invoice::query()
                ->where('business_id', $businessId)
                ->where('idempotency_key', $data['idempotency_key'])
                ->first();

            if ($existingInvoice) {
                $alreadyProcessed = true;
                return $existingInvoice;
            }

            if ($quoteVersionId) {
                $lockedVersion = \App\Models\QuoteVersion::query()
                    ->whereKey($quoteVersionId)
                    ->lockForUpdate()
                    ->firstOrFail();

                abort_unless(
                    $lockedVersion->status === 'accepted',
                    409,
                    'The quote version changed while the invoice was being prepared. Please review it and try again.'
                );

                abort_if(
                    Invoice::query()->where('quote_version_id', $lockedVersion->id)->exists(),
                    422,
                    'This accepted quote version has already been invoiced.'
                );
            }

            $invoice = Invoice::create([
                'business_id' => $businessId,
                'idempotency_key' => $data['idempotency_key'],
                'event_id' => $event?->id,
                'quote_id' => $data['quote_id'] ?? null,
                'quote_version_id' => $quoteVersionId,
                'number' => 'INV-'.now()->format('Ym').'-'.Str::upper(Str::random(6)),
                'business_legal_name' => $business->taxProfile?->legal_name ?: $business->name,
                'business_trading_name' => $business->taxProfile?->trading_name ?: $business->name,
                'business_address' => $business->address,
                'business_email' => $business->email,
                'business_phone' => $business->phone,
                'business_tax_number' => $business->taxProfile?->income_tax_number ?: $business->tax_number,
                'business_vat_number' => $business->taxProfile?->vat_number,
                'customer_name' => $customer?->legal_name ?: $customer?->name,
                'customer_address' => $customer?->billing_address,
                'customer_email' => $customer?->primaryContact?->email,
                'customer_phone' => $customer?->primaryContact?->phone,
                'customer_tax_number' => $customer?->tax_number,
                'customer_vat_number' => $customer?->vat_number,
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

            $invoice->items()->createMany($invoiceLines);
            Audit::record('finance.invoice.created', $invoice, [
                'number' => $invoice->number,
                'total' => $invoice->total,
                'currency' => $invoice->currency,
            ], $businessId);

            return $invoice;
        });

        if ($alreadyProcessed) {
            return redirect()
                ->route('finance.invoices.show', $invoice)
                ->with('info', 'That invoice submission was already processed.');
        }

        return redirect()
            ->route('finance.invoices.show', $invoice)
            ->with('success', 'Invoice '.$invoice->number.' created.');
    }

    public function showInvoice(Request $request, Invoice $invoice): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());

        $invoice->load(['items', 'event.customer.primaryContact']);

        abort_unless((int) $invoice->business_id === $businessId, 404);

        return view('finance.invoice-show', [
            'invoice' => $invoice,
        ]);
    }

    public function createPayment(Request $request): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());

        return view('finance.payment-create', [
            'idempotencyKey' => (string) Str::uuid(),
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
            'idempotency_key' => ['nullable', 'uuid'],
            'invoice_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            'method' => ['required', 'in:cash,bank_transfer,card,other'],
            'reference' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['idempotency_key'] ??= (string) Str::uuid();
        $alreadyProcessed = false;

        DB::transaction(function () use ($data, $businessId, &$alreadyProcessed): void {
            // Serialize business-level idempotency checks so concurrent retries cannot both pass the lookup.
            \App\Models\Business::query()->whereKey($businessId)->lockForUpdate()->firstOrFail();

            if (Payment::query()
                ->where('business_id', $businessId)
                ->where('idempotency_key', $data['idempotency_key'])
                ->exists()
            ) {
                $alreadyProcessed = true;
                return;
            }

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

            Audit::record('finance.payment.recorded', $invoice->payments()->latest('id')->first(), [
                'invoice_id' => $invoice->id,
                'amount' => $data['amount'],
                'currency' => $invoice->currency,
                'method' => $data['method'],
            ], $businessId);
        });

        if ($alreadyProcessed) {
            return redirect()->route('finance.index')->with('info', 'That payment submission was already processed.');
        }

        return redirect()->route('finance.index')->with('success', 'Payment recorded.');
    }

    public function createExpense(Request $request): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());

        return view('finance.expense-create', [
            'idempotencyKey' => (string) Str::uuid(),
            'suppliers' => Supplier::where('business_id', $businessId)->orderBy('name')->get(),
            'events' => Event::where('business_id', $businessId)->whereNotIn('status', ['cancelled'])->orderByDesc('event_date')->get(),
        ]);
    }

    public function storeExpense(Request $request): RedirectResponse
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        $currency = app(CurrentBusiness::class)->model($request->user())->currency ?? 'ZAR';
        $data = $request->validate([
            'idempotency_key' => ['nullable', 'uuid'],
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

        $data['idempotency_key'] ??= (string) Str::uuid();
        $alreadyProcessed = false;

        $expense = DB::transaction(function () use ($data, $businessId, $currency, &$alreadyProcessed): ?FinanceExpense {
            // Serialize business-level idempotency checks so concurrent retries cannot both pass the lookup.
            \App\Models\Business::query()->whereKey($businessId)->lockForUpdate()->firstOrFail();

            $existingExpense = FinanceExpense::query()
                ->where('business_id', $businessId)
                ->where('idempotency_key', $data['idempotency_key'])
                ->first();

            if ($existingExpense) {
                $alreadyProcessed = true;
                return $existingExpense;
            }

            $expense = FinanceExpense::create([...$data, 'business_id' => $businessId, 'currency' => $currency]);
            Audit::record('finance.expense.recorded', $expense, [
                'amount' => $expense->amount,
                'currency' => $expense->currency,
                'status' => $expense->status,
            ], $businessId);

            return $expense;
        });

        if ($alreadyProcessed) {
            return redirect()->route('finance.index')->with('info', 'That expense submission was already processed.');
        }

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
