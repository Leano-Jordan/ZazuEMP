<?php

namespace App\Support;

use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ZazuBusinessHealth
{
    /**
     * Inspect only the active business owner's workspace access and business-owned data paths.
     *
     * This deliberately does not call the installation-wide incident recorder or expose
     * the platform incident ledger to a business owner.
     */
    public function inspect(Business $business, User $user): array
    {
        $checks = [];

        try {
            DB::connection()->select('select 1 as zazu_business_health_probe');
        } catch (Throwable) {
            $checks[] = [
                'key' => 'database_connection',
                'label' => 'Workspace database access',
                'status' => 'failed',
                'message' => 'Zazu could not complete a read-only database check for this workspace. This check did not change your business records.',
                'next_step' => 'Do not reinstall Zazu, restore a backup or run database changes from this screen. Preserve the installation and contact Zazu support for guided help.',
            ];

            $checks[] = [
                'key' => 'business_access',
                'label' => 'Business access',
                'status' => 'not_checked',
                'message' => 'Not checked because the database did not answer.',
            ];

            $checks[] = [
                'key' => 'business_data_access',
                'label' => 'Business data access',
                'status' => 'not_checked',
                'message' => 'Not checked because the database did not answer.',
            ];

            return $this->result($checks);
        }

        $checks[] = [
            'key' => 'database_connection',
            'label' => 'Workspace database access',
            'status' => 'healthy',
            'message' => 'The database completed a read-only check.',
        ];

        try {
            $activeBusiness = Business::query()
                ->whereKey($business->getKey())
                ->where('status', 'active')
                ->exists();

            $ownerMembership = $business->users()
                ->whereKey($user->getKey())
                ->wherePivot('role', 'owner')
                ->exists();

            $accessHealthy = $activeBusiness && $ownerMembership;
        } catch (Throwable) {
            $accessHealthy = false;
        }

        $checks[] = [
            'key' => 'business_access',
            'label' => 'Business access',
            'status' => $accessHealthy ? 'healthy' : 'failed',
            'message' => $accessHealthy
                ? 'This account is an owner of the active business workspace.'
                : 'Zazu could not confirm active owner access to this workspace.',
            'next_step' => $accessHealthy
                ? null
                : 'Sign in with an authorised business-owner account. If access still fails, contact Zazu support before changing workspace membership.',
        ];

        if (! $accessHealthy) {
            $checks[] = [
                'key' => 'business_data_access',
                'label' => 'Business data access',
                'status' => 'not_checked',
                'message' => 'Not checked because active owner access could not be confirmed.',
            ];

            return $this->result($checks);
        }

        try {
            // Each relationship is constrained by the active business_id.
            $business->customers()->limit(1)->exists();
            $business->events()->limit(1)->exists();
            $business->capabilities()->limit(1)->exists();

            $dataHealthy = true;
        } catch (Throwable) {
            $dataHealthy = false;
        }

        $checks[] = [
            'key' => 'business_data_access',
            'label' => 'Business data access',
            'status' => $dataHealthy ? 'healthy' : 'failed',
            'message' => $dataHealthy
                ? 'Customer, event and service-catalogue data paths for this business responded.'
                : 'Zazu could not verify one or more business data paths. No records were changed by this check.',
            'next_step' => $dataHealthy
                ? null
                : 'Avoid importing, deleting or restoring records until the issue is understood. Contact Zazu support and include the time of this check.',
        ];

        return $this->result($checks);
    }

    private function result(array $checks): array
    {
        $statuses = array_column($checks, 'status');

        $status = in_array('failed', $statuses, true)
            ? 'failed'
            : (in_array('attention', $statuses, true)
                ? 'attention'
                : (in_array('unknown', $statuses, true) ? 'unknown' : 'healthy'));

        return [
            'scope' => 'active_business',
            'status' => $status,
            'checked_at' => now()->toIso8601String(),
            'checks' => $checks,
        ];
    }
}
