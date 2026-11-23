<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Event;
use App\Models\EventRequirement;
use App\Models\Quote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteSingleQuantityConfirmationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signInAsOwner();
    }

    public function test_quote_creation_requires_an_explicit_confirmation_when_a_line_quantity_is_one(): void
    {
        $customer = Customer::create(['name' => 'Single Quantity Customer']);
        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-SINGLE-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)),
            'name' => 'Single Quantity Quote',
            'event_date' => now()->addDays(20)->toDateString(),
            'status' => 'draft',
        ]);
        $requirement = EventRequirement::create([
            'event_id' => $event->id,
            'description' => 'White stretch tent',
            'category' => 'Rental',
            'quantity' => '1.00',
            'unit' => 'tent',
            'status' => 'open',
        ]);
        $createUrl = route('work.quotes.create', $event);
        $storeUrl = route('work.quotes.store', $event);
        $payload = [
            'currency' => 'ZAR',
            'quantity' => [$requirement->id => '1.00'],
            'unit_price' => [$requirement->id => '2500.00'],
        ];

        $this->from($createUrl)
            ->post($storeUrl, $payload)
            ->assertRedirect($createUrl)
            ->assertSessionHasErrors('quantity.'.$requirement->id);

        $this->assertDatabaseCount('quotes', 0);

        $response = $this->post($storeUrl, $payload + [
            'confirm_single_quantity' => [$requirement->id => '1'],
        ]);
        $quote = Quote::query()->firstOrFail();
        $line = $quote->latestVersion()->firstOrFail()->items()->firstOrFail();

        $response->assertRedirect(route('quotes.show', $quote));
        $this->assertSame('1.00', (string) $line->quantity);
        $this->assertSame('2500.00', (string) $line->unit_price);
    }

    public function test_quote_revision_also_requires_explicit_confirmation_for_a_single_unit(): void
    {
        $customer = Customer::create(['name' => 'Single Quantity Revision Customer']);
        $event = Event::create([
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-SINGLE-REV-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 6)),
            'name' => 'Single Quantity Revision',
            'event_date' => now()->addDays(21)->toDateString(),
            'status' => 'draft',
        ]);
        $requirement = EventRequirement::create([
            'event_id' => $event->id,
            'description' => 'Guest chair',
            'category' => 'Rental',
            'quantity' => '2.00',
            'unit' => 'chair',
            'status' => 'open',
        ]);

        $this->post(route('work.quotes.store', $event), [
            'currency' => 'ZAR',
            'quantity' => [$requirement->id => '2.00'],
            'unit_price' => [$requirement->id => '120.00'],
        ])->assertRedirect();

        $quote = Quote::query()->firstOrFail();
        $version = $quote->versions()->firstOrFail();
        $editUrl = route('quotes.versions.edit', [$quote, $version]);
        $updateUrl = route('quotes.versions.update', [$quote, $version]);
        $payload = [
            'quantity' => [$requirement->id => '1.00'],
            'unit_price' => [$requirement->id => '120.00'],
        ];

        $this->from($editUrl)
            ->put($updateUrl, $payload)
            ->assertRedirect($editUrl)
            ->assertSessionHasErrors('quantity.'.$requirement->id);

        $this->assertSame('2.00', (string) $version->items()->firstOrFail()->quantity);

        $this->put($updateUrl, $payload + [
            'confirm_single_quantity' => [$requirement->id => '1'],
        ])->assertRedirect(route('quotes.show', $quote));

        $this->assertSame('1.00', (string) $version->items()->firstOrFail()->fresh()->quantity);
    }
}
