<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAllocation extends Model
{
    use HasFactory;

    protected $fillable = ['business_id','asset_id','event_id','allocated_from','allocated_until','status','notes'];

    protected function casts(): array { return ['allocated_from'=>'date','allocated_until'=>'date']; }

    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
}
