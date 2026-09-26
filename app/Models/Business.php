<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'status',
        'logo_path',
        'dashboard_image_path',
        'wallpaper_path',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function capabilities(): HasMany
    {
        return $this->hasMany(BusinessCapability::class);
    }

    public function costs(): HasMany
    {
        return $this->hasMany(EventCost::class);
    }

    public function preparationItems(): HasMany
    {
        return $this->hasMany(EventPreparationItem::class);
    }
}
