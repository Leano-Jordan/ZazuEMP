<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = ['business_id','invoice_id','type','idempotency_key','event_id','amount','currency','method','reference','paid_at','notes'];

    protected function casts(): array { return ['amount'=>'decimal:2','paid_at'=>'date']; }

    public function isDeposit(): bool
    {
        return $this->type === 'deposit';
    }

    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function event(): BelongsTo { return $this->belongsTo(Event::class)->withTrashed(); }
}
