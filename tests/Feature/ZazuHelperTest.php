<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Event;
use App\Models\EventPreparationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ZazuHelperTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_workspace_exposes_the_zazu_helper_by_default(): void
    {
        $this->signInAsOwner();

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Zazu guide')
            ->assertSee('Turn guide off')
            ->assertSee('aria-expanded="false"', false)
            ->assertDontSee('data-zazu-helper-toggle aria-expanded="false" aria-pressed', false)
            ->assertSee('data-zazu-guide-enabled="on"', false);
    }

    public function test_public_landing_authentication_modal_does_not_render_the_workspace_helper(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('data-auth-modal', false)
            ->assertDontSee('data-zazu-helper', false);
    }

    public function test_helper_stays_docked_to_the_far_right_instead_of_following_navigation_popovers(): void
    {
        $script = file_get_contents(resource_path('js/app.js'));
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertStringContainsString('position: fixed;', $css);
        $this->assertStringContainsString('right: max(16px, env(safe-area-inset-right));', $css);
        $this->assertStringContainsString('right: max(12px, env(safe-area-inset-right));', $css);
        $this->assertStringContainsString('width: 46px;', $css);
        $this->assertStringContainsString('body.zazu-mobile-menu-open .zazu-helper', $css);
        $this->assertStringNotContainsString('moveHelperForNav', $script);
        $this->assertStringNotContainsString('is-nav-avoiding', $script.$css);
        $this->assertStringNotContainsString('--zazu-helper-nav-right', $script.$css);
    }

    public function test_helper_attention_is_business_scoped_and_ignores_terminal_job_preparation(): void
    {
        $business = $this->makeBusiness('Helper Primary');
        $foreignBusiness = $this->makeBusiness('Helper Foreign');
        $business->forceFill([
            'catalogue_setup_completed_at' => now(),
            'business_setup_completed_at' => now(),
        ])->save();

        $user = User::factory()->create();
        $business->users()->attach($user->id, ['role' => 'owner']);

        $activeJob = Event::create([
            'business_id' => $business->id,
            'reference' => 'HELPER-ACTIVE-'.Str::upper(Str::random(5)),
            'name' => 'Active helper job',
            'status' => 'confirmed',
        ]);
        $cancelledJob = Event::create([
            'business_id' => $business->id,
            'reference' => 'HELPER-CANCELLED-'.Str::upper(Str::random(5)),
            'name' => 'Cancelled helper job',
            'status' => 'cancelled',
        ]);
        Event::create([
            'business_id' => $business->id,
            'reference' => 'HELPER-DRAFT-'.Str::upper(Str::random(5)),
            'name' => 'Draft helper job',
            'status' => 'draft',
        ]);
        $foreignJob = Event::create([
            'business_id' => $foreignBusiness->id,
            'reference' => 'HELPER-FOREIGN-'.Str::upper(Str::random(5)),
            'name' => 'Foreign helper job',
            'status' => 'confirmed',
        ]);

        foreach ([$activeJob, $cancelledJob, $foreignJob] as $index => $job) {
            EventPreparationItem::create([
                'business_id' => $job->business_id,
                'event_id' => $job->id,
                'title' => 'Preparation item '.$index,
                'category' => 'Other',
                'quantity' => '1.00',
                'unit' => 'item',
                'status' => 'open',
                'due_date' => now()->subDay()->toDateString(),
            ]);
        }

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('1 preparation item overdue')
            ->assertSee('1 job still in draft');
    }

    public function test_helper_does_not_expose_work_attention_to_a_member_without_work_view_permission(): void
    {
        config(['zazu.permissions.roles.staff' => ['dashboard.view']]);

        $business = $this->makeBusiness('Helper Restricted');
        $business->forceFill([
            'catalogue_setup_completed_at' => now(),
            'business_setup_completed_at' => now(),
        ])->save();

        $user = User::factory()->create();
        $business->users()->attach($user->id, ['role' => 'staff']);

        $job = Event::create([
            'business_id' => $business->id,
            'reference' => 'HELPER-PRIVATE-'.Str::upper(Str::random(5)),
            'name' => 'Restricted helper job',
            'status' => 'draft',
        ]);
        EventPreparationItem::create([
            'business_id' => $business->id,
            'event_id' => $job->id,
            'title' => 'Private overdue preparation',
            'category' => 'Other',
            'quantity' => '1.00',
            'unit' => 'item',
            'status' => 'open',
            'due_date' => now()->subDay()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('preparation item overdue')
            ->assertDontSee('job still in draft');
    }

    private function makeBusiness(string $name): Business
    {
        return Business::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
    }
}
