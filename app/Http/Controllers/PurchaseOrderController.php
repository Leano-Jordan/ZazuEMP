<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Models\Event;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\EventLifecycleService;
use App\Services\PurchaseOrderReceivingService;
use App\Support\Audit;
use App\Support\CurrentBusiness;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        $eventId = $request->integer('event_id') ?: null;

        if ($eventId !== null) {
            abort_unless(
                Event::query()->where('business_id', $businessId)->whereKey($eventId)->exists(),
                404
            );
        }

        $purchaseOrders = PurchaseOrder::query()
            ->where('business_id', $businessId)
            ->when($eventId !== null, fn ($query) => $query->where('event_id', $eventId))
            ->with(['supplier', 'event'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('purchasing.index', compact('purchaseOrders', 'eventId'));
    }

    public function create(Request $request): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());

        return view('purchasing.create', [
            'idempotencyKey' => (string) Str::uuid(),
            'suppliers' => Supplier::where('business_id', $businessId)->orderBy('name')->get(),
            'events' => Event::where('business_id', $businessId)
                ->whereIn('status', ['draft', 'confirmed', 'in_progress'])
                ->orderByDesc('event_date')
                ->get(),
            'catalogue' => BusinessCapability::where('business_id', $businessId)
                ->where('is_active', true)
                ->whereIn('capability_type', ['product', 'rental'])
                ->orderBy('name')
                ->get(),
            'currency' => app(CurrentBusiness::class)->model($request->user())->currency ?? 'ZAR',
        ]);
    }

    public function store(Request $request, EventLifecycleService $lifecycle): RedirectResponse
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        $data = $this->validateStoreData($request, $businessId);

        $supplier = Supplier::where('business_id', $businessId)->findOrFail($data['supplier_id']);
        $businessCurrency = app(CurrentBusiness::class)->model($request->user())->currency ?? 'ZAR';

        abort_unless(
            strtoupper($data['currency']) === strtoupper($businessCurrency),
            422,
            'Purchase orders must use the active business currency.'
        );

        $data['idempotency_key'] ??= (string) Str::uuid();

        $order = $this->createPurchaseOrder(
            $data,
            $businessId,
            $supplier,
            !empty($data['event_id']) ? (int) $data['event_id'] : null,
            $lifecycle
        );

        return redirect()
            ->route('purchasing.show', $order)
            ->with('success', 'Purchase order created.');
    }

    private function validateStoreData(Request $request, int $businessId): array
    {
        $data = $request->validate([
            'idempotency_key' => ['nullable', 'uuid'],
            'event_id' => ['nullable', 'integer'],
            'supplier_id' => ['required', 'integer'],
            'currency' => ['required', 'string', 'size:3', Rule::in(array_keys(config('zazu.currencies')))],
            'expected_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'description' => ['required', 'array', 'min:1'],
            'description.*' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'array'],
            'quantity.*' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            'unit' => ['nullable', 'array'],
            'unit.*' => ['nullable', 'string', 'max:50'],
            'unit_price' => ['required', 'array'],
            'unit_price.*' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'capability_id' => ['nullable', 'array'],
            'capability_id.*' => ['nullable', 'integer'],
        ]);

        $lineCount = count($data['description']);

        abort_unless(
            count($data['quantity']) === $lineCount
                && (empty($data['unit']) || count($data['unit']) === $lineCount)
                && (empty($data['capability_id']) || count($data['capability_id']) === $lineCount)
                && count($data['unit_price']) === $lineCount,
            422,
            'Purchase order lines are incomplete.'
        );

        $capabilityIds = collect($data['capability_id'] ?? [])
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        abort_unless(
            $capabilityIds->count() === BusinessCapability::query()
                ->where('business_id', $businessId)
                ->whereIn('capability_type', ['product', 'rental'])
                ->whereIn('id', $capabilityIds->all())
                ->count(),
            404,
            'One or more catalogue items do not belong to this business.'
        );

        return $data;
    }

    private function createPurchaseOrder(
        array $data,
        int $businessId,
        Supplier $supplier,
        ?int $eventId,
        EventLifecycleService $lifecycle
    ): PurchaseOrder {
        return DB::transaction(function () use ($data, $businessId, $supplier, $eventId, $lifecycle): PurchaseOrder {
            Business::query()
                ->whereKey($businessId)
                ->lockForUpdate()
                ->firstOrFail();

            $existingOrder = PurchaseOrder::query()
                ->where('business_id', $businessId)
                ->where('idempotency_key', $data['idempotency_key'])
                ->first();

            if ($existingOrder) {
                return $existingOrder;
            }

            if ($eventId !== null) {
                $event = $lifecycle->lock($businessId, $eventId);
                $lifecycle->assertOperational($event);
            }

            $order = PurchaseOrder::create([
                'business_id' => $businessId,
                'event_id' => $eventId,
                'supplier_id' => $supplier->id,
                'idempotency_key' => $data['idempotency_key'],
                'reference' => 'PO-' . Str::upper(Str::random(8)),
                'status' => 'draft',
                'currency' => strtoupper($data['currency']),
                'expected_at' => $data['expected_at'] ?? null,
                'notes' => $data['notes'] ?? null,
                'total_amount' => '0.00',
            ]);

            $totalCents = 0;

            foreach ($data['description'] as $i => $description) {
                $qtyHundredths = Money::toHundredths((string) $data['quantity'][$i]);
                $priceCents = Money::toCents((string) $data['unit_price'][$i]);
                $lineCents = Money::multiplyQuantityByPrice($qtyHundredths, $priceCents);
                $totalCents += $lineCents;

                $order->items()->create([
                    'business_id' => $businessId,
                    'capability_id' => $data['capability_id'][$i] ?? null,
                    'description' => trim($description),
                    'quantity' => number_format($qtyHundredths / 100, 2, '.', ''),
                    'unit' => $data['unit'][$i] ?? null,
                    'unit_price' => Money::fromCents($priceCents),
                    'line_total' => Money::fromCents($lineCents),
                ]);
            }

            $order->update(['total_amount' => Money::fromCents($totalCents)]);

            Audit::record('purchasing.order.created', $order, [
                'reference' => $order->reference,
                'total' => $order->total_amount,
                'currency' => $order->currency,
                'event_id' => $order->event_id,
            ], $businessId);

            return $order;
        });
    }

    public function show(Request $request, PurchaseOrder $purchaseOrder): View
    {
        $this->ensure($request, $purchaseOrder);
        $purchaseOrder->load(['supplier', 'event', 'items.capability']);

        return view('purchasing.show', compact('purchaseOrder'));
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function receive(
        Request $request,
        PurchaseOrder $purchaseOrder,
        PurchaseOrderReceivingService $receivingService,
        EventLifecycleService $lifecycle
    ): RedirectResponse {
        $this->ensure($request, $purchaseOrder);

        $data = $request->validate([
            'idempotency_key' => ['required', 'uuid'],
            'received_quantity' => ['required', 'array', 'min:1'],
            'received_quantity.*' => ['nullable', 'numeric', 'decimal:0,2', 'min:0'],
        ]);

        $businessId = app(CurrentBusiness::class)->id($request->user());

        abort_unless(
            collect($data['received_quantity'])->contains(
                fn ($received) => Money::toHundredths((string) ($received ?: '0')) > 0
            ),
            422,
            'Receive at least one positive quantity.'
        );

        $alreadyProcessed = $receivingService->receive(
            $purchaseOrder,
            $businessId,
            $data['received_quantity'],
            $data['idempotency_key'],
            $lifecycle
        );

        if ($alreadyProcessed) {
            return back()->with('info', 'That purchase receipt was already processed.');
        }

        return back()->with('success', 'Purchase receipt recorded.');
    }

    public function updateStatus(
        Request $request,
        PurchaseOrder $purchaseOrder,
        EventLifecycleService $lifecycle
    ): RedirectResponse {
        $this->ensure($request, $purchaseOrder);

        $data = $request->validate([
            'status' => ['required', 'in:draft,sent,ordered,received,cancelled'],
        ]);

        $businessId = app(CurrentBusiness::class)->id($request->user());

        DB::transaction(function () use ($purchaseOrder, $data, $businessId, $lifecycle): void {
            Business::query()
                ->whereKey($businessId)
                ->lockForUpdate()
                ->firstOrFail();

            $eventId = PurchaseOrder::query()
                ->where('business_id', $businessId)
                ->whereKey($purchaseOrder->id)
                ->value('event_id');

            $lockedEvent = $eventId
                ? $lifecycle->lock($businessId, (int) $eventId)
                : null;

            $lockedOrder = PurchaseOrder::query()
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->findOrFail($purchaseOrder->id);

            $currentStatus = $lockedOrder->status;
            $newStatus = $data['status'];

            if ($currentStatus === $newStatus) {
                return;
            }

            abort_unless(
                $lockedOrder->canTransitionTo($newStatus),
                422,
                'That purchase order status change is not allowed.'
            );

            if ($lockedEvent && $newStatus !== 'cancelled') {
                $lifecycle->assertOperational($lockedEvent);
            }

            if ($newStatus === 'ordered' && !$lockedOrder->ordered_at) {
                $lockedOrder->ordered_at = now()->toDateString();
            }

            if ($newStatus === 'received') {
                abort_unless(
                    $lockedOrder->isFullyReceived(),
                    422,
                    'A purchase order can only be marked received after all ordered quantities have been received.'
                );
            }

            $lockedOrder->status = $newStatus;
            $lockedOrder->save();

            Audit::record('purchasing.order.status_changed', $lockedOrder, [
                'from' => $currentStatus,
                'to' => $newStatus,
            ], $businessId);
        });

        return back()->with('success', 'Purchase order status updated.');
    }

    private function ensure(Request $request, PurchaseOrder $order): void
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());

        abort_unless((int) $order->business_id === $businessId, 404);
        abort_unless((int) $order->supplier->business_id === $businessId, 404);
    }
}
