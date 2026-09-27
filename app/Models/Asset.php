<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = ['business_id','capability_id','asset_tag','name','status','condition','location','acquired_at','purchase_cost','currency','notes'];

    protected function casts(): array { return ['acquired_at'=>'date','purchase_cost'=>'decimal:2']; }

    public function capability(): BelongsTo { return $this->belongsTo(BusinessCapability::class); }
    public function allocations(): HasMany { return $this->hasMany(AssetAllocation::class); }
}
