<x-app-layout>
    <x-slot:title>Dashboard</x-slot:title>
    <x-slot:heading>Dashboard</x-slot:heading>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Overview</div>
            <h2 class="zazu-command-title">Your operating desk</h2>
            <p class="zazu-command-copy">A single starting point for the work, relationships, commercial activity and resources that keep the business moving.</p>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">Workspace</div>
            <div class="zazu-command-meta-value">Zazu</div>
        </div>
    </section>

    <section class="zazu-module-grid">
        @php
            $modules = [
                ['label' => 'Work', 'copy' => 'Events, jobs and operational workspaces.', 'route' => 'work.index', 'group' => 'Operations'],
                ['label' => 'Customers', 'copy' => 'Relationships, contacts and work history.', 'route' => 'customers.index', 'group' => 'Relationships'],
                ['label' => 'Quotes', 'copy' => 'Commercial offers and quote versions.', 'route' => 'quotes.index', 'group' => 'Commercial'],
                ['label' => 'Calendar', 'copy' => 'Upcoming work, dates and operational timing.', 'route' => 'calendar.index', 'group' => 'Operations'],
                ['label' => 'Suppliers', 'copy' => 'Buying relationships and supplier records.', 'route' => 'suppliers.index', 'group' => 'Resources'],
                ['label' => 'Inventory', 'copy' => 'Stock, availability and movement.', 'route' => 'inventory.index', 'group' => 'Resources'],
                ['label' => 'Assets', 'copy' => 'Reusable equipment and accountability.', 'route' => 'assets.index', 'group' => 'Resources'],
                ['label' => 'Reports', 'copy' => 'Operational and commercial visibility.', 'route' => 'reports.index', 'group' => 'Insights'],
                ['label' => 'Settings', 'copy' => 'Business configuration and system controls.', 'route' => 'settings.index', 'group' => 'System'],
                ['label' => 'Capabilities', 'copy' => 'Reusable services, rentals and products.', 'route' => 'capabilities.index', 'group' => 'Catalogue'],
            ];
        @endphp

        @foreach ($modules as $module)
            <a href="{{ route($module['route']) }}" class="zazu-module">
                <div class="zazu-module-top">
                    <span class="zazu-module-group">{{ $module['group'] }}</span>
                    <span class="zazu-module-arrow">→</span>
                </div>
                <div class="zazu-module-title">{{ $module['label'] }}</div>
                <div class="zazu-module-copy">{{ $module['copy'] }}</div>
            </a>
        @endforeach
    </section>
</x-app-layout>
