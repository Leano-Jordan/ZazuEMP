<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Support\Offline\SyncEntityIdentityRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LogicException;
use Tests\TestCase;

class SyncEntityIdentityRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_identity_is_stable_and_unique_within_its_business(): void
    {
        $business = $this->createBusiness('Identity Business');
        $first = Customer::create([
            'business_id' => $business->id,
            'name' => 'First customer',
        ]);
        $second = Customer::create([
            'business_id' => $business->id,
            'name' => 'Second customer',
        ]);
        $registry = app(SyncEntityIdentityRegistry::class);

        $firstIdentity = $registry->identify($first, 'customer');

        $this->assertSame($firstIdentity->id, $registry->identify($first, 'customer')->id);
        $this->assertSame('customer', $firstIdentity->entity_type);
        $this->assertTrue(Str::isUuid($firstIdentity->entity_uuid));
        $this->assertNotSame(
            $firstIdentity->entity_uuid,
            $registry->identify($second, 'customer')->entity_uuid,
        );
    }

    public function test_imported_identity_registration_is_idempotent_and_business_scoped(): void
    {
        $business = $this->createBusiness('Imported Identity Business');
        $record = Customer::create([
            'business_id' => $business->id,
            'name' => 'Imported customer',
        ]);
        $entityUuid = (string) Str::uuid();
        $registry = app(SyncEntityIdentityRegistry::class);

        $identity = $registry->register($record, 'customer', strtoupper($entityUuid));

        $this->assertSame($identity->id, $registry->register($record, 'customer', $entityUuid)->id);
        $this->assertSame($entityUuid, $identity->entity_uuid);

        $otherBusiness = $this->createBusiness('Other Identity Business');
        $otherRecord = Customer::create([
            'business_id' => $otherBusiness->id,
            'name' => 'Other customer',
        ]);

        $this->assertNotSame(
            $identity->id,
            $registry->register($otherRecord, 'customer', $entityUuid)->id,
        );
    }

    public function test_identity_cannot_be_reassigned_to_another_record_or_type(): void
    {
        $business = $this->createBusiness('Identity Integrity Business');
        $first = Customer::create([
            'business_id' => $business->id,
            'name' => 'First customer',
        ]);
        $second = Customer::create([
            'business_id' => $business->id,
            'name' => 'Second customer',
        ]);
        $registry = app(SyncEntityIdentityRegistry::class);
        $identity = $registry->identify($first, 'customer');

        try {
            $registry->register($second, 'customer', $identity->entity_uuid);
            $this->fail('An identity already assigned in this business must not be reused.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('entity_uuid', $exception->errors());
        }

        try {
            $registry->identify($first, 'event');
            $this->fail('An existing record identity must not change its entity type.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('entity_type', $exception->errors());
        }
    }

    public function test_identity_rejects_unsaved_records(): void
    {
        $this->expectException(LogicException::class);
        app(SyncEntityIdentityRegistry::class)->identify(new Customer, 'customer');
    }

    public function test_identity_rejects_a_record_with_unpersisted_business_ownership(): void
    {
        $business = $this->createBusiness('Ownership Identity Business');
        $otherBusiness = $this->createBusiness('Other Ownership Identity Business');
        $record = Customer::create([
            'business_id' => $business->id,
            'name' => 'Ownership customer',
        ]);
        $record->setAttribute('business_id', $otherBusiness->id);

        $this->expectException(LogicException::class);
        app(SyncEntityIdentityRegistry::class)->identify($record, 'customer');
    }

    public function test_registry_rejects_invalid_entity_types_and_identifiers(): void
    {
        $business = $this->createBusiness('Invalid Identity Business');
        $record = Customer::create([
            'business_id' => $business->id,
            'name' => 'Identity customer',
        ]);
        $registry = app(SyncEntityIdentityRegistry::class);

        try {
            $registry->identify($record, 'Customer');
            $this->fail('Entity types must use the stable lowercase identifier format.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('entity_type', $exception->errors());
        }

        try {
            $registry->register($record, 'customer', 'not-a-uuid');
            $this->fail('Imported identities must be valid UUIDs.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('entity_uuid', $exception->errors());
        }
    }

    private function createBusiness(string $name): Business
    {
        return Business::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(8)),
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
    }
}
