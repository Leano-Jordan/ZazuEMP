<?php

return [
    'currencies' => [
        'ZAR' => 'South African rand (ZAR)',
        'BWP' => 'Botswana pula (BWP)',
        'EUR' => 'Euro (EUR)',
        'GBP' => 'Pound sterling (GBP)',
        'USD' => 'US dollar (USD)',
    ],

    'units' => [
        'service' => 'Service',
        'unit' => 'Unit',
        'item' => 'Item',
        'person' => 'Person',
        'guest' => 'Guest',
        'chair' => 'Chair',
        'table' => 'Table',
        'plate' => 'Plate',
        'hour' => 'Hour',
        'day' => 'Day',
        'kilometre' => 'Kilometre',
        'litre' => 'Litre',
    ],

    'cost_categories' => [
        'Food' => 'Food',
        'Transport' => 'Transport',
        'Venue' => 'Venue',
        'Staff' => 'Staff',
        'Equipment' => 'Equipment',
        'Supplies' => 'Supplies',
        'Accommodation' => 'Accommodation',
        'Fees' => 'Fees',
        'Other' => 'Other',
    ],

    'readiness_categories' => [
        'Food' => 'Food',
        'Equipment' => 'Equipment',
        'Staff' => 'Staff',
        'Venue' => 'Venue',
        'Transport' => 'Transport',
        'Documents' => 'Documents',
        'Other' => 'Other',
    ],

    'tax' => [
        'regimes' => [
            'standard_income_tax' => 'Standard income tax',
            'turnover_tax' => 'Turnover tax',
            'other' => 'Other / specialist regime',
        ],
        'vat_statuses' => [
            'not_registered' => 'Not VAT registered',
            'registered' => 'VAT registered',
            'exempt' => 'VAT exempt / special treatment',
        ],
        'treatments' => [
            'standard' => 'Standard-rated',
            'zero_rated' => 'Zero-rated',
            'exempt' => 'Exempt',
            'out_of_scope' => 'Out of scope',
        ],
        'default_standard_rate' => '15.00',
        'sources' => [
            'sars_vat' => 'SARS VAT guidance',
        ],
    ],

    'compliance' => [
        'activity_flags' => [
            'food_handling' => 'Handles or prepares food',
            'employees' => 'Employs staff',
            'government_supply' => 'Supplies or plans to supply government',
            'tendering' => 'Prepares bids / tenders',
            'regulated_activity' => 'Other regulated activity',
        ],
        'document_types' => [
            'cipc_registration' => 'CIPC registration / incorporation proof',
            'cipc_annual_return' => 'CIPC annual return proof',
            'cipc_beneficial_ownership' => 'CIPC beneficial ownership records',
            'sars_income_tax' => 'SARS income tax registration / reference',
            'sars_tcs_good_standing' => 'SARS Tax Compliance Status (Good Standing)',
            'vat_certificate' => 'VAT registration certificate',
            'bbbee' => 'B-BBEE certificate / affidavit where applicable',
            'csd_registration' => 'Central Supplier Database (CSD) registration/report',
            'uif_registration' => 'UIF registration',
            'sdl_registration' => 'SDL registration where applicable',
            'compensation_fund' => 'Compensation Fund / COID evidence where applicable',
            'food_certificate' => 'Certificate of Acceptability for food premises where applicable',
            'business_licence' => 'Municipal business licence where applicable',
            'bank_confirmation' => 'Bank confirmation / banking proof',
            'proof_of_address' => 'Business proof of address',
            'owner_id' => 'Owner/director/member identification',
            'insurance' => 'Insurance / public liability evidence where required',
            'tender_forms' => 'Tender-specific declarations and MBD forms',
            'other' => 'Other compliance document',
        ],
    ],

    'service_categories' => [
        'Catering' => [
            'Catering',
            'Buffet',
            'Plated meals',
            'Snacks & platters',
            'Drinks & refreshments',
        ],
        'Decor' => [
            'Decor',
            'Flowers',
            'Draping',
            'Table settings',
            'Lighting decor',
        ],
        'Sound & entertainment' => [
            'Sound system',
            'DJ',
            'MC',
            'Live music',
        ],
        'Furniture & equipment' => [
            'Chairs',
            'Tables',
            'Tents',
            'Crockery & cutlery',
            'Equipment hire',
        ],
        'Photography & video' => [
            'Photography',
            'Camera hire',
            'Videography',
        ],
        'Baking' => [
            'Wedding cake',
            'Birthday cake',
            'Cookies & treats',
        ],
        'Transport' => [
            'Passenger transport',
            'Delivery',
            'Equipment transport',
        ],
        'Staff' => [
            'Event staff',
            'Waiters',
            'Setup & cleanup',
            'Security',
        ],
        'Venue' => [
            'Venue',
            'Venue setup',
        ],
        'Other' => [],
    ],

    'job_types' => [
        'Wedding',
        'Funeral',
        'Birthday',
        'Corporate event',
        'Party',
        'Conference',
        'Meeting',
        'Equipment hire',
        'Catering order',
        'Other',
    ],
];