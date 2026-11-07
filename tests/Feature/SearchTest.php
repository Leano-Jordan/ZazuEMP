<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_only_records_from_the_active_business(): void
    {
        $first = Business::create([
            'name' => 'Search First',
            'slug' => 'search-first-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $second = Business::create([
            'name' => 'Search Second',
            'slug' => 'search-second-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = User::factory()->create();
        $first->users()->attach($user->id, ['role' => 'owner']);

        Customer::create(['business_id' => $first->id, 'name' => 'Visible Search Customer']);
        Customer::create(['business_id' => $second->id, 'name' => 'Hidden Search Customer']);

        $this->actingAs($user);

        $this->get(route('search.index', ['q' => 'Search Customer']))
            ->assertOk()
            ->assertSee('Visible Search Customer')
            ->assertDontSee('Hidden Search Customer');
    }

    public function test_search_does_not_apply_nonexistent_status_columns_to_customers(): void
    {
        $business = Business::create([
            'name' => 'Status Filter Business',
            'slug' => 'status-filter-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = User::factory()->create();
        $business->users()->attach($user->id, ['role' => 'owner']);

        Customer::create(['business_id' => $business->id, 'name' => 'Status Safe Customer']);

        $this->actingAs($user);

        $response = $this->get(route('search.index', [
            'q' => 'Status Safe Customer',
            'type' => 'customer',
            'status' => 'paid',
        ]));

        $response->assertOk()
            ->assertSee('0 records')
            ->assertViewHas('results', static fn (array $results): bool => $results === []);
    }


    public function test_document_search_finds_job_files_and_keeps_results_inside_the_active_business(): void
    {
        $first = Business::create([
            'name' => 'Document Search First',
            'slug' => 'document-search-first-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $second = Business::create([
            'name' => 'Document Search Second',
            'slug' => 'document-search-second-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = User::factory()->create();
        $first->users()->attach($user->id, ['role' => 'owner']);

        $event = Event::create([
            'business_id' => $first->id,
            'reference' => 'DOC-'.Str::upper(Str::random(8)),
            'name' => 'Riverside Wedding',
            'status' => 'draft',
        ]);
        $foreignEvent = Event::create([
            'business_id' => $second->id,
            'reference' => 'DOC-'.Str::upper(Str::random(8)),
            'name' => 'Foreign Showcase',
            'status' => 'draft',
        ]);

        EventAttachment::create([
            'event_id' => $event->id,
            'business_id' => $first->id,
            'original_name' => 'Venue contract.pdf',
            'disk' => 'private',
            'path' => 'jobs/'.$event->id.'/attachments/venue-contract.pdf',
            'mime_type' => 'application/pdf',
            'size' => 128,
            'source' => 'upload',
            'description' => 'Approved venue agreement',
        ]);
        EventAttachment::create([
            'event_id' => $foreignEvent->id,
            'business_id' => $second->id,
            'original_name' => 'Foreign venue contract.pdf',
            'disk' => 'private',
            'path' => 'jobs/'.$foreignEvent->id.'/attachments/foreign-contract.pdf',
            'mime_type' => 'application/pdf',
            'size' => 128,
            'source' => 'upload',
            'description' => 'Confidential agreement',
        ]);

        $this->actingAs($user);

        $this->get(route('search.index', ['q' => 'contract', 'type' => 'document']))
            ->assertOk()
            ->assertSee('Venue contract.pdf')
            ->assertDontSee('Foreign venue contract.pdf')
            ->assertSee(route('work.show', $event).'#attachments', false)
            ->assertDontSee('jobs/'.$event->id.'/attachments/venue-contract.pdf');

        $this->get(route('search.index', ['q' => 'Approved venue agreement', 'type' => 'document']))
            ->assertOk()
            ->assertSee('Venue contract.pdf');

        $this->get(route('search.index', ['q' => 'Riverside Wedding', 'type' => 'document']))
            ->assertOk()
            ->assertSee('Venue contract.pdf');
    }

    public function test_document_search_applies_attachment_dates_and_rejects_status_filters(): void
    {
        $business = Business::create([
            'name' => 'Document Date Business',
            'slug' => 'document-date-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $user = User::factory()->create();
        $business->users()->attach($user->id, ['role' => 'owner']);

        $event = Event::create([
            'business_id' => $business->id,
            'reference' => 'DOC-'.Str::upper(Str::random(8)),
            'name' => 'Pretoria Wedding',
            'status' => 'draft',
        ]);

        EventAttachment::create([
            'event_id' => $event->id,
            'business_id' => $business->id,
            'original_name' => 'Current seating plan.pdf',
            'disk' => 'private',
            'path' => 'jobs/'.$event->id.'/attachments/current-seating-plan.pdf',
            'mime_type' => 'application/pdf',
            'size' => 128,
            'source' => 'upload',
            'description' => 'Current approved layout',
        ]);
        $historic = EventAttachment::create([
            'event_id' => $event->id,
            'business_id' => $business->id,
            'original_name' => 'Historic seating plan.pdf',
            'disk' => 'private',
            'path' => 'jobs/'.$event->id.'/attachments/historic-seating-plan.pdf',
            'mime_type' => 'application/pdf',
            'size' => 128,
            'source' => 'upload',
            'description' => 'Previous year layout',
        ]);
        $historic->forceFill([
            'created_at' => now()->subDays(30),
            'updated_at' => now()->subDays(30),
        ])->saveQuietly();

        $this->actingAs($user);

        $this->get(route('search.index', [
            'q' => 'Pretoria Wedding',
            'type' => 'document',
            'from' => now()->toDateString(),
            'to' => now()->toDateString(),
        ]))
            ->assertOk()
            ->assertSee('Current seating plan.pdf')
            ->assertDontSee('Historic seating plan.pdf');

        $this->get(route('search.index', [
            'q' => 'Pretoria Wedding',
            'type' => 'document',
            'status' => 'paid',
        ]))
            ->assertOk()
            ->assertSee('0 records')
            ->assertViewHas('results', static fn (array $results): bool => $results === []);
    }

}
