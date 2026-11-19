<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\FounderSupportSession;
use App\Models\PlatformAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FounderSupportAccessTest extends TestCase
{
    use RefreshDatabase;

    private function founder(): User
    {
        config(['zazu.platform_admin_email' => 'founder@example.test']);

        return User::factory()->create(['email' => 'founder@example.test']);
    }

    private function business(): Business
    {
        $owner = User::factory()->create(['email' => 'owner@example.test']);
        $business = Business::create([
            'name' => 'Support Test Catering',
            'slug' => 'support-test-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $business->users()->attach($owner, ['role' => 'owner']);

        return $business;
    }

    public function test_founder_can_start_view_and_end_audited_read_only_support_session(): void
    {
        $founder = $this->founder();
        $business = $this->business();

        $response = $this->actingAs($founder)->post(
            route('admin.sentinel.support.start', $business),
            ['reason' => 'Investigating a customer-reported account issue.']
        );

        $supportSession = FounderSupportSession::query()->firstOrFail();

        $response->assertRedirect(route('admin.sentinel.support.show', $supportSession));
        $this->assertSame($founder->id, $supportSession->actor_user_id);
        $this->assertSame($business->id, $supportSession->business_id);
        $this->assertTrue($supportSession->expires_at->equalTo($supportSession->started_at->copy()->addMinutes(15)));
        $this->assertDatabaseHas('platform_audit_logs', [
            'founder_support_session_id' => $supportSession->id,
            'action' => 'founder.support.started',
            'outcome' => 'success',
        ]);

        $this->get(route('admin.sentinel.support.show', $supportSession))
            ->assertOk()
            ->assertSee('READ-ONLY FOUNDER SUPPORT ACCESS')
            ->assertSee('Support Test Catering')
            ->assertSee('No customer records.')
            ->assertSee('No event records.');

        $this->assertDatabaseHas('platform_audit_logs', [
            'founder_support_session_id' => $supportSession->id,
            'action' => 'founder.support.viewed',
            'outcome' => 'success',
        ]);

        $this->post(route('admin.sentinel.support.end', $supportSession))
            ->assertRedirect(route('admin.sentinel.businesses.show', $business));

        $this->assertNotNull($supportSession->fresh()->ended_at);
        $this->assertDatabaseHas('platform_audit_logs', [
            'founder_support_session_id' => $supportSession->id,
            'action' => 'founder.support.ended',
            'outcome' => 'success',
        ]);

        $this->get(route('admin.sentinel.support.show', $supportSession))
            ->assertForbidden();
    }

    public function test_support_session_requires_reason_and_rejects_expired_sessions(): void
    {
        $founder = $this->founder();
        $business = $this->business();

        $this->actingAs($founder)
            ->from(route('admin.sentinel.businesses.show', $business))
            ->post(route('admin.sentinel.support.start', $business), ['reason' => 'short'])
            ->assertSessionHasErrors('reason');

        $supportSession = FounderSupportSession::query()->create([
            'actor_user_id' => $founder->id,
            'business_id' => $business->id,
            'reason' => 'Expired session test reason.',
            'started_at' => now()->subMinutes(20),
            'expires_at' => now()->subMinutes(5),
        ]);

        $this->withSession(['zazu_founder_support_session_id' => $supportSession->id])
            ->get(route('admin.sentinel.support.show', $supportSession))
            ->assertForbidden();
    }

    public function test_non_founder_cannot_start_or_view_founder_support_access(): void
    {
        $this->founder();
        $business = $this->business();
        $ordinaryUser = User::factory()->create(['email' => 'ordinary@example.test']);

        $this->actingAs($ordinaryUser)
            ->post(route('admin.sentinel.support.start', $business), [
                'reason' => 'Attempted unauthorised support access.',
            ])
            ->assertForbidden();

        $supportSession = FounderSupportSession::query()->create([
            'actor_user_id' => $ordinaryUser->id,
            'business_id' => $business->id,
            'reason' => 'Unauthorised session fixture.',
            'started_at' => now(),
            'expires_at' => now()->addMinutes(15),
        ]);

        $this->actingAs($ordinaryUser)
            ->withSession(['zazu_founder_support_session_id' => $supportSession->id])
            ->get(route('admin.sentinel.support.show', $supportSession))
            ->assertForbidden();
    }

    public function test_starting_a_new_support_session_ends_the_previous_browser_session(): void
    {
        $founder = $this->founder();
        $firstBusiness = $this->business();
        $secondBusiness = $this->business();

        $this->actingAs($founder)->post(
            route('admin.sentinel.support.start', $firstBusiness),
            ['reason' => 'First business support investigation.']
        );

        $firstSession = FounderSupportSession::query()->firstOrFail();

        $this->post(
            route('admin.sentinel.support.start', $secondBusiness),
            ['reason' => 'Second business support investigation.']
        );

        $this->assertNotNull($firstSession->fresh()->ended_at);
        $this->assertDatabaseHas('platform_audit_logs', [
            'founder_support_session_id' => $firstSession->id,
            'action' => 'founder.support.superseded',
            'outcome' => 'success',
        ]);

        $secondSession = FounderSupportSession::query()->latest('id')->firstOrFail();

        $this->get(route('admin.sentinel.support.show', $firstSession))
            ->assertForbidden();

        $this->get(route('admin.sentinel.support.show', $secondSession))
            ->assertOk();
    }

    public function test_founder_cannot_end_another_founders_support_session(): void
    {
        $this->founder();
        $business = $this->business();
        $otherFounder = User::factory()->create(['email' => 'other@example.test']);
        $supportSession = FounderSupportSession::query()->create([
            'actor_user_id' => $otherFounder->id,
            'business_id' => $business->id,
            'reason' => 'Ownership boundary test.',
            'started_at' => now(),
            'expires_at' => now()->addMinutes(15),
        ]);

        $this->actingAs(User::factory()->create(['email' => 'founder@example.test']))
            ->post(route('admin.sentinel.support.end', $supportSession))
            ->assertForbidden();

        $this->assertNull($supportSession->fresh()->ended_at);
        $this->assertSame(0, PlatformAuditLog::query()->count());
    }
}
