<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessTaxProfile;
use App\Models\ComplianceDocument;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventRequirement;
use App\Models\Invoice;
use App\Models\TaxRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaxComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_settings_can_create_and_version_a_vat_rate(): void
    {
        $business = Business::create([
            'name' => 'Tax Test Business',
            'slug' => 'tax-test-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $this->signInAsOwner($business);

        $this->put(route('settings.update'), [
            'name' => $business->name,
            'currency' => 'ZAR',
            'legal_name' => 'Tax Test Business (Pty) Ltd',
            'trading_name' => 'Tax Test',
            'registration_type' => 'company',
            'registration_number' => '2026/123456/07',
            'tax_regime' => 'standard_income_tax',
            'income_tax_number' => '9123456789',
            'vat_status' => 'registered',
            'vat_number' => '4123456789',
            'default_vat_rate' => '15.00',
            'tax_effective_from' => now()->toDateString(),
        ])->assertRedirect(route('settings.index'));

        $this->assertDatabaseHas('business_tax_profiles', [
            'business_id' => $business->id,
            'vat_status' => 'registered',
            'vat_number' => '4123456789',
        ]);

        $rate = TaxRate::where('business_id', $business->id)->where('code', 'VAT_STANDARD')->firstOrFail();
        $this->assertSame('15.00', (string) $rate->rate);
        $this->assertTrue($rate->is_default);

        $this->put(route('settings.update'), [
            'name' => $business->name,
            'currency' => 'ZAR',
            'legal_name' => 'Tax Test Business (Pty) Ltd',
            'registration_type' => 'company',
            'tax_regime' => 'standard_income_tax',
            'vat_status' => 'registered',
            'vat_number' => '4123456789',
            'default_vat_rate' => '14.00',
            'tax_effective_from' => now()->addDay()->toDateString(),
        ])->assertRedirect(route('settings.index'));

        $this->assertSame('14.00', (string) TaxRate::where('business_id', $business->id)->where('code', 'VAT_STANDARD')->where('is_default', true)->firstOrFail()->rate);
        $this->assertSame(now()->toDateString(), TaxRate::whereKey($rate->id)->firstOrFail()->effective_to?->toDateString());
    }

    public function test_quote_snapshots_tax_and_totals_without_using_floating_point_math(): void
    {
        $business = Business::create([
            'name' => 'Quote Tax Business',
            'slug' => 'quote-tax-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $this->signInAsOwner($business);

        $rate = TaxRate::create([
            'business_id' => $business->id,
            'name' => 'Standard VAT',
            'code' => 'VAT_STANDARD',
            'tax_type' => 'vat',
            'treatment' => 'standard',
            'rate' => '15.00',
            'effective_from' => now()->toDateString(),
            'is_default' => true,
            'is_active' => true,
        ]);

        $customer = Customer::create(['business_id' => $business->id, 'name' => 'Tax Quote Customer']);

        $event = Event::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-TAX-001',
            'name' => 'Tax Quote Event',
            'event_date' => '2026-10-20',
            'status' => 'draft',
        ]);

        $requirement = EventRequirement::create([
            'event_id' => $event->id,
            'description' => '100 chairs',
            'quantity' => 100,
            'unit' => 'chairs',
            'status' => 'open',
        ]);

        $this->post(route('work.quotes.store', $event), [
            'currency' => 'ZAR',
            'tax_rate_id' => $rate->id,
            'unit_price' => [$requirement->id => '25.50'],
        ])->assertRedirect();

        $version = $event->quotes()->firstOrFail()->versions()->firstOrFail();

        $this->assertSame('2550.00', (string) $version->subtotal);
        $this->assertSame('382.50', (string) $version->tax_total);
        $this->assertSame('2932.50', (string) $version->total);
        $this->assertSame('VAT_STANDARD', $version->tax_code);
        $this->assertSame('15.00', (string) $version->tax_rate);
    }

    public function test_invoice_requires_an_accepted_quote_and_copies_tax_snapshot(): void
    {
        $business = Business::create([
            'name' => 'Invoice Tax Business',
            'slug' => 'invoice-tax-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $this->signInAsOwner($business);

        $rate = TaxRate::create([
            'business_id' => $business->id,
            'name' => 'Standard VAT',
            'code' => 'VAT_STANDARD',
            'tax_type' => 'vat',
            'treatment' => 'standard',
            'rate' => '15.00',
            'effective_from' => now()->toDateString(),
            'is_default' => true,
            'is_active' => true,
        ]);

        $customer = Customer::create(['business_id' => $business->id, 'name' => 'Invoice Customer']);
        $event = Event::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'reference' => 'ZAZ-INV-TAX',
            'name' => 'Invoice Event',
            'event_date' => '2026-10-21',
            'status' => 'draft',
        ]);
        $requirement = EventRequirement::create([
            'event_id' => $event->id,
            'description' => '10 tables',
            'quantity' => 10,
            'unit' => 'tables',
            'status' => 'open',
        ]);

        $this->post(route('work.quotes.store', $event), [
            'currency' => 'ZAR',
            'tax_rate_id' => $rate->id,
            'unit_price' => [$requirement->id => '100.00'],
        ]);

        $quote = $event->quotes()->firstOrFail();

        $this->post(route('finance.invoices.store'), ['quote_id' => $quote->id])
            ->assertStatus(422);

        $this->patch(route('quotes.status', $quote), ['status' => 'sent'])->assertRedirect();
        $this->patch(route('quotes.status', $quote), ['status' => 'accepted'])->assertRedirect();

        $this->post(route('finance.invoices.store'), ['quote_id' => $quote->id])
            ->assertRedirect(route('finance.index'));

        $invoice = Invoice::where('quote_id', $quote->id)->firstOrFail();

        $this->assertSame('1150.00', (string) $invoice->subtotal);
        $this->assertSame('150.00', (string) $invoice->tax_total);
        $this->assertSame('1150.00', (string) $invoice->total);
        $this->assertSame('VAT_STANDARD', $invoice->tax_code);
        $this->assertSame('15.00', (string) $invoice->tax_rate);

    }

    public function test_compliance_documents_are_business_scoped_and_stored_privately(): void
    {
        Storage::fake('local');

        $business = Business::create([
            'name' => 'Compliance Business',
            'slug' => 'compliance-business',
            'status' => 'active',
            'currency' => 'ZAR',
        ]);

        $this->signInAsOwner($business);

        $response = $this->post(route('settings.compliance.store'), [
            'document_type' => 'sars_tcs_good_standing',
            'title' => 'SARS TCS Good Standing',
            'reference_number' => 'TCS-123',
            'status' => 'current',
            'document' => UploadedFile::fake()->create('tcs.pdf', 100, 'application/pdf'),
            'verified' => '1',
        ]);

        $response->assertRedirect(route('settings.compliance'));

        $document = ComplianceDocument::firstOrFail();

        $this->assertSame($business->id, $document->business_id);
        $this->assertNotNull($document->storage_path);
        Storage::disk('local')->assertExists($document->storage_path);
        $this->assertNotNull($document->verified_at);
    }
}