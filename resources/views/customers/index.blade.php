<x-app-layout>
    <x-slot:title>Customers</x-slot:title>
    <x-slot:heading>Customers</x-slot:heading>
    <x-slot:headerAction>
        <div class="flex gap-2">
            <a href="{{ route('customers.import.create') }}" class="zazu-btn zazu-btn-secondary">Import</a>
            <a href="{{ route('customers.create') }}" class="zazu-btn zazu-btn-primary">New customer</a>
        </div>
    </x-slot:headerAction>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Customers</div>
            <h2 class="zazu-command-title">Your customer directory</h2>
            <p class="zazu-command-copy">People and organisations connected to your events and work.</p>
        </div>
        <div class="zazu-command-meta">
            <div class="zazu-command-meta-label">Customers</div>
            <div class="zazu-command-meta-value">{{ $customers->total() }}</div>
        </div>
    </section>

    <section class="zazu-card zazu-list zazu-customer-directory">        <div class="zazu-card-header">
            <div class="zazu-section-heading">
                <div>
                    <div class="zazu-eyebrow">Directory</div>
                    <div class="zazu-card-title mt-1">Customers</div>
                    <div class="zazu-card-description">Open a customer to see their relationship and connected work.</div>
                </div>
            </div>
        </div>

        @if ($customers->count())
            <div class="zazu-record-header" data-record-cols="2">
                <div class="zazu-record-header-note">Customer</div>
                <div class="zazu-record-header-cell">Work</div>
                <div class="zazu-record-header-cell">Actions</div>
            </div>
        @endif

        @forelse ($customers as $customer)
            <div class="zazu-list-item">
                <a href="{{ route('customers.show', $customer) }}" class="zazu-row-with-avatar min-w-0 flex-1">
                    <x-profile-avatar :name="$customer->name" :path="$customer->profile_photo_path" media-type="customer" :media-id="$customer->id" size="sm" />
                    <div class="zazu-list-main">
                        <div class="zazu-list-title">{{ $customer->name }}</div>
                        <div class="zazu-list-meta">
                            {{ $customer->primaryContact?->phone ?? 'No phone' }}
                            @if ($customer->primaryContact?->email) · {{ $customer->primaryContact->email }} @endif
                        </div>
                    </div>
                </a>
                <div class="zazu-list-side">
                    <div class="zazu-side-primary">{{ $customer->events_count }} {{ $customer->events_count === 1 ? 'work item' : 'work items' }}</div>
                </div>
                <div class="zazu-action-group">
                    <a href="{{ route('work.create', ['customer_id' => $customer->id]) }}" class="zazu-btn zazu-btn-secondary">Start work</a>
                    <a href="{{ route('customers.show', $customer) }}" class="zazu-btn zazu-btn-ghost">Open</a>
                </div>
            </div>
        @empty
            <div class="zazu-empty">
                <div class="zazu-empty-title">No customers yet</div>
                <p class="zazu-empty-copy">Create the first customer record, then use it as the starting point for a work workspace.</p>
                <a href="{{ route('customers.create') }}" class="zazu-btn zazu-btn-primary mt-5">Add customer</a>
            </div>
        @endforelse

        @if ($customers->hasPages())
            <div class="px-5 py-4 pt-4">{{ $customers->links() }}</div>
        @endif
    </section>
</x-app-layout>