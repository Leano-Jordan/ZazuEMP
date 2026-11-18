<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Invoice;
use App\Services\InvoiceNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use LogicException;
use Tests\TestCase;

class InvoiceNumberGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_numbers_continue_after_existing_numeric_records_and_ignore_legacy_random_suffixes(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();
        $period = Carbon::parse('2026-11-18 12:00:00');

        $this->createInvoice($business->id, 'INV-202611-000007');
        $this->createInvoice($business->id, 'INV-202611-ABC123');
        $this->createInvoice($business->id, 'INV-202610-000099');

        $first = DB::transaction(
            fn (): string => app(InvoiceNumberGenerator::class)->nextForBusiness($business->id, $period)
        );

        $this->assertSame('INV-202611-000008', $first);

        // A voided invoice still consumes its number permanently.
        $this->createInvoice($business->id, $first, 'void');

        $second = DB::transaction(
            fn (): string => app(InvoiceNumberGenerator::class)->nextForBusiness($business->id, $period)
        );

        $this->assertSame('INV-202611-000009', $second);
    }

    public function test_number_series_are_isolated_by_business_and_calendar_month(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();
        $otherBusiness = Business::create([
            'name' => 'Second Test Business',
            'slug' => 'second-test-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);
        $generator = app(InvoiceNumberGenerator::class);

        $november = Carbon::parse('2026-11-18 12:00:00');
        $december = Carbon::parse('2026-12-01 09:00:00');

        $businessNovember = DB::transaction(
            fn (): string => $generator->nextForBusiness($business->id, $november)
        );
        $otherBusinessNovember = DB::transaction(
            fn (): string => $generator->nextForBusiness($otherBusiness->id, $november)
        );
        $businessDecember = DB::transaction(
            fn (): string => $generator->nextForBusiness($business->id, $december)
        );

        $this->assertSame('INV-202611-000001', $businessNovember);
        $this->assertSame('INV-202611-000001', $otherBusinessNovember);
        $this->assertSame('INV-202612-000001', $businessDecember);
    }

    public function test_number_allocation_requires_an_active_database_transaction(): void
    {
        $user = $this->signInAsOwner();
        $business = $user->businesses()->firstOrFail();

        $this->expectException(LogicException::class);

        app(InvoiceNumberGenerator::class)->nextForBusiness($business->id);
    }

    private function createInvoice(int $businessId, string $number, string $status = 'issued'): Invoice
    {
        return Invoice::create([
            'business_id' => $businessId,
            'number' => $number,
            'status' => $status,
            'currency' => 'ZAR',
            'subtotal' => '100.00',
            'tax_total' => '0.00',
            'total' => '100.00',
        ]);
    }
}
