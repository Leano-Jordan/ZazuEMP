<?php

namespace App\Support\Offline;

use App\Models\SyncDevice;
use Illuminate\Support\Facades\DB;

/**
 * Revalidates middleware-authenticated device snapshots inside the operation
 * transaction, serializing sensitive sync work with device revocation.
 */
final class SyncDeviceAccessGuard
{
    public function lockActive(SyncDevice $authenticatedDevice): SyncDevice
    {
        DB::table('businesses')
            ->where('id', $authenticatedDevice->business_id)
            ->lockForUpdate()
            ->first();

        $device = SyncDevice::query()
            ->where('business_id', $authenticatedDevice->business_id)
            ->whereKey($authenticatedDevice->getKey())
            ->active()
            ->lockForUpdate()
            ->first();

        abort_unless($device, 401, 'This sync device is no longer active.');

        $expectedTokenHash = (string) (($authenticatedDevice->metadata ?? [])['sync_token_hash'] ?? '');
        $currentTokenHash = (string) (($device->metadata ?? [])['sync_token_hash'] ?? '');
        $sameCredential = $expectedTokenHash === ''
            ? $currentTokenHash === ''
            : hash_equals($expectedTokenHash, $currentTokenHash);

        abort_unless($sameCredential, 401, 'This sync device credential has been replaced.');

        return $device;
    }
}
