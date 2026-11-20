<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

final class PurgeNonFounderWorkspaces extends Command
{
    protected $signature = 'zazu:purge-non-founder-workspaces
        {--keep-business=24 : Founder business ID to preserve}
        {--keep-user=27 : Founder user ID to preserve}
        {--execute : Perform the deletion; without this option the command is a dry run}
        {--confirm= : Required confirmation phrase when executing}';

    protected $description = 'Remove non-founder business workspaces and their now-orphaned accounts after review';

    public function handle(): int
    {
        $keepBusinessId = (int) $this->option('keep-business');
        $keepUserId = (int) $this->option('keep-user');

        if (! $this->founderWorkspaceIsValid($keepBusinessId, $keepUserId)) {
            return self::FAILURE;
        }

        $businesses = $this->nonFounderBusinesses($keepBusinessId);

        if ($businesses->isEmpty()) {
            $this->info('No non-founder business workspaces found. Nothing to remove.');

            return self::SUCCESS;
        }

        $candidateUserIds = $this->candidateUserIds($businesses, $keepUserId);
        $this->displayCandidateBusinesses($businesses, $keepBusinessId, $keepUserId);
        $orphanedUsers = $this->orphanedUsers($candidateUserIds, $keepUserId);
        $this->displayCandidateCounts($candidateUserIds->count(), $orphanedUsers->count());

        if (! $this->option('execute')) {
            return $this->showDryRun();
        }

        if (! $this->hasExecutionConfirmation()) {
            return self::FAILURE;
        }

        return $this->purge($businesses, $candidateUserIds, $keepUserId);
    }

    private function founderWorkspaceIsValid(int $businessId, int $userId): bool
    {
        $business = Business::query()->find($businessId);

        if (! $business || $business->name !== 'Rosscore Labs (Pty) Ltd') {
            $this->error('Safety check failed: the protected business ID does not resolve to Rosscore Labs (Pty) Ltd. Nothing was changed.');

            return false;
        }

        $founderOwnsBusiness = DB::table('business_user')
            ->where('business_id', $businessId)
            ->where('user_id', $userId)
            ->where('role', 'owner')
            ->exists();

        if ($founderOwnsBusiness) {
            return true;
        }

        $this->error('Safety check failed: the protected founder user is not an owner of the protected business. Nothing was changed.');

        return false;
    }

    private function nonFounderBusinesses(int $keepBusinessId)
    {
        return Business::query()
            ->where('id', '!=', $keepBusinessId)
            ->with(['users:id,name,email'])
            ->withCount(['customers', 'events'])
            ->orderBy('id')
            ->get();
    }

    private function candidateUserIds($businesses, int $keepUserId)
    {
        return $businesses
            ->flatMap(fn (Business $business) => $business->users->pluck('id'))
            ->unique()
            ->reject(fn ($id) => (int) $id === $keepUserId)
            ->values();
    }

    private function displayCandidateBusinesses($businesses, int $keepBusinessId, int $keepUserId): void
    {
        $this->warn('NON-FOUNDER WORKSPACES MARKED FOR REMOVAL');
        $this->line('Protected: Rosscore Labs (Pty) Ltd (business '.$keepBusinessId.') and founder user '.$keepUserId);
        $this->newLine();

        foreach ($businesses as $business) {
            $owners = $this->businessOwners($business);
            $this->line(sprintf(
                'Business #%d | %s | created %s | owners: %s | customers: %d | events: %d',
                $business->id,
                $business->name,
                $business->created_at?->format('Y-m-d H:i:s') ?? 'unknown',
                $owners !== '' ? $owners : 'NO OWNER',
                $business->customers_count,
                $business->events_count
            ));
        }
    }

    private function businessOwners(Business $business): string
    {
        return $business->users
            ->filter(fn (User $user) => DB::table('business_user')
                ->where('business_id', $business->id)
                ->where('user_id', $user->id)
                ->where('role', 'owner')
                ->exists())
            ->map(fn (User $user) => $user->name.' <'.$user->email.'>')
            ->implode(', ');
    }

    private function orphanedUsers($candidateUserIds, int $keepUserId)
    {
        return User::query()
            ->whereIn('id', $candidateUserIds)
            ->whereDoesntHave('businesses')
            ->where('id', '!=', $keepUserId)
            ->get(['id', 'name', 'email']);
    }

    private function displayCandidateCounts(int $candidateUserCount, int $orphanedUserCount): void
    {
        $this->newLine();
        $this->line('Candidate linked users: '.$candidateUserCount);
        $this->line('Users that would become orphaned and be deleted: '.$orphanedUserCount);
    }

    private function showDryRun(): int
    {
        $this->comment('DRY RUN ONLY. No records were changed.');
        $this->comment('After reviewing the list and taking a database backup, execute with:');
        $this->line('php artisan zazu:purge-non-founder-workspaces --execute --confirm=DELETE-NON-FOUNDER-WORKSPACES');

        return self::SUCCESS;
    }

    private function hasExecutionConfirmation(): bool
    {
        if ($this->option('confirm') === 'DELETE-NON-FOUNDER-WORKSPACES') {
            return true;
        }

        $this->error('Execution refused. Supply the exact --confirm phrase. No records were changed.');

        return false;
    }

    private function purge($businesses, $candidateUserIds, int $keepUserId): int
    {
        try {
            [$deletedBusinesses, $deletedUsers] = DB::transaction(function () use (
                $businesses,
                $candidateUserIds,
                $keepUserId
            ): array {
                foreach ($businesses as $business) {
                    $business->delete();
                }

                $deletedUsers = User::query()
                    ->whereIn('id', $candidateUserIds)
                    ->whereDoesntHave('businesses')
                    ->where('id', '!=', $keepUserId)
                    ->delete();

                return [$businesses->count(), $deletedUsers];
            });

            $this->info("Removed {$deletedBusinesses} non-founder business workspaces and {$deletedUsers} now-orphaned user accounts.");
            $this->info('Founder business and founder user were preserved.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Purge failed and the database transaction was rolled back. Review the application log before retrying.');

            return self::FAILURE;
        }
    }
}
