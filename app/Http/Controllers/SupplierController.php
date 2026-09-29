<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\EventRequirement;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        $suppliers = Supplier::where('business_id',$businessId)->withCount('purchaseOrders')->orderBy('name')->get();
        $resourceRequirements = EventRequirement::query()
            ->whereHas('event', fn ($query) => $query->where('business_id', $businessId)->whereNotIn('status', ['completed', 'cancelled']))
            ->with(['event.customer', 'capability'])
            ->orderBy('created_at')
            ->get();
        return view('suppliers.index', compact('suppliers', 'resourceRequirements'));
    }

    public function create(): View { return view('suppliers.create'); }

    public function edit(Request $request, Supplier $supplier): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        abort_unless((int) $supplier->business_id === $businessId, 404);

        return view('suppliers.create', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        abort_unless((int) $supplier->business_id === $businessId, 404);

        $data = $request->validate([
            'name'=>['required','string','max:255'],
            'contact_name'=>['nullable','string','max:255'],
            'email'=>['nullable','email','max:255'],
            'phone'=>['nullable','string','max:50'],
            'notes'=>['nullable','string'],
        ]);

        $supplier->update($data);

        return redirect()->route('suppliers.index')->with('success','Supplier updated.');
    }

    public function store(Request $request): RedirectResponse
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        $data = $request->validate([
            'name'=>['required','string','max:255'],
            'contact_name'=>['nullable','string','max:255'],
            'email'=>['nullable','email','max:255'],
            'phone'=>['nullable','string','max:50'],
            'notes'=>['nullable','string'],
        ]);
        Supplier::create([...$data,'business_id'=>$businessId]);
        return redirect()->route('suppliers.index')->with('success','Supplier added.');
    }
}
