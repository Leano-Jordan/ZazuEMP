<x-app-layout>
    <x-slot:title>Customers</x-slot:title>
    <x-slot:heading>Customers</x-slot:heading>
    <x-slot:headerAction>
        <a href="{{ route('customers.create') }}" class="zazu-btn zazu-btn-primary">New customer</a>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Relationships</div>
            <h2 class="zazu-command-title">Customer directory</h2>
            <p class="zazu-command-copy">Keep the people and organisations behind your work in one place, ready to become operational records.</p>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">Customers</div>
            <div class="zazu-command-meta-value">{{ $customers->total() }}</div>
        </div>
    </section>

    <section class="zazu-card zazu-list">
        <div class="zazu-card-header">
            <div class="zazu-card-title">Your customers</div>
            <div class="zazu-card-description">Select a customer to start a new piece of work.</div>
        </div>

        @forelse ($customers as $customer)
            <a href="{{ route('work.create', ['customer_id' => $customer->id]) }}" class="zazu-list-item">
                <div class="zazu-row-with-avatar">
                    <div class="zazu-avatar">{{ IlluminateSupportStr::upper(IlluminateSupportStr::substr($customer->name, 0, 1)) }}</div>
                    <div class="zazu-list-main">
                        <div class="zazu-list-title">{{ $customer->name }}</div>
                        <div class="zazu-list-meta">
                            {{ $customer->primaryContact?->phone ?? 'No phone' }}
                            @if ($customer->primaryContact?->email) · {{ $customer->primaryContact->email }} @endif
                        </div>
                    </div>
                </div>
                <div class="zazu-list-side">
                    <div class="zazu-side-primary">{{ $customer->events_count }} {{ $customer->events_count === 1 ? 'work item' : 'work items' }}</div>
                    <div class="zazu-side-secondary">Start work →</div>
                </div>
            </a>
        @empty
            <div class="zazu-empty">
                <div class="zazu-empty-title">No customers yet</div>
                <p class="zazu-empty-copy">Create the first customer record, then use it as the starting point for a work workspace.</p>
                <a href="{{ route('customers.create') }}" class="zazu-btn zazu-btn-primary mt-5">Add customer</a>
            </div>
        @endforelse

        @if ($customers->hasPages())
            <div class="border-t border-[var(--zazu-border)] px-5 py-4">{{ $customers->links() }}</div>
        @endif
    </section>
</x-app-layout>
