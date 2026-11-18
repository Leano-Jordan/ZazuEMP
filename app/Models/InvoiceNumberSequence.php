<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;

class InvoiceNumberSequence extends Model
{
    use BelongsToBusiness;
    protected $fillable = [
        'business_id',
        'period',
        'next_number',
    ];

    protected function casts(): array
    {
        return [
            'business_id' => 'integer',
            'next_number' => 'integer',
        ];
    }
}
