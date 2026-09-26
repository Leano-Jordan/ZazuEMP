<section class="zazu-panel">
    <div class="zazu-panel-head">
        <div>
            <div class="zazu-panel-title">Job files</div>
            <div class="zazu-panel-copy">Keep receipts, WhatsApp exports, screenshots, photos, contracts, menus and other client material with this job.</div>
        </div>
    </div>

    <form method="POST" action="{{ route('work.attachments.store', $event) }}" enctype="multipart/form-data" class="mt-4">
        @csrf
        <div class="zazu-dropzone">
            <input type="file" name="files[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.jpg,.jpeg,.png,.webp" class="zazu-file-input" required>
            <div class="zazu-dropzone-title">Add files to this job</div>
            <div class="zazu-dropzone-copy">Up to 10 files per upload, 20 MB each. WhatsApp text exports and screenshots are supported as job evidence.</div>
        </div>
        <div class="mt-3 flex flex-wrap justify-end">
            <button class="zazu-btn zazu-btn-primary">Add to job</button>
        </div>
    </form>

    @if ($event->attachments->isNotEmpty())
        <div class="zazu-list mt-4">
            @foreach ($event->attachments as $attachment)
                <div class="zazu-list-item">
                    <div class="zazu-list-main">
                        <div class="zazu-list-title">{{ $attachment->original_name }}</div>
                        <div class="zazu-list-meta">{{ strtoupper(pathinfo($attachment->original_name, PATHINFO_EXTENSION) ?: 'FILE') }} · {{ number_format(($attachment->size ?? 0) / 1024, 0) }} KB · {{ $attachment->created_at->format('d M Y H:i') }}</div>
                    </div>
                    <form method="POST" action="{{ route('work.attachments.destroy', $attachment) }}">
                        @csrf
                        @method('DELETE')
                        <button class="zazu-btn zazu-btn-ghost" type="submit">Remove</button>
                    </form>
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