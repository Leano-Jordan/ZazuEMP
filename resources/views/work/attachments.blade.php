<section class="zazu-panel" id="attachments">
    <div class="zazu-panel-head">
        <div>
            <div class="zazu-panel-title">Job files</div>
            <div class="zazu-panel-copy">Keep receipts, WhatsApp exports, screenshots, photos, contracts, menus and other client material with this job.</div>
        </div>
    </div>

    <form method="POST" action="{{ route('work.attachments.store', $event) }}" enctype="multipart/form-data" class="mt-4" data-zazu-offline-attachment-form data-zazu-event-id="{{ $event->id }}" data-zazu-server-attachment-ids="{{ $event->attachments->pluck('id')->implode(',') }}" data-zazu-no-submit-guard="1">
        @csrf
        <div class="zazu-dropzone">
            <input type="file" name="files[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.jpg,.jpeg,.png,.webp" class="zazu-file-input" required>
            <div class="zazu-dropzone-title">Add files to this job</div>
            <div class="zazu-dropzone-copy">Up to 10 files per upload, 20 MB each. WhatsApp text exports and screenshots are supported as job evidence.</div>
        </div>
        <div class="mt-3 flex flex-wrap justify-end">
            <button type="submit" class="zazu-btn zazu-btn-primary">Add to job</button>
        </div>
        <div data-zazu-offline-attachment-status class="zazu-panel-copy mt-3" role="status" aria-live="polite">
            Files selected while offline are saved on this phone and uploaded when Zazu reconnects.
        </div>
    </form>
    <div data-zazu-offline-attachment-list class="zazu-list mt-3" hidden></div>

    @if ($event->attachments->isNotEmpty())
        <div class="zazu-list mt-4">
            @foreach ($event->attachments as $attachment)
                <div class="zazu-list-item">
                    <div class="zazu-list-main">
                        <div class="zazu-list-title">{{ $attachment->original_name }}</div>
                        <div class="zazu-list-meta">{{ strtoupper(pathinfo($attachment->original_name, PATHINFO_EXTENSION) ?: 'FILE') }} · {{ number_format(($attachment->size ?? 0) / 1024, 0) }} KB · {{ $attachment->created_at->format('d M Y H:i') }}</div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('work.attachments.download', $attachment) }}" class="zazu-btn zazu-btn-secondary">Download</a>
                        <form method="POST" action="{{ route('work.attachments.destroy', $attachment) }}">
                            @csrf
                            @method('DELETE')
                            <button class="zazu-btn zazu-btn-ghost" type="submit">Remove</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="zazu-empty">
            <div class="zazu-empty-title">No job files yet</div>
            <p class="zazu-empty-copy">Add the material clients send you so the job record stays complete.</p>
        </div>
    @endif
</section>