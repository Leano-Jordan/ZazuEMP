<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceNumberSequence extends Model
{
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
