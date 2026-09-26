<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\BusinessCapability;
use App\Models\Event;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        $assets=Asset::where('business_id',$businessId)->with(['capability','allocations'=>fn($q)=>$q->where('status','allocated')->with('event')])->orderBy('name')->get();
        return view('assets.index',compact('assets'));
    }

    public function create(Request $request): View
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        $capabilities=BusinessCapability::where('business_id',$businessId)->where('is_active',true)->where('capability_type','rental')->orderBy('name')->get();
        return view('assets.create',compact('capabilities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        $currency=app(CurrentBusiness::class)->model($request->user())->currency ?? 'ZAR';
        $data=$request->validate([
            'asset_tag'=>['required','string','max:100'],'name'=>['required','string','max:255'],
            'condition'=>['required','in:good,fair,poor,damaged'],'location'=>['nullable','string','max:255'],
            'acquired_at'=>['nullable','date'],'purchase_cost'=>['required','numeric','min:0'],
            'capability_id'=>['nullable','integer'],'notes'=>['nullable','string'],
        ]);
        Asset::create([...$data,'business_id'=>$businessId,'currency'=>$currency,'status'=>'available']);
        return redirect()->route('assets.index')->with('success','Asset added to the register.');
    }

    public function allocate(Request $request, Asset $asset): RedirectResponse
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        abort_unless((int)$asset->business_id===$businessId,404);
        $data=$request->validate([
            'event_id'=>['required','integer'],'allocated_from'=>['required','date'],'allocated_until'=>['nullable','date','after_or_equal:allocated_from'],'notes'=>['nullable','string'],
        ]);
        $event=Event::where('business_id',$businessId)->findOrFail($data['event_id']);
        abort_if($asset->status!=='available',422,'Only available assets can be allocated.');
        $asset->allocations()->create([...$data,'business_id'=>$businessId,'status'=>'allocated']);
        $asset->update(['status'=>'allocated']);
        return back()->with('success',$asset->name.' allocated to '.$event->name.'.');
    }

    public function release(Request $request, Asset $asset): RedirectResponse
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        abort_unless((int)$asset->business_id===$businessId,404);
        $allocation=$asset->allocations()->where('status','allocated')->latest()->first();
        if($allocation){ $allocation->update(['status'=>'returned','allocated_until'=>$allocation->allocated_until ?? now()->toDateString()]); }
        $asset->update(['status'=>'available']);
        return back()->with('success','Asset returned to available status.');
    }
}
