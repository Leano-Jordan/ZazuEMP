<?php

namespace App\Http\Controllers;

use App\Models\BusinessCapability;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Support\Audit;
use App\Support\CurrentBusiness;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    private const STATUS_TRANSITIONS = [
        'draft' => ['sent', 'cancelled'],
        'sent' => ['ordered', 'cancelled'],
        'ordered' => ['received', 'cancelled'],
        'received' => [],
        'cancelled' => [],
    ];

    public function index(Request $request): View
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        $purchaseOrders=PurchaseOrder::where('business_id',$businessId)->with('supplier')->latest()->paginate(20);
        return view('purchasing.index',compact('purchaseOrders'));
    }

    public function create(Request $request): View
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        return view('purchasing.create',[
            'idempotencyKey'=>(string) Str::uuid(),
            'suppliers'=>Supplier::where('business_id',$businessId)->orderBy('name')->get(),
            'catalogue'=>BusinessCapability::where('business_id',$businessId)->where('is_active',true)->whereIn('capability_type',['product','rental'])->orderBy('name')->get(),
            'currency'=>app(CurrentBusiness::class)->model($request->user())->currency ?? 'ZAR',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        $data=$request->validate([
            'idempotency_key'=>['nullable','uuid'],
            'supplier_id'=>['required','integer'],
            'currency'=>['required','string','size:3', Rule::in(array_keys(config('zazu.currencies')))],
            'expected_at'=>['nullable','date'],
            'notes'=>['nullable','string'],
            'description'=>['required','array','min:1'],
            'description.*'=>['required','string','max:255'],
            'quantity'=>['required','array'],
            'quantity.*'=>['required','numeric','gt:0'],
            'unit'=>['nullable','array'],
            'unit.*'=>['nullable','string','max:50'],
            'unit_price'=>['required','array'],
            'unit_price.*'=>['required','numeric','min:0','decimal:0,2'],
            'capability_id'=>['nullable','array'],
            'capability_id.*'=>['nullable','integer'],
        ]);
        $supplier=Supplier::where('business_id',$businessId)->findOrFail($data['supplier_id']);
        $businessCurrency = app(CurrentBusiness::class)->model($request->user())->currency ?? 'ZAR';

        abort_unless(
            strtoupper($data['currency']) === strtoupper($businessCurrency),
            422,
            'Purchase orders must use the active business currency.'
        );

        $lineCount = count($data['description']);

        abort_unless(
            count($data['quantity']) === $lineCount
                && (empty($data['unit']) || count($data['unit']) === $lineCount)
                && (empty($data['capability_id']) || count($data['capability_id']) === $lineCount),
            422,
            'Purchase order lines are incomplete.'
        );

        abort_unless(
            count($data['description']) === count($data['unit_price']),
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

        $data['idempotency_key'] ??= (string) Str::uuid();

        $existingOrder = PurchaseOrder::query()->where('business_id',$businessId)->where('idempotency_key',$data['idempotency_key'])->first();
        if ($existingOrder) {
            return redirect()->route('purchasing.show',$existingOrder)->with('info','That purchase order submission was already processed.');
        }

        $order=\DB::transaction(function() use($data,$businessId,$supplier){
            $order=PurchaseOrder::create([
                'business_id'=>$businessId,'supplier_id'=>$supplier->id,'idempotency_key'=>$data['idempotency_key'],
                'reference'=>'PO-'.Str::upper(Str::random(8)),'status'=>'draft',
                'currency'=>strtoupper($data['currency']),'expected_at'=>$data['expected_at']??null,'notes'=>$data['notes']??null,
                'total_amount'=>'0.00',
            ]);
            $totalCents=0;
            foreach($data['description'] as $i=>$description){
                $qtyHundredths=Money::toHundredths((string)$data['quantity'][$i]);
                $priceCents=Money::toCents((string)$data['unit_price'][$i]);
                $lineCents=Money::multiplyQuantityByPrice($qtyHundredths,$priceCents);
                $totalCents += $lineCents;
                $qty=(float)$data['quantity'][$i]; $price=(float)$data['unit_price'][$i]; $line=Money::fromCents($lineCents);
                $order->items()->create([
                    'business_id'=>$businessId,'capability_id'=>$data['capability_id'][$i]??null,
                    'description'=>$description,'quantity'=>$qty,'unit'=>$data['unit'][$i]??null,
                    'unit_price'=>$price,'line_total'=>$line,
                ]);
            }
            $order->update(['total_amount'=>Money::fromCents($totalCents)]);
            Audit::record('purchasing.order.created', $order, [
                'reference' => $order->reference,
                'total' => $order->total_amount,
                'currency' => $order->currency,
            ], $businessId);
            return $order;
        });
        return redirect()->route('purchasing.show',$order)->with('success','Purchase order created.');
    }

    public function show(Request $request, PurchaseOrder $purchaseOrder): View
    {
        $this->ensure($request,$purchaseOrder);
        $purchaseOrder->load(['supplier','items.capability']);
        return view('purchasing.show',compact('purchaseOrder'));
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->ensure($request, $purchaseOrder);

        $data = $request->validate([
            'status' => ['required', 'in:draft,sent,ordered,received,cancelled'],
        ]);

        $businessId = app(CurrentBusiness::class)->id($request->user());

        DB::transaction(function () use ($purchaseOrder, $data, $businessId): void {
            $lockedOrder = PurchaseOrder::query()
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->findOrFail($purchaseOrder->id);

            $currentStatus = $lockedOrder->status;

            if ($currentStatus !== $data['status']) {
                abort_unless(
                    in_array($data['status'], self::STATUS_TRANSITIONS[$currentStatus] ?? [], true),
                    422,
                    'That purchase order status change is not allowed.'
                );
            }

            if ($data['status'] === 'ordered' && !$lockedOrder->ordered_at) {
                $lockedOrder->ordered_at = now()->toDateString();
            }

            $previousStatus = $lockedOrder->status;
            $lockedOrder->status = $data['status'];
            $lockedOrder->save();

            if ($data['status'] !== 'received') {
                return;
            }

            $lockedOrder->load('items');

            foreach ($lockedOrder->items as $item) {
                abort_unless((int) $item->business_id === $businessId && (int) $item->purchase_order_id === (int) $lockedOrder->id, 409, 'Purchase order line integrity could not be verified.');
                $inventoryItem = \App\Models\InventoryItem::query()
                    ->where('business_id', $businessId)
                    ->when($item->capability_id, fn ($query) => $query->where('capability_id', $item->capability_id))
                    ->where(function ($query) use ($item) {
                        $query->where('name', $item->description)
                            ->orWhere('sku', $item->description);
                    })
                    ->first();

                if (!$inventoryItem) {
                    // Serialize auto-creation across concurrent receipts from different orders.
                    \App\Models\Business::query()->whereKey($businessId)->lockForUpdate()->firstOrFail();
                    $inventoryItem = \App\Models\InventoryItem::query()
                        ->where('business_id', $businessId)
                        ->when($item->capability_id, fn ($query) => $query->where('capability_id', $item->capability_id))
                        ->where(function ($query) use ($item) {
                            $query->where('name', $item->description)
                                ->orWhere('sku', $item->description);
                        })
                        ->first();
                    if (!$inventoryItem) {
                        $inventoryItem = \App\Models\InventoryItem::create([
                        'business_id' => $businessId,
                        'capability_id' => $item->capability_id,
                        'name' => $item->description,
                        'unit' => $item->unit ?: 'unit',
                        'reorder_level' => 0,
                        ]);
                    }
                }

                $alreadyReceived = \App\Models\InventoryMovement::query()
                    ->where('business_id', $businessId)
                    ->where('purchase_order_id', $lockedOrder->id)
                    ->where('purchase_order_item_id', $item->id)
                    ->where('inventory_item_id', $inventoryItem->id)
                    ->where('type', 'receipt')
                    ->exists();

                if (!$alreadyReceived) {
                    $inventoryItem->movements()->create([
                        'business_id' => $businessId,
                        'purchase_order_id' => $lockedOrder->id,
                        'purchase_order_item_id' => $item->id,
                        'type' => 'receipt',
                        'quantity' => $item->quantity,
                        'unit_cost' => $item->unit_price,
                        'movement_date' => now()->toDateString(),
                        'reference' => $lockedOrder->reference,
                        'notes' => 'Received from purchase order.',
                    ]);
                }
            }

            if ($previousStatus !== $lockedOrder->status) {
                Audit::record('purchasing.order.status_changed', $lockedOrder, [
                    'from' => $previousStatus,
                    'to' => $lockedOrder->status,
                ], $businessId);
            }
        });

        return back()->with('success', 'Purchase order status updated.');
    }

    private function ensure(Request $request, PurchaseOrder $order): void
    {
        abort_unless((int)$order->business_id===app(CurrentBusiness::class)->id($request->user()),404);
        abort_unless((int)$order->supplier->business_id===app(CurrentBusiness::class)->id($request->user()),404);
    }
}
