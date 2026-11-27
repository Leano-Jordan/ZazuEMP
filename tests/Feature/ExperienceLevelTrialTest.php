<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\SyncDevice;
use App\Models\User;
use App\Support\ExperienceLevel;
use App\Support\NicheFocus;
use App\Support\Offline\SyncMutationPushProcessor;
use App\Support\Offline\SyncMutationRecorder;
use App\Support\Offline\SyncMutationApplier;
use App\Support\Offline\SyncConflictRecorder;
use App\Support\Offline\SyncEntityIdentityRegistry;
use App\Support\Offline\SyncMutationProtocol;
use App\Support\Offline\OfflineDomainMutationHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExperienceLevelTrialTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_workspace_receives_an_advanced_trial_for_exactly_thirty_days(): void
    {
        $business = Business::create([
            'name' => 'Trial Business',
            'slug' => 'trial-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $createdAt = Carbon::parse('2026-11-01 09:00:00', 'UTC');
        $business->forceFill(['created_at' => $createdAt])->saveQuietly();

        $levels = app(ExperienceLevel::class);
        $this->assertSame('advanced', $levels->maximumFor($business, $createdAt->copy()->addDays(29)->endOfDay()));
        $this->assertTrue($levels->isTrial($business, $createdAt->copy()->addDays(29)));
        $this->assertSame('2026-12-01 09:00:00', $levels->trialEndsAt($business)?->format('Y-m-d H:i:s'));
        $this->assertSame('basic', $levels->maximumFor($business, $createdAt->copy()->addDays(30)));
        $this->assertFalse($levels->isTrial($business, $createdAt->copy()->addDays(30)));
    }

    public function test_expired_trial_clamps_presentation_without_overwriting_saved_preference(): void
    {
        $business = Business::create([
            'name' => 'Expired Trial Business',
            'slug' => 'expired-trial-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $business->forceFill(['created_at' => Carbon::parse('2026-10-01 09:00:00', 'UTC')])->saveQuietly();

        $user = User::factory()->create();
        $business->users()->attach($user->id, [
            'role' => 'owner',
            'experience_level' => 'advanced',
            'primary_niche' => NicheFocus::DEFAULT,
        ]);

        $levels = app(ExperienceLevel::class);
        $this->assertSame('advanced', $levels->selected($user, $business));
        $this->assertSame('basic', $levels->for($user, $business));
        $this->assertSame('advanced', $levels->selected($user, $business));
        $this->assertSame(['basic'], $levels->availableLevels($business));
    }

    public function test_setup_centre_displays_effective_level_after_trial_expiry_without_rewriting_preference(): void
    {
        $business = Business::create([
            'name' => 'Expired Setup Business',
            'slug' => 'expired-setup-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $business->forceFill(['created_at' => Carbon::parse('2026-10-01 09:00:00', 'UTC')])->saveQuietly();

        $user = User::factory()->create();
        $business->users()->attach($user->id, [
            'role' => 'owner',
            'experience_level' => 'advanced',
            'primary_niche' => NicheFocus::DEFAULT,
        ]);

        $this->actingAs($user)
            ->get(route('onboarding.index'))
            ->assertOk()
            ->assertSee('Basic')
            ->assertDontSee('Advanced.');

        $this->assertSame(
            'advanced',
            $user->businesses()->whereKey($business->id)->first()->pivot->experience_level
        );
    }

    public function test_queued_offline_work_reconnects_and_is_delivered_after_trial_expiry(): void
    {
        $createdAt = Carbon::parse('2026-11-01 09:00:00', 'UTC');
        $business = Business::create([
            'name' => 'Queued Downgrade Business',
            'slug' => 'queued-downgrade-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $business->forceFill(['created_at' => $createdAt])->saveQuietly();

        $user = User::factory()->create();
        $business->users()->attach($user->id, [
            'role' => 'owner',
            'experience_level' => 'advanced',
            'primary_niche' => NicheFocus::DEFAULT,
        ]);

        $sourceDevice = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $receivingDevice = SyncDevice::create([
            'business_id' => $business->id,
            'installation_id' => (string) Str::uuid(),
        ]);
        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => 'Before offline edit',
        ]);
        $identity = app(SyncEntityIdentityRegistry::class)->identify($customer, 'customer');

        try {
            Carbon::setTestNow($createdAt->copy()->addDays(29));
            $mutation = app(SyncMutationRecorder::class)->record(
                $sourceDevice,
                'customer',
                $identity->entity_uuid,
                'update',
                [
                    'local_id' => $identity->entity_uuid,
                    'expected_revision' => 1,
                    'record' => ['name' => 'Offline edit queued before expiry'],
                ],
            );

            $this->assertSame('pending', $mutation->status);

            Carbon::setTestNow($createdAt->copy()->addDays(30));
            $levels = app(ExperienceLevel::class);
            $this->assertSame('basic', $levels->for($user, $business));
            $this->assertSame('advanced', $levels->selected($user, $business));

            // Simulate the phone reconnecting after the trial has expired.
            // Sync processing is authorised by business/device credentials, not
            // by the member's presentation preference or commercial tier.
            $processor = new SyncMutationPushProcessor(
                app(SyncMutationRecorder::class),
                app(SyncMutationApplier::class),
                app(OfflineDomainMutationHandler::class),
                app(SyncConflictRecorder::class),
            );
            $result = $processor->process($sourceDevice->fresh(), [
                'id' => $mutation->mutation_id,
                'entity_type' => $mutation->entity_type,
                'entity_id' => $mutation->entity_id,
                'operation' => $mutation->operation,
                'payload' => $mutation->payload,
            ]);

            $this->assertSame('applied', $result['status']);
            $this->assertTrue($result['applied']);
            $this->assertSame('Offline edit queued before expiry', $customer->fresh()->name);
            $this->assertSame('applied', $mutation->fresh()->status);

            $protocol = app(SyncMutationProtocol::class);
            $deliveries = $protocol->pull($receivingDevice->fresh());
            $this->assertCount(1, $deliveries);
            $this->assertSame($mutation->id, $deliveries->first()->sync_mutation_id);
            $protocol->acknowledgeThrough($receivingDevice->fresh(), (int) $deliveries->first()->delivery_sequence);
            $this->assertDatabaseHas('sync_deliveries', [
                'sync_mutation_id' => $mutation->id,
                'destination_device_id' => $receivingDevice->id,
                'status' => 'applied',
            ]);
        } finally {
            Carbon::setTestNow();
        }
    }
}
