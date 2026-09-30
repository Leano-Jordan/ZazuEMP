<x-app-layout>
    <x-slot:title>Import customers</x-slot:title>
    <x-slot:heading>Import customers</x-slot:heading>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Bring existing records into Zazu</div>
            <h2 class="zazu-command-title">Customer spreadsheet import</h2>
            <p class="zazu-command-copy">Upload a CSV or XLSX file. Zazu checks the rows before anything is saved.</p>
        </div>
    </section>

    @if (empty($preview))
        <section class="zazu-card p-6">
            <form method="POST" action="{{ route('customers.import.preview') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div>
                    <label for="spreadsheet" class="zazu-eyebrow block mb-2">Spreadsheet</label>
                    <input id="spreadsheet" name="spreadsheet" type="file" accept=".csv,.xlsx" required class="block w-full">
                    <p class="text-sm mt-2">CSV or XLSX, up to 5 MB and 5,000 rows.</p>
                </div>
                <button type="submit" class="zazu-btn zazu-btn-primary">Review import</button>
            </form>
        </section>
    @else
        <section class="zazu-card p-6 mb-5">
            <div class="zazu-section-heading">
                <div>
                    <div class="zazu-eyebrow">Review before saving</div>
                    <div class="zazu-card-title mt-1">{{ $preview['summary']['total'] }} rows found</div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mt-5">
                <div class="p-3 border rounded"><strong>{{ $preview['summary']['new'] }}</strong><div>New</div></div>
                <div class="p-3 border rounded"><strong>{{ $preview['summary']['existing'] }}</strong><div>Existing</div></div>
                <div class="p-3 border rounded"><strong>{{ $preview['summary']['duplicate'] }}</strong><div>Duplicate</div></div>
                <div class="p-3 border rounded"><strong>{{ $preview['summary']['needs_review'] }}</strong><div>Needs review</div></div>
                <div class="p-3 border rounded"><strong>{{ $preview['summary']['needs_attention'] }}</strong><div>Needs attention</div></div>
            </div>
        </section>

        <form method="POST" action="{{ route('customers.import.store') }}">
            @csrf
            <section class="zazu-card zazu-list">
                @foreach ($preview['rows'] as $row)
                    @php
                        $matchStatus = $row['match']['status'];
                        $blocked = in_array($matchStatus, ['duplicate', 'needs_review'], true) || $row['status'] !== 'ready';
                    @endphp
                    <div class="zazu-list-item items-start">
                        <div class="min-w-0 flex-1">
                            <div class="zazu-list-title">{{ $row['customer']['name'] ?? 'Unnamed customer' }}</div>
                            <div class="zazu-list-meta">
                                Row {{ $row['row_number'] }}
                                · {{ $matchStatus === 'not_checked' ? 'Not checked' : str_replace('_', ' ', ucfirst($matchStatus)) }}
                                @if ($row['customer']['legal_name']) · {{ $row['customer']['legal_name'] }} @endif
                            </div>

                            @if ($row['issues'])
                                <ul class="mt-2 text-sm">
                                    @foreach ($row['issues'] as $issue)
                                        <li>{{ is_array($issue) ? $issue['message'] : $issue }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            @if ($row['match']['message'])
                                <p class="mt-2 text-sm">{{ $row['match']['message'] }}</p>
                            @endif
                        </div>

                        <div class="shrink-0">
                            @if ($blocked)
                                <span class="zazu-btn zazu-btn-ghost opacity-60">Resolve first</span>
                            @elseif ($matchStatus === 'new')
                                <select name="actions[{{ $row['row_number'] }}]" class="zazu-input">
                                    <option value="create">Create customer</option>
                                </select>
                            @else
                                <select name="actions[{{ $row['row_number'] }}]" class="zazu-input">
                                    <option value="skip">Skip existing</option>
                                </select>
                            @endif
                        </div>
                    </div>
                @endforeach
            </section>

            @if ($preview['summary']['duplicate'] === 0 && $preview['summary']['needs_review'] === 0 && $preview['summary']['needs_attention'] === 0)
                <div class="mt-5 flex gap-3">
                    <button type="submit" class="zazu-btn zazu-btn-primary">Import approved rows</button>
                    <a href="{{ route('customers.import.create') }}" class="zazu-btn zazu-btn-ghost">Start over</a>
                </div>
            @else
                <div class="mt-5">
                    <a href="{{ route('customers.import.create') }}" class="zazu-btn zazu-btn-secondary">Upload a corrected file</a>
                </div>
            @endif
        </form>
    @endif
</x-app-layout>
