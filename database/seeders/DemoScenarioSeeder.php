<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessTaxProfile;
use App\Models\BusinessCapability;
use App\Models\Asset;
use App\Models\AssetAllocation;
use App\Models\ComplianceDocument;
use App\Models\FinanceExpense;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\PurchaseOrderReceipt;
use App\Models\TravelCost;
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
                'address' => '18 Freedom Avenue, Soshanguve, Pretoria, Gauteng, 0152',
                'website' => 'https://demo.zazu.local',
                'tax_number' => '9012345678',
                'business_setup_completed_at' => now(),
                'catalogue_setup_completed_at' => now(),
            ]
        );

        foreach ([
            ['name' => 'Zazu Operations Manager', 'username' => 'zazu_operations', 'email' => 'operations@zazu.local', 'role' => 'manager', 'experience_level' => 'advanced'],
            ['name' => 'Zazu Finance Administrator', 'username' => 'zazu_finance', 'email' => 'finance@zazu.local', 'role' => 'staff', 'experience_level' => 'advanced'],
        ] as $teamMember) {
            $member = User::updateOrCreate(
                ['email' => $teamMember['email']],
                ['name' => $teamMember['name'], 'username' => $teamMember['username'], 'password' => Hash::make('password'), 'email_verified_at' => now()]
            );
            $business->users()->syncWithoutDetaching([
                $member->id => ['role' => $teamMember['role'], 'experience_level' => $teamMember['experience_level']],
            ]);
        }

        $business->users()->syncWithoutDetaching([
            $user->id => ['role' => 'owner', 'experience_level' => 'intermediate'],
        ]);

        BusinessTaxProfile::updateOrCreate(
            ['business_id' => $business->id],
            [
                'legal_name' => 'Zazu Demo Catering (Pty) Ltd',
                'trading_name' => 'Zazu Demo Catering',
                'registration_type' => 'company',
                'registration_number' => '2024/123456/07',
                'income_tax_number' => '9012345678',
                'financial_year_end' => now()->endOfYear()->toDateString(),
                'paye_number' => '7123456789',
                'uif_number' => '7654321',
                'sdl_number' => 'L123456789',
                'tax_regime' => 'standard_income_tax',
                'vat_status' => 'registered',
                'vat_number' => '4123456789',
                'representative_name' => 'Zazu Demo Owner',
                'representative_email' => $email,
                'representative_phone' => '010 000 0000',
                'activity_flags' => ['food_handling' => true, 'employees' => true, 'alcohol_service' => false, 'equipment_rental' => true, 'transport' => true],
                'compliance_notes' => 'Demo profile representing an established South African catering and event-services business.',
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

        $capabilities = [];
        foreach ([
            ['name' => 'Buffet Catering', 'category' => 'Catering', 'capability_type' => 'service', 'pricing_basis' => 'per guest', 'default_price' => '93.75', 'default_unit' => 'guest', 'description' => 'Full buffet preparation, setup and service for private and corporate events.'],
            ['name' => 'Event Furniture', 'category' => 'Rentals', 'capability_type' => 'rental', 'pricing_basis' => 'per table', 'default_price' => '100.00', 'default_unit' => 'table', 'description' => 'Tables and chairs supplied, delivered and collected for events.'],
            ['name' => 'Service Staff', 'category' => 'Staff', 'capability_type' => 'service', 'pricing_basis' => 'per person', 'default_price' => '375.00', 'default_unit' => 'person', 'description' => 'Event setup, serving and close-down staff.'],
            ['name' => 'Event Transport', 'category' => 'Logistics', 'capability_type' => 'service', 'pricing_basis' => 'per km', 'default_price' => '8.50', 'default_unit' => 'km', 'description' => 'Delivery and event logistics transport.'],
            ['name' => 'Dessert Table', 'category' => 'Baking', 'capability_type' => 'service', 'pricing_basis' => 'per event', 'default_price' => '2200.00', 'default_unit' => 'event', 'description' => 'Curated dessert and sweet-treat table for events.'],
        ] as $capability) {
            $capabilities[$capability['name']] = BusinessCapability::updateOrCreate(
                ['business_id' => $business->id, 'name' => $capability['name']],
                $capability + ['currency' => 'ZAR', 'is_active' => true]
            );
        }

        $customer = Customer::updateOrCreate(
            ['business_id' => $business->id, 'name' => 'Mokoena Family Events'],
            [
                'legal_name' => 'Mokoena Family Events (Pty) Ltd',
                'registration_number' => '2025/456789/07',
                'tax_number' => '9876543210',
                'vat_number' => '4987654321',
                'billing_address' => '42 Jacaranda Street, Pretoria, Gauteng, 0001',
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
                'event_date' => now()->subDay()->toDateString(),
                'event_address' => 'Pretoria, Gauteng',
                'notes' => 'Complete seeded scenario covering sales, preparation, purchasing, costs and finance.',
                'status' => 'completed',
            ]
        );

        $requirements = [
            ['description' => 'Buffet catering', 'category' => 'Catering', 'quantity' => '80.00', 'unit' => 'guests', 'notes' => 'Main buffet service', 'capability_id' => $capabilities['Buffet Catering']->id],
            ['description' => 'Staff service', 'category' => 'Staff', 'quantity' => '4.00', 'unit' => 'people', 'notes' => 'Setup and service', 'capability_id' => $capabilities['Service Staff']->id],
            ['description' => 'Table and chair setup', 'category' => 'Furniture & equipment', 'quantity' => '10.00', 'unit' => 'tables', 'notes' => 'Event seating', 'capability_id' => $capabilities['Event Furniture']->id],
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
                        'capability_id' => $requirement->capability_id ? (int) $requirement->capability_id : null,
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
                $item + ['status' => 'completed', 'due_date' => now()->subDays(3)->toDateString(), 'completed_at' => now()]
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
                'capability_id' => $capabilities['Buffet Catering']->id,
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
                'due_at' => now()->subDay()->toDateString(),
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

        // Inventory, assets, procurement, travel, expenses and compliance are populated so the demo behaves like an established business.
        $inventoryItems = [];
        foreach ([
            ['sku' => 'INV-CHAFER-001', 'name' => 'Stainless chafing dish', 'unit' => 'unit', 'reorder_level' => '6.00', 'capability' => 'Buffet Catering'],
            ['sku' => 'INV-TABLE-001', 'name' => '6ft folding table', 'unit' => 'unit', 'reorder_level' => '4.00', 'capability' => 'Event Furniture'],
            ['sku' => 'INV-NAPKIN-001', 'name' => 'White linen napkin', 'unit' => 'piece', 'reorder_level' => '100.00', 'capability' => 'Buffet Catering'],
            ['sku' => 'INV-PLATE-001', 'name' => 'White dinner plate', 'unit' => 'piece', 'reorder_level' => '120.00', 'capability' => 'Buffet Catering'],
        ] as $row) {
            $inventoryItems[$row['sku']] = InventoryItem::updateOrCreate(
                ['business_id' => $business->id, 'sku' => $row['sku']],
                [
                    'name' => $row['name'],
                    'unit' => $row['unit'],
                    'reorder_level' => $row['reorder_level'],
                    'capability_id' => $capabilities[$row['capability']]->id,
                ]
            );
        }

        $movementRows = [
            [$inventoryItems['INV-CHAFER-001'], 'receipt', '20.00', '350.00', 'INV-REC-001', 'Opening equipment stock', 'Opening equipment stock received into storage.'],
            [$inventoryItems['INV-CHAFER-001'], 'usage', '4.00', '350.00', 'INV-USE-001', 'Allocated to demo event', 'Equipment issued for event service.'],
            [$inventoryItems['INV-TABLE-001'], 'receipt', '18.00', '850.00', 'INV-REC-002', 'Opening rental stock', 'Rental stock received into storage.'],
            [$inventoryItems['INV-TABLE-001'], 'usage', '10.00', '850.00', 'INV-USE-002', 'Allocated to demo event', 'Tables issued for event setup.'],
            [$inventoryItems['INV-NAPKIN-001'], 'receipt', '300.00', '12.00', 'INV-REC-003', 'Opening consumable stock', 'Consumables received into storage.'],
            [$inventoryItems['INV-NAPKIN-001'], 'usage', '80.00', '12.00', 'INV-USE-003', 'Consumed for demo event', 'Consumables issued for guest service.'],
            [$inventoryItems['INV-PLATE-001'], 'receipt', '240.00', '18.00', 'INV-REC-004', 'Opening consumable stock', 'Dinnerware received into storage.'],
            [$inventoryItems['INV-PLATE-001'], 'usage', '80.00', '18.00', 'INV-USE-004', 'Consumed for demo event', 'Dinnerware issued for guest service.'],
        ];
        foreach ($movementRows as [$item, $type, $quantity, $unitCost, $key, $reference, $notes]) {
            InventoryMovement::updateOrCreate(
                ['business_id' => $business->id, 'idempotency_key' => 'demo-'.$key],
                [
                    'inventory_item_id' => $item->id,
                    'event_id' => $event->id,
                    'type' => $type,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'movement_date' => now()->subDays(3)->toDateString(),
                    'reference' => $reference,
                    'notes' => $notes,
                ]
            );
        }

        foreach ([
            ['asset_tag' => 'AST-TENT-001', 'name' => '6m x 12m stretch tent', 'status' => 'available', 'condition' => 'good', 'location' => 'Main storage', 'acquired_at' => now()->subYear()->toDateString(), 'purchase_cost' => '42000.00', 'capability' => 'Event Furniture'],
            ['asset_tag' => 'AST-SOUND-001', 'name' => 'Portable PA sound system', 'status' => 'available', 'condition' => 'good', 'location' => 'Main storage', 'acquired_at' => now()->subMonths(8)->toDateString(), 'purchase_cost' => '18500.00', 'capability' => 'Event Furniture'],
            ['asset_tag' => 'AST-OVEN-001', 'name' => 'Commercial convection oven', 'status' => 'in_use', 'condition' => 'good', 'location' => 'Kitchen', 'acquired_at' => now()->subMonths(18)->toDateString(), 'purchase_cost' => '32000.00', 'capability' => 'Buffet Catering'],
        ] as $row) {
            $asset = Asset::updateOrCreate(
                ['business_id' => $business->id, 'asset_tag' => $row['asset_tag']],
                collect($row)->except('capability')->merge(['currency' => 'ZAR', 'capability_id' => $capabilities[$row['capability']]->id, 'notes' => 'Seeded business asset for operational testing.'])->all()
            );
            if ($row['asset_tag'] === 'AST-TENT-001') {
                AssetAllocation::updateOrCreate(
                    ['business_id' => $business->id, 'asset_id' => $asset->id, 'event_id' => $event->id],
                    ['allocated_from' => now()->subDays(5)->toDateString(), 'allocated_until' => now()->subDays(1)->toDateString(), 'status' => 'released', 'notes' => 'Allocated for event setup and teardown.']
                );
            }
        }

        $supplier2 = Supplier::updateOrCreate(
            ['business_id' => $business->id, 'name' => 'Pretoria Catering Wholesale'],
            ['contact_name' => 'Thabo Maseko', 'email' => 'orders@pretoria-wholesale.example', 'phone' => '012 555 0198', 'notes' => 'Primary food and consumables supplier. 30-day terms.']
        );

        $purchaseOrder2 = PurchaseOrder::updateOrCreate(
            ['business_id' => $business->id, 'reference' => 'PO-ZAZU-DEMO-002'],
            [
                'event_id' => null,
                'supplier_id' => $supplier2->id,
                'idempotency_key' => 'demo-po-zazu-002',
                'reference' => 'PO-ZAZU-DEMO-002',
                'status' => 'ordered',
                'currency' => 'ZAR',
                'total_amount' => '1850.00',
                'ordered_at' => now()->subDay()->toDateString(),
                'expected_at' => now()->addDays(2)->toDateString(),
                'notes' => 'Open supplier order for general stock replenishment.',
            ]
        );
        $po2Item = PurchaseOrderItem::updateOrCreate(
            ['purchase_order_id' => $purchaseOrder2->id, 'description' => 'Consumables replenishment'],
            ['business_id' => $business->id, 'capability_id' => $capabilities['Buffet Catering']->id, 'quantity' => '1.00', 'received_quantity' => '0.00', 'unit' => 'lot', 'unit_price' => '1850.00', 'line_total' => '1850.00']
        );
        PurchaseOrderReceipt::updateOrCreate(
            ['business_id' => $business->id, 'purchase_order_id' => $purchaseOrder->id, 'idempotency_key' => 'demo-receipt-001'],
            []
        );

        foreach ([
            ['description' => 'Fresh produce and dry goods', 'amount' => '2800.00', 'reference' => 'EXP-DEMO-001', 'status' => 'paid', 'supplier_id' => $supplier->id, 'purchase_order_id' => $purchaseOrder->id],
            ['description' => 'Fuel and delivery expense', 'amount' => '420.00', 'reference' => 'EXP-DEMO-002', 'status' => 'paid', 'supplier_id' => null, 'purchase_order_id' => null],
            ['description' => 'Kitchen gas refill', 'amount' => '680.00', 'reference' => 'EXP-DEMO-003', 'status' => 'unpaid', 'supplier_id' => $supplier2->id, 'purchase_order_id' => null],
        ] as $expense) {
            FinanceExpense::updateOrCreate(
                ['business_id' => $business->id, 'idempotency_key' => 'demo-'.$expense['reference']],
                $expense + ['event_id' => $event->id, 'currency' => 'ZAR', 'expense_date' => now()->subDays(4)->toDateString(), 'notes' => 'Seeded operational expense for finance testing.']
            );
        }

        $travel = TravelCost::calculate('32.50', '23.50', '9.20', true, '8.50');
        TravelCost::updateOrCreate(
            ['event_id' => $event->id, 'route_label' => 'Kitchen to event venue'],
            [
                'currency' => 'ZAR',
                'provider' => 'Zazu Logistics',
                'origin' => 'Soshanguve kitchen',
                'destination' => 'Pretoria event venue',
                'distance_km' => $travel['distance_km'],
                'travel_time_minutes' => 55,
                'fuel_price_per_litre' => '23.50',
                'vehicle_consumption_l_per_100km' => '9.20',
                'round_trip' => true,
                'customer_rate_per_km' => '8.50',
                'total_distance_km' => $travel['total_distance_km'],
                'fuel_litres' => $travel['fuel_litres'],
                'fuel_cost' => $travel['fuel_cost'],
                'customer_charge' => $travel['customer_charge'],
                'notes' => 'Round trip delivery and collection.',
                'calculation_snapshot' => $travel,
            ]
        );

        foreach ([
            ['document_type' => 'company_registration', 'title' => 'Company registration certificate', 'reference_number' => '2024/123456/07', 'status' => 'current', 'expiry_date' => null, 'notes' => 'Demo compliance record.'],
            ['document_type' => 'tax', 'title' => 'VAT registration confirmation', 'reference_number' => '4123456789', 'status' => 'current', 'expiry_date' => null, 'notes' => 'Demo SARS tax record.'],
            ['document_type' => 'food_safety', 'title' => 'Food handling certificate', 'reference_number' => 'FHC-2026-0042', 'status' => 'current', 'expiry_date' => now()->addMonths(9)->toDateString(), 'notes' => 'Kitchen staff food-safety compliance record.'],
            ['document_type' => 'insurance', 'title' => 'Public liability insurance', 'reference_number' => 'PLI-2026-0188', 'status' => 'current', 'expiry_date' => now()->addMonths(6)->toDateString(), 'notes' => 'Public liability cover for event operations.'],
        ] as $document) {
            ComplianceDocument::updateOrCreate(
                ['business_id' => $business->id, 'title' => $document['title']],
                $document + ['issue_date' => now()->subMonths(3)->toDateString(), 'source_reference' => 'Zazu demo scenario', 'verified_at' => now()]
            );
        }

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
