<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\FinanceExpense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quote;
use App\Models\Supplier;
use App\Support\CurrentBusiness;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(Request $request): View
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        $invoices=Invoice::where('business_id',$businessId)->with('payments')->latest()->get();
        $payments=Payment::where('business_id',$businessId)->latest('paid_at')->limit(10)->get();
        $expenses=FinanceExpense::where('business_id',$businessId)->latest('expense_date')->limit(10)->get();
        $invoiced=$invoices->sum(fn($i)=>(float)$i->total);
        $paid=$payments->sum(fn($p)=>(float)$p->amount);
        $expensesTotal=$expenses->sum(fn($e)=>(float)$e->amount);
        return view('finance.index',compact('invoices','payments','expenses','invoiced','paid','expensesTotal'));
    }

    public function createInvoice(Request $request): View
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        return view('finance.invoice-create',[
            'quotes'=>Quote::whereHas('event',fn($q)=>$q->where('business_id',$businessId))->with(['event.customer','latestVersion'])->latest()->get(),
            'events'=>Event::where('business_id',$businessId)->whereNotIn('status',['cancelled'])->orderByDesc('event_date')->get(),
        ]);
    }

    public function storeInvoice(Request $request): RedirectResponse
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        $data=$request->validate([
            'quote_id'=>['nullable','integer'],'event_id'=>['nullable','integer'],
            'issued_at'=>['nullable','date'],'due_at'=>['nullable','date'],'notes'=>['nullable','string'],
        ]);
        $quote=$data['quote_id']?Quote::whereHas('event',fn($q)=>$q->where('business_id',$businessId))->with('latestVersion')->findOrFail($data['quote_id']):null;
        $event=$data['event_id']?Event::where('business_id',$businessId)->findOrFail($data['event_id']):$quote?->event;
        abort_unless($quote || $event,422,'Select a quote or job for the invoice.');
        $version=$quote?->latestVersion;
        $total=$version?->total ?? 0; $subtotal=$version?->subtotal ?? $total; $tax=$version?->tax_total ?? 0;
        $currency=$quote?->currency ?? app(CurrentBusiness::class)->model($request->user())->currency;
        $invoice=Invoice::create([
            'business_id'=>$businessId,'event_id'=>$event?->id,'quote_id'=>$quote?->id,
            'number'=>'INV-'.now()->format('Ym').'-'.Str::upper(Str::random(6)),
            'status'=>'issued','currency'=>$currency,'subtotal'=>$subtotal,'tax_total'=>$tax,'total'=>$total,
            'issued_at'=>$data['issued_at']??now()->toDateString(),'due_at'=>$data['due_at']??now()->addDays(7)->toDateString(),'notes'=>$data['notes']??null,
        ]);
        return redirect()->route('finance.index')->with('success','Invoice '.$invoice->number.' created.');
    }

    public function createPayment(Request $request): View
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        return view('finance.payment-create',['invoices'=>Invoice::where('business_id',$businessId)->whereNotIn('status',['paid','void'])->with('event.customer')->orderByDesc('issued_at')->get()]);
    }

    public function storePayment(Request $request): RedirectResponse
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        $data=$request->validate([
            'invoice_id'=>['required','integer'],'amount'=>['required','numeric','gt:0'],'method'=>['required','in:cash,bank_transfer,card,other'],
            'reference'=>['nullable','string','max:255'],'paid_at'=>['required','date'],'notes'=>['nullable','string'],
        ]);
        $invoice=Invoice::where('business_id',$businessId)->with('payments')->findOrFail($data['invoice_id']);
        $totalCents = Money::toCents((string) $invoice->total);
        $paidCents = $invoice->payments->sum(fn ($payment) => Money::toCents((string) $payment->amount));
        $paymentCents = Money::toCents((string) $data['amount']);

        abort_if($paymentCents > ($totalCents - $paidCents), 422, 'Payment cannot exceed the outstanding invoice balance.');

        Payment::create([...$data,'business_id'=>$businessId,'event_id'=>$invoice->event_id,'currency'=>$invoice->currency]);

        $newPaidCents = $paidCents + $paymentCents;
        $invoice->update([
            'status' => $newPaidCents >= $totalCents ? 'paid' : 'issued',
        ]);
        return redirect()->route('finance.index')->with('success','Payment recorded.');
    }

    public function createExpense(Request $request): View
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        return view('finance.expense-create',[
            'suppliers'=>Supplier::where('business_id',$businessId)->orderBy('name')->get(),
            'events'=>Event::where('business_id',$businessId)->whereNotIn('status',['cancelled'])->orderByDesc('event_date')->get(),
        ]);
    }

    public function storeExpense(Request $request): RedirectResponse
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        $currency=app(CurrentBusiness::class)->model($request->user())->currency ?? 'ZAR';
        $data=$request->validate([
            'description'=>['required','string','max:255'],'amount'=>['required','numeric','gt:0'],'expense_date'=>['required','date'],
            'supplier_id'=>['nullable','integer'],'event_id'=>['nullable','integer'],'status'=>['required','in:unpaid,paid'],'reference'=>['nullable','string','max:255'],'notes'=>['nullable','string'],
        ]);
        FinanceExpense::create([...$data,'business_id'=>$businessId,'currency'=>$currency]);
        return redirect()->route('finance.index')->with('success','Finance expense recorded.');
    }
}
