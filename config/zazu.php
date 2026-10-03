<?php

return [
    'platform_admin_emails' => array_values(array_filter(array_map('trim', explode(',', (string) env('ZAZU_PLATFORM_ADMIN_EMAILS', ''))))),
    'landing_image_library' => [
        'catering_service' => [
            'label' => 'Outdoor catered event',
            'url' => '/images/landing/hero.svg',
        ],
        'event_catering' => [
            'label' => 'Event catering service',
            'url' => '/images/landing/operations.svg',
        ],
        'sound_stage' => [
            'label' => 'Sound and stage setup',
            'url' => '/images/landing/resources.svg',
        ],
        'wedding_catering' => [
            'label' => 'Wedding catering',
            'url' => '/images/landing/control.svg',
        ],
        'luxury_banquet' => [
            'label' => 'Event venue and service',
            'url' => '/images/landing/hero.svg',
        ],
        'stage_av' => [
            'label' => 'Event stage and AV',
            'url' => '/images/landing/resources.svg',
        ],
    ],
    'errors' => [
        'AUTH-001' => ['category' => 'Authentication', 'severity' => 'medium', 'status' => 401, 'headline' => 'Sign-in required.', 'message' => 'Please sign in to continue.'],
        'AUTH-002' => ['category' => 'Authentication', 'severity' => 'medium', 'status' => 419, 'headline' => 'Your session has expired.', 'message' => 'Please refresh the page or sign in again to continue.'],
        'AUTHZ-001' => ['category' => 'Authorization', 'severity' => 'high', 'status' => 403, 'headline' => 'You do not have permission for that.', 'message' => 'Your current access level does not allow this action.'],
        'AUTHZ-002' => ['category' => 'Workspace', 'severity' => 'high', 'status' => 403, 'headline' => 'Your account is signed in, but no workspace is active.', 'message' => 'Zazu could not find an active business workspace for this account. Return to the public site to check your account state, or sign out and use another account.'],
        'VAL-001' => ['category' => 'Validation', 'severity' => 'low', 'status' => 422, 'headline' => 'Some information needs attention.', 'message' => 'Check the highlighted information and try again.'],
        'ROUTE-001' => ['category' => 'Routing', 'severity' => 'low', 'status' => 404, 'headline' => 'We could not find that page.', 'message' => 'The page may have moved, the record may no longer exist, or it may belong to another business workspace.'],
        'ROUTE-002' => ['category' => 'Routing', 'severity' => 'low', 'status' => 405, 'headline' => 'That action is not available here.', 'message' => 'This action is not available for this request.'],
        'DB-001' => ['category' => 'Database', 'severity' => 'critical', 'status' => 500, 'headline' => 'We could not load or save the requested data.', 'message' => 'Zazu could not complete a database operation. Please try again and use the reference below if the problem continues.'],
        'BUS-001' => ['category' => 'Business rule', 'severity' => 'medium', 'status' => 409, 'headline' => 'That action conflicts with the current work state.', 'message' => 'Review the record and try again.'],
        'FILE-001' => ['category' => 'File / storage', 'severity' => 'high', 'status' => 500, 'headline' => 'We could not access that file.', 'message' => 'Zazu could not access the requested file or storage. Please try again.'],
        'API-001' => ['category' => 'Integration / API', 'severity' => 'high', 'status' => 502, 'headline' => 'A connected service did not respond as expected.', 'message' => 'Please try again. The connected service may be temporarily unavailable.'],
        'CFG-001' => ['category' => 'Configuration', 'severity' => 'critical', 'status' => 500, 'headline' => 'Zazu could not use a required setting.', 'message' => 'The workspace could not complete this request. Please contact an administrator if it continues.'],
        'APP-001' => ['category' => 'Application', 'severity' => 'high', 'status' => 500, 'headline' => 'Zazu could not complete that request.', 'message' => 'We could not complete your request. Please try again, and use the reference below if the problem continues.'],
        'SYS-001' => ['category' => 'Server / infrastructure', 'severity' => 'critical', 'status' => 503, 'headline' => 'Zazu is temporarily unavailable.', 'message' => 'Please try again shortly. No technical details are exposed on this screen.'],
        'SYS-002' => ['category' => 'Server / infrastructure', 'severity' => 'medium', 'status' => 429, 'headline' => 'Please slow down for a moment.', 'message' => 'Too many requests were received. Please wait a moment and try again.'],
    ],

    'experience_levels' => [
        'basic' => [
            'label' => 'Basic',
            'description' => 'Keep Zazu focused on customers, jobs, essential commercial work and the next action.',
        ],
        'intermediate' => [
            'label' => 'Intermediate',
            'description' => 'Surface the operational flow across jobs, resources, purchasing, costs and finance.',
        ],
        'advanced' => [
            'label' => 'Advanced',
            'description' => 'Expose the full operational picture, controls, deeper search and more detailed guidance.',
        ],
    ],

    /*
     * Primary business focus is a presentation preference. It does not change
     * permissions or remove the underlying business capabilities.
     */
    'niches' => [
        'chairs_tents' => [
            'label' => 'Chairs & tents',
            'description' => 'Keep hire bookings, availability, delivery/setup and money close to the work.',
        ],
        'catering_baking' => [
            'label' => 'Catering & baking',
            'description' => 'Keep food service, quantities, preparation, costs and money close to the work.',
        ],
        'sound_dj' => [
            'label' => 'Sound & DJ',
            'description' => 'Keep bookings, equipment, setup/travel and money close to the work.',
        ],
        'mixed' => [
            'label' => 'Mixed event services',
            'description' => 'Keep the shared event workflow visible without prioritising one service type.',
        ],
    ],

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
        'bottle' => 'Bottle',
        'can' => 'Can',
        'crate' => 'Crate',
        'jug' => 'Jug',
        'case' => 'Case',
        'serving' => 'Serving',
    ],

    'beverage_sizes' => [
        '250ml',
        '300ml',
        '330ml',
        '440ml',
        '500ml',
        '750ml',
        '1L',
        '1.25L',
        '1.5L',
        '2L',
        '5L',
        '20L',
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
            'Cooldrinks',
            'Bottled water',
            'Juices',
            'Homemade juices',
            'Ginger ale',
            'Homemade beer (Mqombothi)',
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

    'permissions' => [
        'roles' => [
            'staff' => [
                'dashboard.view', 'calendar.view',
                'work.view', 'work.create', 'work.update', 'work.delete',
                'customers.view', 'customers.create', 'customers.update', 'customers.contacts.manage',
                'quotes.view', 'quotes.create', 'quotes.update', 'quotes.status',
                'finance.view',
                'purchasing.view', 'purchasing.create', 'purchasing.status',
                'inventory.view', 'inventory.create', 'inventory.movement',
                'assets.view', 'assets.create', 'assets.update', 'assets.allocate', 'assets.release',
                'suppliers.view', 'suppliers.create', 'suppliers.update',
                'reports.view', 'capabilities.view',
            ],
            'manager' => [
                'dashboard.view', 'calendar.view',
                'work.view', 'work.create', 'work.update',
                'customers.view', 'customers.create', 'customers.update', 'customers.contacts.manage',
                'quotes.view', 'quotes.create', 'quotes.update', 'quotes.status',
                'finance.view',
                'purchasing.view', 'purchasing.create', 'purchasing.status',
                'inventory.view', 'inventory.create', 'inventory.movement',
                'assets.view', 'assets.create', 'assets.update', 'assets.allocate', 'assets.release',
                'suppliers.view', 'suppliers.create', 'suppliers.update',
                'reports.view', 'capabilities.view',
            ],
        ],
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