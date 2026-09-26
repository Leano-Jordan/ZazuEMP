<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessTaxProfile extends Model
{
    use HasFactory;

    protected $hidden = ['tcs_pin'];

    protected $fillable = [
        'business_id',
        'legal_name',
        'trading_name',
        'registration_type',
        'registration_number',
        'tax_regime',
        'income_tax_number',
        'vat_status',
        'vat_number',
        'paye_number',
        'uif_number',
        'sdl_number',
        'financial_year_end',
        'representative_name',
        'representative_email',
        'representative_phone',
        'tcs_reference',
        'tcs_pin',
        'tcs_pin_expires_at',
        'tcs_last_checked_at',
        'activity_flags',
        'compliance_notes',
    ];

    protected function casts(): array
    {
        return [
            'financial_year_end' => 'date',
            'tcs_pin' => 'encrypted',
            'tcs_pin_expires_at' => 'datetime',
            'tcs_last_checked_at' => 'datetime',
            'activity_flags' => 'array',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}