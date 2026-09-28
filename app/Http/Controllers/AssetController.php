<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\BusinessCapability;
use App\Models\Event;
use App\Models\EventRequirement;
use App\Services\EventLifecycleService;
use App\Support\Audit;
use App\Support\CurrentBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        $assets=Asset::where('business_id',$businessId)->with(['capability','allocations'=>fn($q)=>$q->where('status','allocated')->with('event')])->orderBy('name')->get();
        $events=Event::where('business_id',$businessId)->whereNotIn('status',['completed','cancelled'])->orderByDesc('event_date')->get();
        $demand = EventRequirement::query()
            ->whereHas('event', fn ($query) => $query->where('business_id', $businessId)->whereNotIn('status', ['completed', 'cancelled']))
            ->whereHas('capability', fn ($query) => $query->where('business_id', $businessId)->where('capability_type', 'rental'))
            ->with('event.customer', 'capability')
            ->orderBy('created_at')
            ->get();
        return view('assets.index',compact('assets','events','demand'));
    }

    public function create(Request $request): View
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        $capabilities=BusinessCapability::where('business_id',$businessId)->where('is_active',true)->where('capability_type','rental')->orderBy('name')->get();
        return view('assets.create',compact('capabilities'));
    }

    public function edit(Request $request, Asset $asset): View
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        abort_unless((int) $asset->business_id === $businessId, 404);

        $capabilities = BusinessCapability::query()
            ->where('business_id', $businessId)
            ->where('is_active', true)
            ->where('capability_type', 'rental')
            ->orderBy('name')
            ->get();

        return view('assets.create', compact('asset', 'capabilities'));
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $businessId = app(CurrentBusiness::class)->id($request->user());
        abort_unless((int) $asset->business_id === $businessId, 404);

        $data = $request->validate([
            'asset_tag' => ['required', 'string', 'max:100', Rule::unique('assets', 'asset_tag')
                ->where(fn ($query) => $query->where('business_id', $businessId))
                ->ignore($asset->id)],
            'name' => ['required', 'string', 'max:255'],
            'condition' => ['required', 'in:good,fair,poor,damaged'],
            'location' => ['nullable', 'string', 'max:255'],
            'acquired_at' => ['nullable', 'date'],
            'purchase_cost' => ['required', 'numeric', 'decimal:0,2', 'min:0'],
            'capability_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
        ]);

        if (!empty($data['capability_id'])) {
            abort_unless(
                BusinessCapability::where('business_id', $businessId)
                    ->where('capability_type', 'rental')
                    ->whereKey($data['capability_id'])
                    ->exists(),
                404
            );
        }

        DB::transaction(function () use ($asset, $businessId, $data): void {
            $lockedAsset = Asset::query()
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->findOrFail($asset->id);

            $lockedAsset->update($data);

            Audit::record('assets.updated', $lockedAsset, [
                'condition' => $lockedAsset->condition,
                'location' => $lockedAsset->location,
                'capability_id' => $lockedAsset->capability_id,
            ], $businessId);
        });

        return redirect()->route('assets.index')->with('success', 'Asset details updated.');
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
        if (!empty($data['capability_id'])) {
            abort_unless(BusinessCapability::where('business_id',$businessId)->where('capability_type','rental')->whereKey($data['capability_id'])->exists(), 404);
        }
        Asset::create([...$data,'business_id'=>$businessId,'currency'=>$currency,'status'=>'available']);
        return redirect()->route('assets.index')->with('success','Asset added to the register.');
    }

    public function allocate(Request $request, Asset $asset, EventLifecycleService $lifecycle): RedirectResponse
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        abort_unless((int)$asset->business_id===$businessId,404);
        $data=$request->validate([
            'event_id'=>['required','integer'],'allocated_from'=>['required','date'],'allocated_until'=>['nullable','date','after_or_equal:allocated_from'],'notes'=>['nullable','string'],
        ]);
        return DB::transaction(function () use ($asset, $businessId, $data, $lifecycle): RedirectResponse {
            $event = $lifecycle->lock($businessId, (int) $data['event_id']);
            $lifecycle->assertOperational($event);
            $lockedAsset = Asset::query()
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->findOrFail($asset->id);

            abort_if($lockedAsset->status!=='available',422,'Only available assets can be allocated.');
            abort_if($data['allocated_until'] && $data['allocated_from'] > $data['allocated_until'], 422, 'Allocation dates are invalid.');

            $overlap=$lockedAsset->allocations()->where('status','allocated')
                ->whereDate('allocated_from','<=',$data['allocated_until'] ?: $data['allocated_from'])
                ->where(function($query) use ($data) {
                    $query->whereNull('allocated_until')->orWhereDate('allocated_until','>=',$data['allocated_from']);
                })->exists();

            abort_if($overlap, 422, 'This asset is already allocated for the selected period.');

            $lockedAsset->allocations()->create([...$data,'business_id'=>$businessId,'status'=>'allocated']);
            $lockedAsset->update(['status'=>'allocated']);

            Audit::record('assets.allocated', $lockedAsset, [
                'event_id' => $event->id,
                'allocation_id' => $lockedAsset->allocations()->latest('id')->value('id'),
                'allocated_from' => $data['allocated_from'],
                'allocated_until' => $data['allocated_until'],
            ], $businessId);

            return back()->with('success',$lockedAsset->name.' allocated to '.$event->name.'.');
        });
    }

    public function release(Request $request, Asset $asset): RedirectResponse
    {
        $businessId=app(CurrentBusiness::class)->id($request->user());
        abort_unless((int)$asset->business_id===$businessId,404);

        return DB::transaction(function () use ($asset, $businessId): RedirectResponse {
            $lockedAsset = Asset::query()
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->findOrFail($asset->id);

            $allocation=$lockedAsset->allocations()->where('status','allocated')->latest()->lockForUpdate()->first();
            if($allocation){
                $allocation->update([
                    'status' => 'returned',
                    'allocated_until' => $allocation->allocated_until ?? now()->toDateString(),
                ]);

                Audit::record('assets.released', $lockedAsset, [
                    'event_id' => $allocation->event_id,
                    'allocation_id' => $allocation->id,
                ], $businessId);
            }

            $lockedAsset->update(['status'=>'available']);

            return back()->with('success','Asset returned to available status.');
        });
    }
}
