<?php

namespace Tests\Feature;

use Tests\TestCase;

class ZazuFoundationTest extends TestCase
{
    public function test_core_error_codes_and_catering_beverage_foundations_are_registered(): void
    {
        $requiredCodes = [
            'AUTH-001',
            'AUTHZ-001',
            'VAL-001',
            'ROUTE-001',
            'DB-001',
            'BUS-001',
            'FILE-001',
            'API-001',
            'CFG-001',
            'APP-001',
            'SYS-001',
        ];

        foreach ($requiredCodes as $code) {
            $this->assertArrayHasKey($code, config('zazu.errors'));
        }

        $catering = config('zazu.service_categories.Catering', []);

        foreach ([
            'Cooldrinks',
            'Bottled water',
            'Juices',
            'Homemade juices',
            'Ginger ale',
            'Homemade beer (Mqombothi)',
        ] as $service) {
            $this->assertContains($service, $catering);
        }

        $this->assertContains('500ml', config('zazu.beverage_sizes'));
        $this->assertContains('2L', config('zazu.beverage_sizes'));
        $this->assertSame('Bottle', config('zazu.units.bottle'));
        $this->assertSame('Crate', config('zazu.units.crate'));
    }
}
