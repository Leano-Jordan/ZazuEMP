<?php

namespace App\Http\Controllers;

use App\Models\BusinessCapability;
use App\Models\Event;
use App\Models\InventoryItem;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        $items=InventoryItem::where('business_id',$businessId)->with(['capability','movements'])->orderBy('name')->get();
        $totalOnHand=$items->sum(fn($item)=>$item->on_hand);
        $lowStock=$items->filter(fn($item)=>$item->on_hand <= (float)$item->reorder_level)->count();
        return view('inventory.index',compact('items','totalOnHand','lowStock'));
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
        InventoryItem::create([...$data,'business_id'=>$businessId]);
        return redirect()->route('inventory.index')->with('success','Inventory item created.');
    }

    public function movement(Request $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        abort_unless((int)$inventoryItem->business_id===$businessId,404);
        $data=$request->validate([
            'type'=>['required','in:receipt,issue,return,adjustment_in,adjustment_out'],
            'quantity'=>['required','numeric','gt:0'],'unit_cost'=>['required','numeric','min:0'],
            'movement_date'=>['required','date'],'reference'=>['nullable','string','max:255'],'notes'=>['nullable','string'],
            'event_id'=>['nullable','integer'],
        ]);
        $inventoryItem->movements()->create([...$data,'business_id'=>$businessId]);
        return back()->with('success','Inventory movement recorded.');
    }
}
