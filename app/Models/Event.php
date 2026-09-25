<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    protected $fillable = [
        'business_id',
        'customer_id',
        'event_day_contact_id',
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

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function eventDayContact(): BelongsTo
    {
        return $this->belongsTo(CustomerContact::class, 'event_day_contact_id');
    }
}