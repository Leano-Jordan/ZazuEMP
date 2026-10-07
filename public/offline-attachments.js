(() => {
    const DB = 'zazu-phone-attachments';
    const STORE = 'files';

    function openAttachmentDb() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB, 1);
            request.onupgradeneeded = () => {
                const db = request.result;
                if (!db.objectStoreNames.contains(STORE)) {
                    db.createObjectStore(STORE, { keyPath: 'idempotency_key' });
                }
            };
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async function putFile(record) {
        const db = await openAttachmentDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE, 'readwrite');
            tx.objectStore(STORE).put(record);
            tx.oncomplete = resolve;
            tx.onerror = () => reject(tx.error);
        });
    }

    async function getFiles() {
        const db = await openAttachmentDb();
        return new Promise((resolve, reject) => {
            const request = db.transaction(STORE).objectStore(STORE).getAll();
            request.onsuccess = () => resolve(request.result || []);
            request.onerror = () => reject(request.error);
        });
    }

    async function removeFile(idempotencyKey) {
        const db = await openAttachmentDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE, 'readwrite');
            tx.objectStore(STORE).delete(idempotencyKey);
            tx.oncomplete = resolve;
            tx.onerror = () => reject(tx.error);
        });
    }

    function message(text) {
        const state = document.getElementById('state');
        if (state) state.textContent = text;
    }

    async function upload(record) {
        if (!navigator.onLine || !data?.sync?.token || !record.file) return false;

        const form = new FormData();
        form.append('event_id', String(record.event_id));
        form.append('idempotency_key', record.idempotency_key);
        if (record.description) form.append('description', record.description);
        form.append('file', record.file, record.original_name);

        const response = await syncFetch('/api/sync/attachments', { method: 'POST', body: form });
        if (!response?.ok) return false;

        const result = await response.json();
        await putFile({ ...record, status: 'synced', attachment: result.attachment, file: record.file });
        return true;
    }

    async function flush() {
        if (!navigator.onLine || !data?.sync?.token) return;
        const files = await getFiles();
        for (const record of files.filter(x => x.status === 'pending')) {
            try {
                if (await upload(record)) message('Offline attachment synced');
            } catch (e) {}
        }
        if (typeof refreshQueueState === 'function') refreshQueueState();
    }

    async function downloadForEvent(eventId) {
        if (!navigator.onLine || !data?.sync?.token || !eventId) return;
        const response = await syncFetch('/api/sync/attachments?event_id=' + encodeURIComponent(eventId));
        if (!response?.ok) return;
        const result = await response.json();
        for (const attachment of result.attachments || []) {
            const existing = (await getFiles()).find(x => x.attachment?.id === attachment.id);
            if (existing) continue;
            try {
                const fileResponse = await syncFetch('/api/sync/attachments/' + attachment.id);
                if (!fileResponse?.ok) continue;
                const blob = await fileResponse.blob();
                await putFile({
                    idempotency_key: attachment.idempotency_key || ('server-' + attachment.id),
                    event_id: attachment.event_id,
                    original_name: attachment.original_name,
                    description: attachment.description,
                    status: 'synced',
                    attachment,
                    file: blob
                });
            } catch (e) {}
        }
    }

    function panel() {
        const content = document.getElementById('content');
        if (!content) return;
        const events = (data?.events || []).filter(x => Number(x.id) > 0);
        content.innerHTML = '<section class="panel"><h2>Offline attachments</h2>' +
            '<div class="notice">Files are kept on this phone. New files can be queued without internet and uploaded automatically when Zazu reconnects.</div>' +
            '<form class="form" id="attachment-form" style="margin-top:12px">' +
            '<label>Job<select id="attachment-event" required>' +
            events.map(x => '<option value="' + esc(x.id) + '">' + esc(x.name || x.reference || ('Job ' + x.id)) + '</option>').join('') +
            '</select></label>' +
            '<label>File<input id="attachment-file" type="file" required accept="image/jpeg,image/png,image/webp,application/pdf,text/plain,text/csv,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"></label>' +
            '<label>Description<input id="attachment-description"></label>' +
            '<div class="form-actions"><button class="button" type="submit">Save attachment</button><button class="button secondary" type="button" id="attachment-cancel">Back</button></div>' +
            '</form><div class="list" id="attachment-list" style="margin-top:12px"></div></section>';

        document.getElementById('attachment-cancel').onclick = render;
        document.getElementById('attachment-form').onsubmit = async event => {
            event.preventDefault();
            const file = document.getElementById('attachment-file').files[0];
            const eventId = Number(document.getElementById('attachment-event').value);
            if (!file || !eventId) return;
            if (file.size > 20 * 1024 * 1024) {
                message('Attachment is limited to 20 MB');
                return;
            }
            const record = {
                idempotency_key: crypto.randomUUID(),
                event_id: eventId,
                original_name: file.name,
                description: document.getElementById('attachment-description').value.trim(),
                status: 'pending',
                file
            };
            await putFile(record);
            if (await upload(record)) message('Attachment uploaded and stored on phone');
            else message('Attachment saved on phone · upload will resume when online');
            await listAttachments();
        };
        listAttachments();
    }

    async function listAttachments() {
        const target = document.getElementById('attachment-list');
        if (!target) return;
        const eventId = Number(document.getElementById('attachment-event')?.value || 0);
        const files = (await getFiles()).filter(x => Number(x.event_id) === eventId);
        target.innerHTML = files.length ? files.map((x, i) =>
            '<div class="row"><div><strong>' + esc(x.original_name) + '</strong><span>' +
            esc(x.status === 'pending' ? 'Waiting for connection' : 'Stored on this phone') +
            '</span></div><button class="button secondary" data-attachment-index="' + i + '">Open</button></div>'
        ).join('') : '<div class="empty">No local attachments for this job.</div>';

        target.querySelectorAll('[data-attachment-index]').forEach(button => {
            button.onclick = async () => {
                const current = (await getFiles()).filter(x => Number(x.event_id) === eventId)[Number(button.dataset.attachmentIndex)];
                if (!current?.file) return;
                const url = URL.createObjectURL(current.file);
                window.open(url, '_blank', 'noopener');
                setTimeout(() => URL.revokeObjectURL(url), 60000);
            };
        });
    }

    function addAction() {
        const actions = document.querySelector('.hero-actions');
        if (!actions || document.getElementById('offline-attachments-action')) return;
        const button = document.createElement('button');
        button.id = 'offline-attachments-action';
        button.className = 'button secondary';
        button.textContent = 'Attachments';
        button.onclick = panel;
        actions.appendChild(button);
    }

    const originalRender = window.render;
    if (typeof originalRender === 'function') {
        window.render = function () {
            originalRender();
            addAction();
        };
    }

    window.addEventListener('online', flush);
    setTimeout(() => {
        addAction();
        flush();
    }, 0);
})();