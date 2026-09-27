<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quote extends Model
{
    public const STATUS_TRANSITIONS = [
        'draft' => ['sent'],
        'sent' => ['accepted', 'declined', 'expired'],
        'declined' => ['draft'],
        'expired' => ['draft'],
        'accepted' => [],
    ];

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::STATUS_TRANSITIONS[$this->status] ?? [], true);
    }

    use HasFactory;

    protected $fillable = [
        'event_id',
        'reference',
        'status',
        'currency',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class)->withTrashed();
    }

    public function versions(): HasMany
    {
        return $this->hasMany(QuoteVersion::class);
    }

    public function latestVersion(): HasOne
    {
        return $this->versions()->one()->latestOfMany('version');
    }
}