<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventAttachment extends Model
{
    protected $fillable = [
        'event_id',
        'business_id',
        'uploaded_by',
        'original_name',
        'disk',
        'path',
        'mime_type',
        'size',
        'source',
        'description',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}