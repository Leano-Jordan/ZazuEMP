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

        $founderBusiness = Business::query()->find($keepBusinessId);
        if (!$founderBusiness || $founderBusiness->name !== 'Rosscore Labs (Pty) Ltd') {
            $this->error('Safety check failed: the protected business ID does not resolve to Rosscore Labs (Pty) Ltd. Nothing was changed.');

            return self::FAILURE;
        }

        $founderOwnsBusiness = DB::table('business_user')
            ->where('business_id', $keepBusinessId)
            ->where('user_id', $keepUserId)
            ->where('role', 'owner')
            ->exists();

        if (!$founderOwnsBusiness) {
            $this->error('Safety check failed: the protected founder user is not an owner of the protected business. Nothing was changed.');

            return self::FAILURE;
        }

        $businesses = Business::query()
            ->whereKeyNot($keepBusinessId)
            ->with(['users:id,name,email'])
            ->withCount(['customers', 'events'])
            ->orderBy('id')
            ->get();

        if ($businesses->isEmpty()) {
            $this->info('No non-founder business workspaces found. Nothing to remove.');

            return self::SUCCESS;
        }

        $candidateUserIds = $businesses
            ->flatMap(fn (Business $business) => $business->users->pluck('id'))
            ->unique()
            ->reject(fn ($id) => (int) $id === $keepUserId)
            ->values();

        $this->warn('NON-FOUNDER WORKSPACES MARKED FOR REMOVAL');
        $this->line('Protected: '.$founderBusiness->name.' (business '.$keepBusinessId.') and founder user '.$keepUserId);
        $this->newLine();

        foreach ($businesses as $business) {
            $owners = $business->users
                ->filter(fn (User $user) => DB::table('business_user')
                    ->where('business_id', $business->id)
                    ->where('user_id', $user->id)
                    ->where('role', 'owner')
                    ->exists())
                ->map(fn (User $user) => $user->name.' <'.$user->email.'>')
                ->implode(', ');

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

        $orphanedUsers = User::query()
            ->whereIn('id', $candidateUserIds)
            ->whereDoesntHave('businesses')
            ->whereKeyNot($keepUserId)
            ->get(['id', 'name', 'email']);

        $this->newLine();
        $this->line('Candidate linked users: '.$candidateUserIds->count());
        $this->line('Users that would become orphaned and be deleted: '.$orphanedUsers->count());

        if (!$this->option('execute')) {
            $this->comment('DRY RUN ONLY. No records were changed.');
            $this->comment('After reviewing the list and taking a database backup, execute with:');
            $this->line('php artisan zazu:purge-non-founder-workspaces --execute --confirm=DELETE-NON-FOUNDER-WORKSPACES');

            return self::SUCCESS;
        }

        if ($this->option('confirm') !== 'DELETE-NON-FOUNDER-WORKSPACES') {
            $this->error('Execution refused. Supply the exact --confirm phrase. No records were changed.');

            return self::FAILURE;
        }

        try {
            $deletedBusinesses = 0;
            $deletedUsers = 0;

            DB::transaction(function () use ($businesses, $candidateUserIds, $keepUserId, &$deletedBusinesses, &$deletedUsers): void {
                foreach ($businesses as $business) {
                    $business->delete();
                    $deletedBusinesses++;
                }

                $deletedUsers = User::query()
                    ->whereIn('id', $candidateUserIds)
                    ->whereDoesntHave('businesses')
                    ->whereKeyNot($keepUserId)
                    ->delete();
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
