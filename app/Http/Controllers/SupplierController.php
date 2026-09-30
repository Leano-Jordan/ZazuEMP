<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\EventRequirement;
use App\Support\Audit;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        $suppliers = Supplier::where('business_id', $businessId)->withCount('purchaseOrders')->orderBy('name')->get();
        $resourceRequirements = EventRequirement::query()
            ->whereHas('event', fn ($query) => $query->where('business_id', $businessId)->whereNotIn('status', ['completed', 'cancelled']))
            ->with(['event.customer', 'capability'])
            ->orderBy('created_at')
            ->get();

        return view('suppliers.index', compact('suppliers', 'resourceRequirements'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        $data = $this->validated($request);

        $supplier = Supplier::create([...$data, 'business_id' => $businessId]);

        Audit::record('suppliers.created', $supplier, [
            'name' => $supplier->name,
        ], $businessId);

        return redirect()->route('suppliers.index')->with('success', 'Supplier added.');
    }

    public function edit(Request $request, Supplier $supplier): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        $this->ensureBusiness($supplier, $businessId);

        return view('suppliers.create', ['supplier' => $supplier]);
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        $this->ensureBusiness($supplier, $businessId);
        $data = $this->validated($request);

        DB::transaction(function () use ($supplier, $businessId, $data): void {
            $lockedSupplier = Supplier::query()
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->findOrFail($supplier->id);

            $lockedSupplier->update($data);

            Audit::record('suppliers.updated', $lockedSupplier, [
                'name' => $lockedSupplier->name,
            ], $businessId);
        });

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function ensureBusiness(Supplier $supplier, int $businessId): void
    {
        abort_unless((int) $supplier->business_id === $businessId, 404);
    }
}
