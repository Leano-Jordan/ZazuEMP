<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int|null $business_id
 * @property string $name
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Business|null $business
 * @property-read \Illuminate\Database\Eloquent\Collection<int, CustomerContact> $contacts
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Event> $events
 * @property-read CustomerContact|null $primaryContact
 */
class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'name',
        'notes',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function primaryContact(): HasOne
    {
        return $this->hasOne(CustomerContact::class)->where('is_primary', true);
    }
}