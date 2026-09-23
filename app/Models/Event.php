<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'reference',
        'name',
        'event_type',
        'customer_name',
        'customer_phone',
        'customer_email',
        'event_date',
        'event_address',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
        ];
    }
}