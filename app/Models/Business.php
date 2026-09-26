<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Business extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'catalogue_setup_completed_at' => 'datetime',
            'catalogue_setup_skipped_at' => 'datetime',
            'business_setup_completed_at' => 'datetime',
            'business_setup_skipped_at' => 'datetime',
        ];
    }

    protected $fillable = [
        'name',
        'slug',
        'status',
        'currency',
        'logo_path',
        'dashboard_image_path',
        'wallpaper_path',
        'email',
        'phone',
        'address',
        'website',
        'tax_number',
        'catalogue_setup_completed_at',
        'catalogue_setup_skipped_at',
        'business_setup_completed_at',
        'business_setup_skipped_at',
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

    public function taxProfile(): HasOne
    {
        return $this->hasOne(BusinessTaxProfile::class);
    }

    public function taxRates(): HasMany
    {
        return $this->hasMany(TaxRate::class);
    }

    public function complianceDocuments(): HasMany
    {
        return $this->hasMany(ComplianceDocument::class);
    }
}