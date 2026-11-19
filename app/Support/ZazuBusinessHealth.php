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
        if (! $this->databaseConnectionHealthy()) {
            return $this->result([
                $this->databaseConnectionCheck(false),
                $this->notChecked('business_access', 'Business access', 'Not checked because the database did not answer.'),
                $this->notChecked('business_data_access', 'Business data access', 'Not checked because the database did not answer.'),
            ]);
        }

        $checks = [$this->databaseConnectionCheck(true)];
        $accessHealthy = $this->businessAccessHealthy($business, $user);
        $checks[] = $this->businessAccessCheck($accessHealthy);

        if (! $accessHealthy) {
            $checks[] = $this->notChecked(
                'business_data_access',
                'Business data access',
                'Not checked because active owner access could not be confirmed.',
            );

            return $this->result($checks);
        }

        $checks[] = $this->businessDataAccessCheck($this->businessDataAccessHealthy($business));

        return $this->result($checks);
    }

    private function databaseConnectionHealthy(): bool
    {
        try {
            DB::connection()->select('select 1 as zazu_business_health_probe');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function businessAccessHealthy(Business $business, User $user): bool
    {
        try {
            $activeBusiness = Business::query()
                ->whereKey($business->getKey())
                ->where('status', 'active')
                ->exists();

            $ownerMembership = $business->users()
                ->whereKey($user->getKey())
                ->wherePivot('role', 'owner')
                ->exists();

            return $activeBusiness && $ownerMembership;
        } catch (Throwable) {
            return false;
        }
    }

    private function businessDataAccessHealthy(Business $business): bool
    {
        try {
            // Each relationship is constrained by the active business_id.
            $business->customers()->limit(1)->exists();
            $business->events()->limit(1)->exists();
            $business->capabilities()->limit(1)->exists();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function databaseConnectionCheck(bool $healthy): array
    {
        if ($healthy) {
            return [
                'key' => 'database_connection',
                'label' => 'Workspace database access',
                'status' => 'healthy',
                'message' => 'The database completed a read-only check.',
            ];
        }

        return [
            'key' => 'database_connection',
            'label' => 'Workspace database access',
            'status' => 'failed',
            'message' => 'Zazu could not complete a read-only database check for this workspace. This check did not change your business records.',
            'next_step' => 'Do not reinstall Zazu, restore a backup or run database changes from this screen. Preserve the installation and contact Zazu support for guided help.',
        ];
    }

    private function businessAccessCheck(bool $healthy): array
    {
        return [
            'key' => 'business_access',
            'label' => 'Business access',
            'status' => $healthy ? 'healthy' : 'failed',
            'message' => $healthy
                ? 'This account is an owner of the active business workspace.'
                : 'Zazu could not confirm active owner access to this workspace.',
            'next_step' => $healthy
                ? null
                : 'Sign in with an authorised business-owner account. If access still fails, contact Zazu support before changing workspace membership.',
        ];
    }

    private function businessDataAccessCheck(bool $healthy): array
    {
        return [
            'key' => 'business_data_access',
            'label' => 'Business data access',
            'status' => $healthy ? 'healthy' : 'failed',
            'message' => $healthy
                ? 'Customer, event and service-catalogue data paths for this business responded.'
                : 'Zazu could not verify one or more business data paths. No records were changed by this check.',
            'next_step' => $healthy
                ? null
                : 'Avoid importing, deleting or restoring records until the issue is understood. Contact Zazu support and include the time of this check.',
        ];
    }

    private function notChecked(string $key, string $label, string $message): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'status' => 'not_checked',
            'message' => $message,
        ];
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
