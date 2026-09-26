<x-skeleton-page
    eyebrow="Operations"
    title="Calendar"
    description="A work-first calendar for dates, preparation windows, event days and follow-up timing."
>
    <section class="zazu-calendar-shell">
        <div class="zazu-card-header">
            <div class="zazu-card-title">October 2026</div>
            <div class="zazu-card-description">Skeleton calendar surface. Real work records will populate the grid later.</div>
        </div>
        <div class="zazu-calendar-grid">
            @foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day)
                <div class="zazu-calendar-label">{{ $day }}</div>
            @endforeach
            @for ($i = 1; $i <= 35; $i++)
                <div class="zazu-calendar-cell">
                    @if ($i <= 31)
                        <span class="zazu-calendar-date">{{ $i }}</span>
                        @if (in_array($i, [3, 8, 14, 21, 27]))
                            <span class="zazu-calendar-event">Work</span>
                        @endif
                    @endif
                </div>
            @endfor
        </div>
    </section>

    <div class="zazu-skeleton-actions">
        <a href="{{ route('work.index') }}" class="zazu-btn zazu-btn-secondary">Open work</a>
        <a href="{{ route('quotes.index') }}" class="zazu-btn zazu-btn-ghost">Quotes →</a>
    </div>
</x-skeleton-page>
