<x-app-layout>
    <x-slot:title>Activity audit</x-slot:title>
    <x-slot:heading>Activity audit</x-slot:heading>

    <section class="zazu-command-band">
        <div>
            <div class="zazu-eyebrow">Control layer</div>
            <h2 class="zazu-command-title">Operational activity</h2>
            <p class="zazu-command-copy">A business-scoped record of important commercial and setup actions. Audit records are append-only from the application UI.</p>
        </div>
    </section>

    <section class="zazu-card zazu-list mt-5">
        <div class="zazu-card-header">
            <div>
                <div class="zazu-card-title">Recent activity</div>
                <div class="zazu-card-description">Most recent actions appear first.</div>
            </div>
        </div>
        @forelse($logs as $log)
            <div class="zazu-list-item">
                <div class="zazu-list-main">
                    <div class="zazu-list-title">{{ $log->action }}</div>
                    <div class="zazu-list-meta">
                        {{ $log->user?->name ?? 'System' }}
                        · {{ $log->created_at?->format('d M Y H:i:s') }}
                        @if($log->subject_type && $log->subject_id)
                            · {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                        @endif
                    </div>
                </div>
                <div class="zazu-list-side">
                    <span class="zazu-chip zazu-chip-neutral">{{ $log->request_id ?: 'No request ID' }}</span>
                </div>
            </div>
        @empty
            <div class="zazu-empty">
                <div class="zazu-empty-title">No activity recorded yet</div>
                <p class="zazu-empty-copy">Important Zazu actions will appear here as they occur.</p>
            </div>
        @endforelse
    </section>

    <div class="mt-5">{{ $logs->links() }}</div>
</x-app-layout>
