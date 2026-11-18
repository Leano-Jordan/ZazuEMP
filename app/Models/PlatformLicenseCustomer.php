<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformLicenseCustomer extends Model
{
    protected $fillable = [
        'business_uuid',
        'business_name',
        'contact_email',
        'created_by',
        'last_license_id',
        'last_plan',
        'last_status',
        'last_starts_at',
        'last_expires_at',
        'last_issued_at',
    ];

    protected function casts(): array
    {
        return [
            'last_starts_at' => 'datetime',
            'last_expires_at' => 'datetime',
            'last_issued_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
