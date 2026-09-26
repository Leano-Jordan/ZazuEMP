<x-skeleton-page
    eyebrow="System"
    title="Settings"
    description="Business configuration, workspace preferences and system controls will converge here."
>
    <section class="zazu-settings-grid">
        @foreach ([
            ['title'=>'Business profile','copy'=>'Business name, identity and operational defaults.'],
            ['title'=>'Workflow settings','copy'=>'Statuses, defaults and operational behaviour.'],
            ['title'=>'Notifications','copy'=>'Reminders, alerts and communication preferences.'],
            ['title'=>'Security & access','copy'=>'Users, roles, permissions and business isolation.'],
        ] as $item)
            <div class="zazu-card zazu-setting-card">
                <div class="zazu-card-title">{{ $item['title'] }}</div>
                <div class="zazu-card-description">{{ $item['copy'] }}</div>
                <span class="zazu-chip zazu-chip-neutral mt-4">Skeleton</span>
            </div>
        @endforeach
    </section>

    <div class="zazu-skeleton-actions">
        <a href="{{ route('dashboard') }}" class="zazu-btn zazu-btn-secondary">Dashboard</a>
        <a href="{{ route('capabilities.index') }}" class="zazu-btn zazu-btn-ghost">Capabilities →</a>
    </div>
</x-skeleton-page>
