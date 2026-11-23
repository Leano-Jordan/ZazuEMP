<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivacyDataMinimisationCopyTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_and_requirement_notes_explain_minimisation_of_sensitive_details(): void
    {
        $this->signInAsOwner();

        $customer = Customer::create(['name' => 'Privacy Guidance Customer']);
        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-PRIVACY-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)),
            'name' => 'Privacy Guidance Event',
            'event_date' => now()->addDays(20)->toDateString(),
            'status' => 'draft',
        ]);

        $this->get(route('work.create'))
            ->assertOk()
            ->assertSee('Keep notes limited to what the team needs to deliver the job.')
            ->assertSee('record only the practical instruction; do not add diagnoses or medical history.');

        $this->get(route('work.requirements.create', $event))
            ->assertOk()
            ->assertSee('Record only details needed to deliver this item.')
            ->assertSee('use the practical instruction only; avoid diagnoses or medical history.');
    }
}
