<?php

namespace App\Http\Controllers;

use App\Models\BusinessCapability;
use App\Models\Event;
use App\Models\InventoryItem;
use App\Models\EventRequirement;
use App\Support\Audit;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        $items=InventoryItem::where('business_id',$businessId)->with(['capability','movements'])->orderBy('name')->get();
        $totalOnHand=$items->sum(fn($item)=>$item->on_hand);
        $lowStock=$items->filter(fn($item)=>$item->on_hand <= (float)$item->reorder_level)->count();
        $demand = EventRequirement::query()
            ->whereHas('event', fn ($query) => $query->where('business_id', $businessId)->whereNotIn('status', ['completed', 'cancelled']))
            ->whereHas('capability', fn ($query) => $query->where('business_id', $businessId)->where('capability_type', 'product'))
            ->with('event.customer', 'capability')
            ->orderBy('created_at')
            ->get();
        return view('inventory.index',compact('items','totalOnHand','lowStock','demand'));
    }

    public function create(Request $request): View
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        $capabilities=BusinessCapability::where('business_id',$businessId)->where('is_active',true)->where('capability_type','product')->orderBy('name')->get();
        return view('inventory.create',compact('capabilities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        $data=$request->validate([
            'name'=>['required','string','max:255'],'sku'=>['nullable','string','max:100'],
            'unit'=>['required','string','max:50'],'reorder_level'=>['required','numeric','min:0'],
            'capability_id'=>['nullable','integer'],
        ]);
        if (!empty($data['capability_id'])) {
            abort_unless(BusinessCapability::where('business_id',$businessId)->where('capability_type','product')->whereKey($data['capability_id'])->exists(), 404);
        }
        $item = InventoryItem::create([...$data,'business_id'=>$businessId]);
        Audit::record('inventory.item.created', $item, ['name' => $item->name], $businessId);
        return redirect()->route('inventory.index')->with('success','Inventory item created.');
    }

    public function movement(Request $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        abort_unless((int)$inventoryItem->business_id===$businessId,404);
        $data=$request->validate([
            'idempotency_key'=>['nullable','uuid'],
            'type'=>['required','in:receipt,issue,return,adjustment_in,adjustment_out'],
            'quantity'=>['required','numeric','gt:0'],'unit_cost'=>['required','numeric','min:0'],
            'movement_date'=>['required','date'],'reference'=>['nullable','string','max:255'],'notes'=>['nullable','string'],
            'event_id'=>['nullable','integer'],
        ]);
        if (!empty($data['event_id'])) {
            abort_unless(Event::where('business_id',$businessId)->whereKey($data['event_id'])->exists(), 404);
        }

        $data['idempotency_key'] ??= (string) \Illuminate\Support\Str::uuid();

        $existingMovement = \App\Models\InventoryMovement::query()
            ->where('business_id', $businessId)
            ->where('idempotency_key', $data['idempotency_key'])
            ->first();

        if ($existingMovement) {
            return back()->with('info', 'That stock submission was already processed.');
        }

        DB::transaction(function () use ($inventoryItem, $businessId, $data): void {
            $lockedItem = InventoryItem::query()
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->findOrFail($inventoryItem->id);

            if (in_array($data['type'], ['issue','adjustment_out'], true)) {
                $lockedItem->load('movements');
                $onHand = $lockedItem->on_hand;
                abort_if((float) $data['quantity'] > $onHand + 0.0001, 422, 'This movement would make stock on hand negative.');
            }

            $movement = $lockedItem->movements()->create([...$data,'business_id'=>$businessId]);
            Audit::record('inventory.movement.recorded', $movement, [
                'type' => $movement->type,
                'quantity' => $movement->quantity,
                'item_id' => $lockedItem->id,
            ], $businessId);
        });

        return back()->with('success','Inventory movement recorded.');
    }
}
