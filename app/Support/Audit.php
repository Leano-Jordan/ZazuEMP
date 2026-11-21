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
        $syncDevice = request()->attributes->get('sync_device');
        $deviceActorId = $syncDevice?->approved_by_user_id;

        if ($businessId === null) {
            $businessId = $syncDevice?->business_id ?? app(CurrentBusiness::class)->id($user);
        }

        $actorUserId = $user?->getAuthIdentifier() ?? $deviceActorId;
        if (! $user && $syncDevice) {
            $metadata = array_merge($metadata, [
                'actor_source' => 'owner_approved_sync_device',
                'sync_device_id' => (int) $syncDevice->getKey(),
            ]);
        }

        AuditLog::create([
            'business_id' => $businessId,
            'user_id' => $actorUserId,
            'action' => $action,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
            'metadata' => $metadata,
            'request_id' => request()->attributes->get('zazu_request_id'),
            'created_at' => now(),
        ]);
    }
}
