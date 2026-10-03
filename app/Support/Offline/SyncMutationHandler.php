<?php

namespace App\Support\Offline;

use App\Models\SyncMutation;

interface SyncMutationHandler
{
    /**
     * Apply one already-authorized mutation inside the caller's transaction.
     *
     * Implementations must enforce entity-specific validation and state rules.
     * They must never trust arbitrary payload fields or cross-business IDs.
     */
    public function apply(SyncMutation $mutation): void;
}
