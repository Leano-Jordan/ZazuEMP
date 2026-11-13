<?php

namespace Tests\Feature;

use App\Models\SyncDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BusinessContextSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_without_an_active_business_cannot_enter_workspace(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertForbidden();
    }
    public function test_user_without_a_business_can_still_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('landing'));

        $this->assertGuest();
    }

    public function test_business_switch_is_membership_scoped_and_preserves_the_selected_workspace(): void
    {
        $first = \App\Models\Business::create([
            'name' => 'Switch First',
            'slug' => 'switch-first-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $second = \App\Models\Business::create([
            'name' => 'Switch Second',
            'slug' => 'switch-second-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $foreign = \App\Models\Business::create([
            'name' => 'Switch Foreign',
            'slug' => 'switch-foreign-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = User::factory()->create();
        $first->users()->attach($user->id, ['role' => 'owner']);
        $second->users()->attach($user->id, ['role' => 'staff']);

        $this->actingAs($user)
            ->post(route('business.switch'), ['business_id' => $first->id])
            ->assertRedirect();
        $this->assertSame($first->id, app(\App\Support\CurrentBusiness::class)->id($user));

        $this->post(route('business.switch'), ['business_id' => $second->id])
            ->assertRedirect();
        $this->assertSame($second->id, app(\App\Support\CurrentBusiness::class)->id($user));

        $this->post(route('business.switch'), ['business_id' => $foreign->id])
            ->assertForbidden();
        $this->assertSame($second->id, app(\App\Support\CurrentBusiness::class)->id($user));
    }


    public function test_switching_business_cannot_rebind_a_trusted_device_to_another_business(): void
    {
        $first = \\App\\Models\\Business::create([
            'name' => 'Trusted Device First',
            'slug' => 'trusted-device-first-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $second = \\App\\Models\\Business::create([
            'name' => 'Trusted Device Second',
            'slug' => 'trusted-device-second-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = User::factory()->create();
        $first->users()->attach($user->id, ['role' => 'owner']);
        $second->users()->attach($user->id, ['role' => 'owner']);

        $this->actingAs($user)
            ->post(route('business.switch'), ['business_id' => $first->id])
            ->assertRedirect();

        $installationId = (string) Str::uuid();
        $firstRegistration = $this->postJson(route('offline.device.register'), [
            'installation_id' => $installationId,
            'device_name' => 'Trusted phone',
            'device_type' => 'phone',
        ])->assertOk();

        $firstDeviceId = (int) $firstRegistration->json('device.id');
        $firstTokenHash = hash('sha256', (string) $firstRegistration->json('token'));
        $this->assertSame($first->id, (int) $firstRegistration->json('device.business_id'));

        $this->post(route('business.switch'), ['business_id' => $second->id])
            ->assertRedirect();

        // The installation identifier is trusted by the first business only.
        // A browser in the second business must register its own namespace and
        // cannot silently take ownership of the first business's device token.
        $this->postJson(route('offline.device.register'), [
            'installation_id' => $installationId,
            'device_name' => 'Reused phone',
            'device_type' => 'phone',
        ])->assertConflict();

        $firstDevice = SyncDevice::query()->findOrFail($firstDeviceId);
        $this->assertSame($first->id, $firstDevice->business_id);
        $this->assertSame($firstTokenHash, $firstDevice->metadata['sync_token_hash']);

        $secondRegistration = $this->postJson(route('offline.device.register'), [
            'installation_id' => (string) Str::uuid(),
            'device_name' => 'Second business phone',
            'device_type' => 'phone',
        ])->assertOk();

        $this->assertSame($second->id, (int) $secondRegistration->json('device.business_id'));
        $this->assertNotSame($firstDeviceId, (int) $secondRegistration->json('device.id'));
        $this->assertSame(2, SyncDevice::query()->count());
        $this->assertSame($firstTokenHash, $firstDevice->fresh()->metadata['sync_token_hash']);
    }

}
