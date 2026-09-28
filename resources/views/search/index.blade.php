<x-app-layout>
    <x-slot:title>Search</x-slot:title>
    <x-slot:heading>Workspace search</x-slot:heading>

    <section class="zazu-page-intro">
        <div>
            <span class="zazu-eyebrow">Find records</span>
            <h2>Search across the workspace.</h2>
            <p>Results stay inside the active business context and are limited to records your current access level can see.</p>
        </div>
    </section>

    <form method="GET" action="{{ route('search.index') }}" class="zazu-form mb-6">
        <div class="zazu-form-grid">
            <label class="zazu-field zazu-field-wide">
                <span class="zazu-label">Search</span>
                <input class="zazu-input" type="search" name="q" value="{{ $filters['q'] }}" autofocus placeholder="Customer, job, supplier, invoice, quote…">
            </label>
            <label class="zazu-field">
                <span class="zazu-label">Record type</span>
                <select class="zazu-input" name="type">
                    <option value="">All record types</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" @selected($filters['type'] === $type)>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="zazu-field">
                <span class="zazu-label">Status</span>
                <select class="zazu-input" name="status">
                    <option value="">Any status</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="zazu-field">
                <span class="zazu-label">From</span>
                <input class="zazu-input" type="date" name="from" value="{{ $filters['from'] }}">
            </label>
            <label class="zazu-field">
                <span class="zazu-label">To</span>
                <input class="zazu-input" type="date" name="to" value="{{ $filters['to'] }}">
            </label>
        </div>
        <div class="zazu-actionbar mt-5">
            <a href="{{ route('search.index') }}" class="zazu-btn zazu-btn-secondary">Clear</a>
            <button type="submit" class="zazu-btn zazu-btn-primary">Search workspace</button>
        </div>
    </form>

    <section class="zazu-panel">
        <header class="zazu-section-head">
            <div>
                <span class="zazu-eyebrow">Results</span>
                <h3>{{ count($results) }} record{{ count($results) === 1 ? '' : 's' }}</h3>
            </div>
        </header>

        @if(empty($results))
            <div class="zazu-empty-state">
                <strong>No matching records.</strong>
                <span>Try a broader name, reference, status or date range.</span>
            </div>
        @else
            <div class="divide-y">
                @foreach($results as $result)
                    <a href="{{ $result['href'] }}" class="flex items-center justify-between gap-4 px-4 py-4 hover:bg-[var(--zazu-blue-soft)]">
                        <div class="min-w-0">
                            <div class="text-xs uppercase tracking-wide opacity-70">{{ $result['type_label'] }}</div>
                            <strong class="block truncate">{{ $result['title'] }}</strong>
                            <span class="block text-sm opacity-75 truncate">{{ $result['meta'] }}</span>
                        </div>
                        <div class="text-right shrink-0">
                            @if($result['status'])<div class="text-xs uppercase tracking-wide opacity-70">{{ str_replace('_', ' ', $result['status']) }}</div>@endif
                            @if($result['date'])<div class="text-sm opacity-75">{{ $result['date'] }}</div>@endif
                            <span class="text-sm">Open →</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</x-app-layout>
