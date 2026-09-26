<?php

namespace Tests\Feature;

use App\Models\BusinessCapability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessCapabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_capability_can_be_created(): void
    {
        $response = $this->post(route('capabilities.store'), [
            'name' => 'Wedding Catering',
            'category' => 'Catering',
            'capability_type' => 'service',
            'pricing_basis' => 'per_person',
            'default_unit' => 'guest',
            'description' => 'Food and service for weddings.',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('capabilities.index'));

        $this->assertDatabaseHas('business_capabilities', [
            'name' => 'Wedding Catering',
            'category' => 'Catering',
            'capability_type' => 'service',
            'pricing_basis' => 'per_person',
            'is_active' => 1,
        ]);
    }

    public function test_business_capability_can_be_updated(): void
    {
        $capability = BusinessCapability::create([
            'name' => 'Chair Hire',
            'capability_type' => 'rental',
            'pricing_basis' => 'per_unit',
            'default_unit' => 'chair',
            'is_active' => true,
        ]);

        $response = $this->put(route('capabilities.update', $capability), [
            'name' => 'Premium Chair Hire',
            'category' => 'Furniture & equipment',
            'capability_type' => 'rental',
            'pricing_basis' => 'per_unit',
            'default_unit' => 'chair',
            'description' => 'Premium event chairs.',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('capabilities.index'));

        $this->assertDatabaseHas('business_capabilities', [
            'id' => $capability->id,
            'name' => 'Premium Chair Hire',
            'category' => 'Equipment Hire',
        ]);
    }
}
