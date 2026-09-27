<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = ['business_id', 'name', 'contact_name', 'email', 'phone', 'notes'];

    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
    public function purchaseOrders(): HasMany { return $this->hasMany(PurchaseOrder::class); }
}
