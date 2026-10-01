<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessTaxProfile;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Event;
use App\Models\EventCost;
use App\Models\EventPreparationItem;
use App\Models\EventRequirement;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\QuoteVersion;
use App\Models\Supplier;
use App\Models\TaxRate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('ZAZU_DEMO_EMAIL', 'demo@zazu.local');
        $password = (string) env('ZAZU_DEMO_PASSWORD', 'password');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Zazu Demo Owner',
                'username' => 'zazu_demo_owner',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        $business = Business::updateOrCreate(
            ['slug' => 'zazu-demo-catering'],
            [
                'name' => 'Zazu Demo Catering',
                'status' => 'active',
                'currency' => 'ZAR',
                'email' => $email,
                'phone' => '010 000 0000',
                'address' => 'Pretoria, Gauteng',
                'business_setup_completed_at' => now(),
                'catalogue_setup_completed_at' => now(),
            ]
        );

        $business->users()->syncWithoutDetaching([
            $user->id => ['role' => 'owner', 'experience_level' => 'intermediate'],
        ]);

        BusinessTaxProfile::updateOrCreate(
            ['business_id' => $business->id],
            [
                'legal_name' => 'Zazu Demo Catering (Pty) Ltd',
                'trading_name' => 'Zazu Demo Catering',
                'registration_type' => 'company',
                'tax_regime' => 'standard_income_tax',
                'vat_status' => 'registered',
                'vat_number' => '4123456789',
                'representative_name' => 'Zazu Demo Owner',
                'representative_email' => $email,
                'representative_phone' => '010 000 0000',
                'activity_flags' => ['food_handling' => true, 'employees' => true],
            ]
        );

        $taxRate = TaxRate::updateOrCreate(
            ['business_id' => $business->id, 'code' => 'VAT15'],
            [
                'name' => 'VAT 15%',
                'tax_type' => 'vat',
                'treatment' => 'standard',
                'rate' => '15.00',
                'effective_from' => now()->toDateString(),
                'is_default' => true,
                'is_active' => true,
                'source_reference' => 'Zazu demo data',
            ]
        );

        $customer = Customer::updateOrCreate(
            ['business_id' => $business->id, 'name' => 'Mokoena Family Events'],
            [
                'legal_name' => 'Mokoena Family Events',
                'billing_address' => 'Pretoria, Gauteng',
                'notes' => 'Demo customer for end-to-end Zazu hardening.',
            ]
        );

        $contact = CustomerContact::updateOrCreate(
            ['customer_id' => $customer->id, 'email' => 'client@example.test'],
            [
                'name' => 'Naledi Mokoena',
                'phone' => '082 000 0000',
                'label' => 'Primary contact',
                'is_primary' => true,
            ]
        );

        $event = Event::updateOrCreate(
            ['business_id' => $business->id, 'reference' => 'ZAZU-DEMO-001'],
            [
                'customer_id' => $customer->id,
                'event_day_contact_id' => $contact->id,
                'reference' => 'ZAZU-DEMO-001',
                'name' => 'Mokoena Family Celebration',
                'event_type' => 'Birthday',
                'customer_name' => $customer->name,
                'customer_phone' => $contact->phone,
                'customer_email' => $contact->email,
                'event_date' => now()->addDays(14)->toDateString(),
                'event_address' => 'Pretoria, Gauteng',
                'notes' => 'Complete seeded scenario covering sales, preparation, purchasing, costs and finance.',
                'status' => 'completed',
            ]
        );

        $requirements = [
            ['description' => 'Buffet catering', 'category' => 'Catering', 'quantity' => '80.00', 'unit' => 'guests', 'notes' => 'Main buffet service'],
            ['description' => 'Staff service', 'category' => 'Staff', 'quantity' => '4.00', 'unit' => 'people', 'notes' => 'Setup and service'],
            ['description' => 'Table and chair setup', 'category' => 'Furniture & equipment', 'quantity' => '10.00', 'unit' => 'tables', 'notes' => 'Event seating'],
        ];

        $createdRequirements = [];
        foreach ($requirements as $row) {
            $createdRequirements[] = EventRequirement::updateOrCreate(
                ['event_id' => $event->id, 'description' => $row['description']],
                $row + ['status' => 'completed']
            );
        }

        $quote = Quote::updateOrCreate(
            ['event_id' => $event->id, 'reference' => 'QUO-ZAZU-DEMO-001'],
            ['status' => 'accepted', 'currency' => 'ZAR']
        );

        $version = QuoteVersion::updateOrCreate(
            ['quote_id' => $quote->id, 'version' => 1],
            [
                'status' => 'accepted',
                'subtotal' => '10000.00',
                'tax_total' => '1500.00',
                'total' => '11500.00',
                'deposit_percent' => '30.00',
                'deposit_amount' => '3450.00',
                'tax_rate_id' => $taxRate->id,
                'tax_code' => 'VAT15',
                'tax_label' => 'VAT',
                'tax_treatment' => 'standard',
                'tax_rate' => '15.00',
                'tax_snapshot_at' => now(),
                'notes' => 'Accepted demo quote.',
            ]
        );

        $lineData = [
            [$createdRequirements[0], 'Buffet catering', '80.00', 'guest', '7500.00', '93.75'],
            [$createdRequirements[1], 'Staff service', '4.00', 'person', '1500.00', '375.00'],
            [$createdRequirements[2], 'Table and chair setup', '10.00', 'table', '1000.00', '100.00'],
        ];

        foreach ($lineData as [$requirement, $description, $quantity, $unit, $lineTotal, $unitPrice]) {
            QuoteItem::updateOrCreate(
                ['quote_version_id' => $version->id, 'event_requirement_id' => $requirement->id],
                [
                    'description' => $description,
                    'quantity' => $quantity,
                    'unit' => $unit,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'pricing_basis' => 'Demo fixed price',
                    'source_snapshot' => [
                        'description' => $requirement->description,
                        'category' => $requirement->category,
                        'quantity' => (string) $requirement->quantity,
                        'unit' => $requirement->unit,
                        'notes' => $requirement->notes,
                        'capability_id' => null,
                    ],
                ]
            );
        }

        foreach ([
            ['title' => 'Confirm buffet quantities', 'category' => 'Food', 'quantity' => '80.00', 'unit' => 'guests'],
            ['title' => 'Prepare tables and chairs', 'category' => 'Equipment', 'quantity' => '10.00', 'unit' => 'tables'],
            ['title' => 'Allocate service staff', 'category' => 'Staff', 'quantity' => '4.00', 'unit' => 'people'],
        ] as $item) {
            EventPreparationItem::updateOrCreate(
                ['business_id' => $business->id, 'event_id' => $event->id, 'title' => $item['title']],
                $item + ['status' => 'completed', 'due_date' => now()->addDays(10)->toDateString(), 'completed_at' => now()]
            );
        }

        $supplier = Supplier::updateOrCreate(
            ['business_id' => $business->id, 'name' => 'Demo Fresh Foods Supplier'],
            ['contact_name' => 'Supplier Contact', 'email' => 'supplier@example.test', 'phone' => '083 000 0000']
        );

        $purchaseOrder = PurchaseOrder::updateOrCreate(
            ['business_id' => $business->id, 'reference' => 'PO-ZAZU-DEMO-001'],
            [
                'event_id' => $event->id,
                'supplier_id' => $supplier->id,
                'idempotency_key' => 'demo-po-zazu-001',
                'reference' => 'PO-ZAZU-DEMO-001',
                'status' => 'received',
                'currency' => 'ZAR',
                'total_amount' => '2800.00',
                'ordered_at' => now()->subDays(5)->toDateString(),
                'expected_at' => now()->subDays(2)->toDateString(),
                'notes' => 'Demo fully received purchase order.',
            ]
        );

        PurchaseOrderItem::updateOrCreate(
            ['purchase_order_id' => $purchaseOrder->id, 'description' => 'Fresh catering supplies'],
            [
                'business_id' => $business->id,
                'description' => 'Fresh catering supplies',
                'quantity' => '1.00',
                'received_quantity' => '1.00',
                'unit' => 'lot',
                'unit_price' => '2800.00',
                'line_total' => '2800.00',
            ]
        );

        foreach ([
            ['category' => 'Food', 'description' => 'Fresh catering supplies', 'amount' => '2800.00'],
            ['category' => 'Staff', 'description' => 'Event staffing', 'amount' => '1800.00'],
            ['category' => 'Transport', 'description' => 'Delivery and setup transport', 'amount' => '700.00'],
        ] as $cost) {
            EventCost::updateOrCreate(
                ['business_id' => $business->id, 'event_id' => $event->id, 'description' => $cost['description']],
                [
                    'category' => $cost['category'],
                    'currency' => 'ZAR',
                    'projected_amount' => $cost['amount'],
                    'actual_amount' => $cost['amount'],
                    'status' => 'paid',
                    'notes' => 'Seeded demo cost.',
                ]
            );
        }

        $invoice = Invoice::updateOrCreate(
            ['business_id' => $business->id, 'number' => 'INV-ZAZU-DEMO-001'],
            [
                'event_id' => $event->id,
                'quote_id' => $quote->id,
                'quote_version_id' => $version->id,
                'number' => 'INV-ZAZU-DEMO-001',
                'idempotency_key' => 'demo-invoice-zazu-001',
                'business_legal_name' => 'Zazu Demo Catering (Pty) Ltd',
                'business_trading_name' => 'Zazu Demo Catering',
                'business_address' => 'Pretoria, Gauteng',
                'business_email' => $email,
                'business_phone' => '010 000 0000',
                'customer_name' => $customer->name,
                'customer_address' => $customer->billing_address,
                'customer_email' => $contact->email,
                'customer_phone' => $contact->phone,
                'status' => 'paid',
                'currency' => 'ZAR',
                'subtotal' => '10000.00',
                'tax_total' => '1500.00',
                'total' => '11500.00',
                'issued_at' => now()->subDays(8)->toDateString(),
                'due_at' => now()->addDays(7)->toDateString(),
                'tax_rate_id' => $taxRate->id,
                'tax_code' => 'VAT15',
                'tax_label' => 'VAT',
                'tax_treatment' => 'standard',
                'tax_rate' => '15.00',
                'notes' => 'Paid demo invoice linked to accepted quote v1.',
            ]
        );

        foreach ([
            ['description' => 'Buffet catering', 'quantity' => '80.00', 'unit' => 'guest', 'unit_price' => '93.75', 'line_total' => '7500.00'],
            ['description' => 'Staff service', 'quantity' => '4.00', 'unit' => 'person', 'unit_price' => '375.00', 'line_total' => '1500.00'],
            ['description' => 'Table and chair setup', 'quantity' => '10.00', 'unit' => 'table', 'unit_price' => '100.00', 'line_total' => '1000.00'],
        ] as $line) {
            InvoiceItem::updateOrCreate(
                ['invoice_id' => $invoice->id, 'description' => $line['description']],
                $line
            );
        }

        Payment::updateOrCreate(
            ['business_id' => $business->id, 'idempotency_key' => 'demo-payment-deposit-001'],
            [
                'invoice_id' => $invoice->id,
                'event_id' => $event->id,
                'type' => 'deposit',
                'idempotency_key' => 'demo-payment-deposit-001',
                'amount' => '3450.00',
                'currency' => 'ZAR',
                'method' => 'bank_transfer',
                'reference' => 'DEP-ZAZU-DEMO-001',
                'paid_at' => now()->subDays(7)->toDateString(),
                'notes' => 'Demo deposit payment.',
            ]
        );

        Payment::updateOrCreate(
            ['business_id' => $business->id, 'idempotency_key' => 'demo-payment-balance-001'],
            [
                'invoice_id' => $invoice->id,
                'event_id' => $event->id,
                'type' => 'payment',
                'idempotency_key' => 'demo-payment-balance-001',
                'amount' => '8050.00',
                'currency' => 'ZAR',
                'method' => 'bank_transfer',
                'reference' => 'BAL-ZAZU-DEMO-001',
                'paid_at' => now()->subDays(2)->toDateString(),
                'notes' => 'Demo final balance payment.',
            ]
        );
    }
}
