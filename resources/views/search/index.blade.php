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

    <form method="GET" action="{{ route('search.index') }}" class="zazu-form zazu-search-page-form mb-6">
        <div class="zazu-search-primary">
            <span class="zazu-search-icon" aria-hidden="true">⌕</span>
            <label class="sr-only" for="workspace-search-page-input">Search the workspace</label>
            <input id="workspace-search-page-input" class="zazu-search-page-input" type="search" name="q" value="{{ $filters['q'] }}" autofocus placeholder="Search customers, jobs, suppliers, invoices or quotes…" autocomplete="off">
            <kbd>⌘K</kbd>
        </div>

        <div class="zazu-search-filter-row">
            <label class="zazu-search-filter">
                <span>Record type</span>
                <select class="zazu-input" name="type">
                    <option value="">All records</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" @selected($filters['type'] === $type)>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="zazu-search-filter">
                <span>Status</span>
                <select class="zazu-input" name="status">
                    <option value="">Any status</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="zazu-search-filter">
                <span>From</span>
                <input class="zazu-input" type="date" name="from" value="{{ $filters['from'] }}">
            </label>
            <label class="zazu-search-filter">
                <span>To</span>
                <input class="zazu-input" type="date" name="to" value="{{ $filters['to'] }}">
            </label>
        </div>

        <div class="zazu-search-form-footer">
            <span>Search stays inside this workspace and respects your current access.</span>
            <div>
                <a href="{{ route('search.index') }}" class="zazu-btn zazu-btn-ghost">Clear</a>
                <button type="submit" class="zazu-btn zazu-btn-primary">Search workspace <span>→</span></button>
            </div>
        </div>
    </form>

    <section class="zazu-panel zazu-search-results-panel">
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
            <div class="zazu-search-results-list">
                @foreach($results as $result)
                    <a href="{{ $result['href'] }}" class="zazu-search-result">
                        <span class="zazu-search-result-mark" aria-hidden="true"></span>
                        <div class="zazu-search-result-main">
                            <div class="zazu-search-result-type">{{ $result['type_label'] }}</div>
                            <strong>{{ $result['title'] }}</strong>
                            <span>{{ $result['meta'] }}</span>
                        </div>
                        <div class="zazu-search-result-side">
                            @if($result['status'])<span class="zazu-search-result-status">{{ str_replace('_', ' ', $result['status']) }}</span>@endif
                            @if($result['date'])<time>{{ $result['date'] }}</time>@endif
                            <b aria-hidden="true">→</b>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</x-app-layout>
