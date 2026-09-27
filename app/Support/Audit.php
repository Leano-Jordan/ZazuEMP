<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class Audit
{
    public static function record(
        string $action,
        ?Model $subject = null,
        array $metadata = [],
        ?int $businessId = null,
    ): void {
        $user = auth()->user();
        $businessId ??= app(CurrentBusiness::class)->id($user);

        AuditLog::create([
            'business_id' => $businessId,
            'user_id' => $user?->getAuthIdentifier(),
            'action' => $action,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
            'metadata' => $metadata,
            'request_id' => request()->attributes->get('zazu_request_id'),
            'created_at' => now(),
        ]);
    }
}