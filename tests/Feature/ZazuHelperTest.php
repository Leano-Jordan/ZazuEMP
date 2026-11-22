<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessCapability;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventRequirement;
use App\Models\EventPreparationItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
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
            ->assertSee('data-zazu-guide-enabled="on"', false)
            ->assertSee('data-zazu-helper-recommendation', false)
            ->assertSee('data-zazu-helper-connectivity', false);
    }

    public function test_helper_guides_resolve_real_semantic_targets_on_core_workspaces(): void
    {
        $this->signInAsOwner();

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-zazu-anchor="dashboard.overview"', false)
            ->assertSee('data-target="dashboard.overview"', false);

        $this->get(route('work.index'))
            ->assertOk()
            ->assertSee('data-zazu-anchor="work.register"', false)
            ->assertSee('data-target="work.register"', false);

        $this->get(route('quotes.index'))
            ->assertOk()
            ->assertSee('data-zazu-anchor="quotes.register"', false)
            ->assertSee('data-target="quotes.register"', false);

        $customer = Customer::create(['name' => 'Helper Job Customer']);
        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'HELPER-GUIDE-'.Str::upper(Str::random(5)),
            'name' => 'Helper Guide Job',
            'event_date' => '2026-12-20',
            'status' => 'draft',
        ]);

        $this->get(route('work.show', $event))
            ->assertOk()
            ->assertSee('data-zazu-anchor="work.next-action"', false)
            ->assertSee('data-target="work.next-action"', false)
            ->assertSee('data-zazu-anchor="work.progress"', false)
            ->assertSee('data-target="work.progress"', false);
    }

    public function test_helper_uses_safe_storage_and_reduced_motion_aware_semantic_highlighting(): void
    {
        $script = file_get_contents(resource_path('js/app.js'));
        $css = file_get_contents(resource_path('css/zazu-final-visual-sweep.css'));

        $this->assertStringContainsString('safeStorageGet(storageKey)', $script);
        $this->assertStringContainsString("safeStorageSet(storageKey, 'off')", $script);
        $this->assertStringContainsString('[data-zazu-anchor]', $script);
        $this->assertStringContainsString('resolveVisibleTarget', $script);
        $this->assertStringContainsString("element.closest('[hidden], [aria-hidden=\"true\"]')", $script);
        $this->assertStringContainsString('target.isConnected', $script);
        $this->assertStringContainsString("toggle.setAttribute('aria-label', 'Open Zazu guide')", $script);
        $this->assertStringContainsString('closeButton?.focus()', $script);
        $this->assertStringContainsString('prefers-reduced-motion', $script);
        $this->assertStringContainsString('.zazu-helper-target-highlight', $css);
        $this->assertStringContainsString("window.addEventListener('zazu:origin-availability'", $script);
        $this->assertStringContainsString('originAvailable === true', $script);
        $this->assertStringContainsString('data-zazu-helper-recommendation', $script);
        $this->assertStringContainsString('Connection to Zazu is unavailable', $script);
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

        $localSupplier = Supplier::create([
            'business_id' => $business->id,
            'name' => 'Local helper supplier',
        ]);
        $foreignSupplier = Supplier::create([
            'business_id' => $foreignBusiness->id,
            'name' => 'Foreign helper supplier',
        ]);
        PurchaseOrder::create([
            'business_id' => $business->id,
            'supplier_id' => $localSupplier->id,
            'reference' => 'HELPER-PO-LOCAL-'.Str::upper(Str::random(5)),
            'status' => 'sent',
            'currency' => 'ZAR',
            'total_amount' => '100.00',
        ]);
        PurchaseOrder::create([
            'business_id' => $foreignBusiness->id,
            'supplier_id' => $foreignSupplier->id,
            'reference' => 'HELPER-PO-FOREIGN-'.Str::upper(Str::random(5)),
            'status' => 'ordered',
            'currency' => 'ZAR',
            'total_amount' => '200.00',
        ]);
        PurchaseOrder::create([
            'business_id' => $business->id,
            'supplier_id' => $localSupplier->id,
            'reference' => 'HELPER-PO-CLOSED-'.Str::upper(Str::random(5)),
            'status' => 'received',
            'currency' => 'ZAR',
            'total_amount' => '50.00',
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
            ->assertSee('1 purchase order still open')
            ->assertSee('1 job still in draft')
            ->assertDontSee('2 purchase orders still open')
            ->assertSee('data-zazu-helper-attention-item', false)
            ->assertSee('data-zazu-helper-attention-title', false)
            ->assertSee('data-zazu-helper-attention-copy', false)
            ->assertSee('data-zazu-helper-attention-link', false);
    }


    public function test_helper_prioritises_real_operational_risks_before_setup_reminders(): void
    {
        $business = $this->makeBusiness('Helper Priority Business');
        $user = User::factory()->create();
        $business->users()->attach($user->id, ['role' => 'owner']);

        $overdueEvent = Event::create([
            'business_id' => $business->id,
            'reference' => 'HELPER-PRIORITY-'.Str::upper(Str::random(5)),
            'name' => 'Overdue operational job',
            'status' => 'confirmed',
        ]);
        EventPreparationItem::create([
            'business_id' => $business->id,
            'event_id' => $overdueEvent->id,
            'title' => 'Missed preparation deadline',
            'category' => 'Equipment',
            'quantity' => '1.00',
            'unit' => 'set',
            'status' => 'open',
            'due_date' => now()->subDay()->toDateString(),
        ]);

        $supplier = Supplier::create([
            'business_id' => $business->id,
            'name' => 'Priority supplier',
        ]);
        PurchaseOrder::create([
            'business_id' => $business->id,
            'supplier_id' => $supplier->id,
            'reference' => 'HELPER-PRIORITY-PO-'.Str::upper(Str::random(5)),
            'status' => 'ordered',
            'currency' => 'ZAR',
            'total_amount' => '400.00',
        ]);
        Event::create([
            'business_id' => $business->id,
            'reference' => 'HELPER-PRIORITY-DRAFT-'.Str::upper(Str::random(5)),
            'name' => 'Draft operational job',
            'status' => 'draft',
        ]);

        $html = $this->actingAs($user)->get(route('dashboard'))->assertOk()->getContent();
        $overdue = strpos($html, 'data-priority="10"');
        $purchasing = strpos($html, 'data-priority="20"');
        $draft = strpos($html, 'data-priority="30"');
        $setup = strpos($html, 'data-priority="40"');

        $this->assertNotFalse($overdue);
        $this->assertNotFalse($purchasing);
        $this->assertNotFalse($draft);
        $this->assertNotFalse($setup);
        $this->assertLessThan($purchasing, $overdue);
        $this->assertLessThan($draft, $purchasing);
        $this->assertLessThan($setup, $draft);
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


    public function test_helper_exposes_purchasing_attention_only_when_the_member_has_purchasing_view_permission(): void
    {
        config(['zazu.permissions.roles.staff' => ['dashboard.view', 'purchasing.view']]);

        $business = $this->makeBusiness('Helper Purchasing Access');
        $business->forceFill([
            'catalogue_setup_completed_at' => now(),
            'business_setup_completed_at' => now(),
        ])->save();

        $user = User::factory()->create();
        $business->users()->attach($user->id, ['role' => 'staff']);
        $supplier = Supplier::create([
            'business_id' => $business->id,
            'name' => 'Permitted helper supplier',
        ]);
        PurchaseOrder::create([
            'business_id' => $business->id,
            'supplier_id' => $supplier->id,
            'reference' => 'HELPER-PO-PERMISSION-'.Str::upper(Str::random(5)),
            'status' => 'ordered',
            'currency' => 'ZAR',
            'total_amount' => '125.00',
        ]);
        Event::create([
            'business_id' => $business->id,
            'reference' => 'HELPER-WORK-RESTRICTED-'.Str::upper(Str::random(5)),
            'name' => 'Restricted work item',
            'status' => 'draft',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('1 purchase order still open')
            ->assertDontSee('job still in draft')
            ->assertDontSee('preparation item overdue');
    }

    public function test_helper_hides_linked_booking_record_counts_without_their_workspace_permission(): void
    {
        config(['zazu.permissions.roles.staff' => ['dashboard.view', 'work.view']]);

        $business = $this->makeBusiness('Helper Record Permissions');
        $user = User::factory()->create();
        $business->users()->attach($user->id, ['role' => 'staff']);

        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'HELPER-PERMISSION-'.Str::upper(Str::random(5)),
            'name' => 'Restricted booking records',
            'status' => 'confirmed',
        ]);
        EventPreparationItem::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'title' => 'Prepare equipment',
            'category' => 'Equipment',
            'quantity' => '1.00',
            'unit' => 'set',
            'status' => 'open',
        ]);
        $supplier = Supplier::create([
            'business_id' => $business->id,
            'name' => 'Restricted supplier',
        ]);
        PurchaseOrder::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'supplier_id' => $supplier->id,
            'reference' => 'HELPER-RESTRICTED-PO-'.Str::upper(Str::random(5)),
            'status' => 'sent',
            'currency' => 'ZAR',
            'total_amount' => '350.00',
        ]);

        $this->actingAs($user)
            ->get(route('work.show', $event))
            ->assertOk()
            ->assertSee('data-zazu-helper-event-records="1"', false)
            ->assertSee('1 preparation item')
            ->assertDontSee('purchase order');
    }

    public function test_helper_guidance_adapts_to_the_saved_business_focus(): void
    {
        $cases = [
            'chairs_tents' => 'Keep hire quantities, availability, delivery, setup, collection and asset condition connected to each booking.',
            'sound_dj' => 'Keep sound equipment, crew, setup/breakdown, programme timing and transport connected to each booking.',
            'catering_baking' => 'Keep menus, quantities, dietary details, purchasing, preparation and delivery connected to each booking.',
        ];

        foreach ($cases as $focus => $expectedGuidance) {
            $business = $this->makeBusiness('Helper Focus '.Str::random(6));
            $user = User::factory()->create();
            $business->users()->attach($user->id, [
                'role' => 'owner',
                'primary_niche' => $focus,
                'experience_level' => 'intermediate',
            ]);

            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertSee('data-zazu-helper-focus="'.$focus.'"', false)
                ->assertSee($expectedGuidance);
        }
    }


    public function test_helper_combines_primary_focus_with_multiple_active_catalogue_capabilities(): void
    {
        $business = $this->makeBusiness('Helper Multi Service Business');
        $user = User::factory()->create();
        $business->users()->attach($user->id, [
            'role' => 'owner',
            'primary_niche' => 'sound_dj',
        ]);

        BusinessCapability::create([
            'business_id' => $business->id,
            'name' => 'White chair hire',
            'category' => 'Furniture & equipment',
            'capability_type' => 'rental',
            'pricing_basis' => 'per_unit',
            'default_unit' => 'chair',
            'currency' => 'ZAR',
            'is_active' => true,
        ]);
        BusinessCapability::create([
            'business_id' => $business->id,
            'name' => 'Buffet catering',
            'category' => 'Catering',
            'capability_type' => 'service',
            'pricing_basis' => 'per_person',
            'default_unit' => 'person',
            'currency' => 'ZAR',
            'is_active' => true,
        ]);
        BusinessCapability::create([
            'business_id' => $business->id,
            'name' => 'Archived cake service',
            'category' => 'Baking',
            'capability_type' => 'service',
            'pricing_basis' => 'custom',
            'currency' => 'ZAR',
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-zazu-helper-focus="sound_dj"', false)
            ->assertSee('data-zazu-helper-focuses="sound_dj,chairs_tents,catering_baking"', false)
            ->assertSee('For your active service mix:')
            ->assertSee('Keep sound equipment, crew, setup/breakdown, programme timing and transport connected to each booking.')
            ->assertSee('Keep hire quantities, availability, delivery, setup, collection and asset condition connected to each booking.')
            ->assertSee('Keep menus, quantities, dietary details, purchasing, preparation and delivery connected to each booking.')
            ->assertDontSee('Archived cake service')
            ->assertDontSee('Camera editing');
    }

    public function test_helper_shows_only_the_current_jobs_saved_components(): void
    {
        $business = $this->makeBusiness('Helper Event Scope');
        $user = User::factory()->create();
        $business->users()->attach($user->id, ['role' => 'owner']);
        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'HELPER-SCOPE-'.Str::upper(Str::random(5)),
            'name' => 'Tent and chair booking',
            'status' => 'confirmed',
        ]);
        EventRequirement::create([
            'event_id' => $event->id,
            'description' => '100 white chairs',
            'category' => 'Furniture & equipment',
            'quantity' => '100.00',
            'unit' => 'chair',
            'status' => 'open',
        ]);
        EventRequirement::create([
            'event_id' => $event->id,
            'description' => 'Two stretch tents',
            'category' => 'Rentals',
            'quantity' => '2.00',
            'unit' => 'unit',
            'status' => 'open',
        ]);
        EventPreparationItem::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'title' => 'Prepare tent hardware',
            'category' => 'Equipment',
            'quantity' => '2.00',
            'unit' => 'set',
            'status' => 'open',
        ]);
        $supplier = Supplier::create([
            'business_id' => $business->id,
            'name' => 'Event component supplier',
        ]);
        PurchaseOrder::create([
            'business_id' => $business->id,
            'event_id' => $event->id,
            'supplier_id' => $supplier->id,
            'reference' => 'HELPER-EVENT-PO-'.Str::upper(Str::random(5)),
            'status' => 'sent',
            'currency' => 'ZAR',
            'total_amount' => '250.00',
        ]);

        $this->actingAs($user)
            ->get(route('work.show', $event))
            ->assertOk()
            ->assertSee('data-zazu-helper-event-components="2"', false)
            ->assertSee('100 white chairs')
            ->assertSee('Two stretch tents')
            ->assertSee('data-zazu-helper-focuses="chairs_tents"', false)
            ->assertSee('For this booking, review hire quantities, asset availability, delivery, setup, collection and condition.')
            ->assertSee('data-zazu-helper-event-records="2"', false)
            ->assertSee('1 preparation item')
            ->assertSee('1 purchase order')
            ->assertDontSee('Catering preparation')
            ->assertDontSee('Camera editing');
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
