<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerContactLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_creation_accepts_optional_day_and_night_contacts_and_profile_photo(): void
    {
        Storage::fake('public');

        $photo = UploadedFile::fake()->image('customer.jpg');

        $response = $this->post(route('customers.store'), [
            'name' => 'Day Night Customer',
            'profile_photo' => $photo,
            'primary_contact_name' => 'Primary Contact',
            'primary_contact_phone' => '0711111111',
            'day_contact_name' => 'Day Contact',
            'day_contact_phone' => '0722222222',
            'night_contact_name' => 'Night Contact',
            'night_contact_phone' => '0733333333',
        ]);

        $response->assertRedirect(route('customers.index'));

        $customer = Customer::query()->where('name', 'Day Night Customer')->firstOrFail();

        $this->assertNotNull($customer->profile_photo_path);
        $this->assertCount(3, $customer->contacts);
        $this->assertSame(1, $customer->contacts()->where('label', 'Day')->count());
        $this->assertSame(1, $customer->contacts()->where('label', 'Night')->count());
        $this->assertSame(1, $customer->contacts()->where('is_primary', true)->count());

        Storage::disk('public')->assertExists($customer->profile_photo_path);
    }

    public function test_secondary_contact_can_be_edited_and_removed_without_destroying_history(): void
    {
        $customer = Customer::create(['name' => 'Contact Lifecycle Customer']);

        $primary = CustomerContact::create([
            'customer_id' => $customer->id,
            'name' => 'Primary',
            'is_primary' => true,
        ]);

        $contact = CustomerContact::create([
            'customer_id' => $customer->id,
            'name' => 'Secondary',
            'label' => 'Night',
        ]);

        $response = $this->put(route('customers.contacts.update', [$customer, $contact]), [
            'name' => 'Updated Secondary',
            'label' => 'Day',
            'is_primary' => false,
        ]);

        $response->assertRedirect(route('customers.show', $customer));
        $this->assertSame('Updated Secondary', $contact->fresh()->name);

        $response = $this->delete(route('customers.contacts.destroy', [$customer, $contact]));

        $response->assertRedirect(route('customers.show', $customer));
        $this->assertSoftDeleted('customer_contacts', ['id' => $contact->id]);
        $this->assertNotNull($primary->fresh()->id);
        $this->assertCount(1, $customer->fresh()->contacts);
    }

    public function test_primary_contact_cannot_be_removed_without_replacement(): void
    {
        $customer = Customer::create(['name' => 'Primary Protection Customer']);

        $primary = CustomerContact::create([
            'customer_id' => $customer->id,
            'name' => 'Primary',
            'is_primary' => true,
        ]);

        $response = $this->delete(route('customers.contacts.destroy', [$customer, $primary]));

        $response->assertRedirect(route('customers.show', $customer));
        $response->assertSessionHas('error');
        $this->assertNull($primary->fresh()->deleted_at);
    }
}
