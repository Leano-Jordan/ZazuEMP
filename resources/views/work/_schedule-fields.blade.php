@php($scheduleEvent = $event ?? null)
<label class="zazu-field">
    <span class="zazu-label">Job start date <span class="zazu-required">*</span></span>
    <input id="job-start-date" type="date" name="event_date" value="{{ old('event_date', $scheduleEvent?->event_date?->format('Y-m-d')) }}" required class="zazu-input">
    <span class="zazu-field-help" id="job-start-date-help">The first day of the event.</span>
    @error('event_date')<span class="zazu-field-error">{{ $message }}</span>@enderror
</label>
<label class="zazu-field">
    <span class="zazu-label">Job end date</span>
    <input id="job-end-date" type="date" name="event_end_date" value="{{ old('event_end_date', $scheduleEvent?->event_end_date?->format('Y-m-d')) }}" class="zazu-input">
    <span class="zazu-field-help" id="job-end-date-help">The last day. Leave this blank for a one-day job.</span>
    @error('event_end_date')<span class="zazu-field-error">{{ $message }}</span>@enderror
</label>
<label class="zazu-field">
    <span class="zazu-label">Event start time</span>
    <input id="job-start-time" type="time" name="event_start_time" value="{{ old('event_start_time', substr((string) ($scheduleEvent?->event_start_time ?? ''), 0, 5)) }}" class="zazu-input">
    <span class="zazu-field-help" id="job-start-time-help">When the event begins on the first day.</span>
    @error('event_start_time')<span class="zazu-field-error">{{ $message }}</span>@enderror
</label>
<label class="zazu-field">
    <span class="zazu-label">Event end time</span>
    <input id="job-end-time" type="time" name="event_end_time" value="{{ old('event_end_time', substr((string) ($scheduleEvent?->event_end_time ?? ''), 0, 5)) }}" class="zazu-input">
    <span class="zazu-field-help" id="job-end-time-help">When the event ends on the last day.</span>
    @error('event_end_time')<span class="zazu-field-error">{{ $message }}</span>@enderror
</label>
<label class="zazu-field">
    <span class="zazu-label">Setup date</span>
    <input id="job-setup-date" type="date" name="setup_date" value="{{ old('setup_date', $scheduleEvent?->setup_date?->format('Y-m-d')) }}" class="zazu-input">
    <span class="zazu-field-help" id="job-setup-date-help">When your team needs to prepare at the venue. This must be on or before the job start date.</span>
    @error('setup_date')<span class="zazu-field-error">{{ $message }}</span>@enderror
</label>
<label class="zazu-field">
    <span class="zazu-label">Equipment collection date</span>
    <input id="job-collection-date" type="date" name="collection_date" value="{{ old('collection_date', $scheduleEvent?->collection_date?->format('Y-m-d')) }}" class="zazu-input">
    <span class="zazu-field-help" id="job-collection-date-help">When hired items or equipment must be collected from the venue. This must be on or after the job end date.</span>
    @error('collection_date')<span class="zazu-field-error">{{ $message }}</span>@enderror
</label>

<script nonce="{{ request()->attributes->get('cspNonce') }}">
    (() => {
        const fields = document.currentScript?.parentElement;
        if (!fields) return;

        const startDate = fields.querySelector('[name="event_date"]');
        const endDate = fields.querySelector('[name="event_end_date"]');
        const startTime = fields.querySelector('[name="event_start_time"]');
        const endTime = fields.querySelector('[name="event_end_time"]');
        const setupDate = fields.querySelector('[name="setup_date"]');
        const collectionDate = fields.querySelector('[name="collection_date"]');

        if (![startDate, endDate, startTime, endTime, setupDate, collectionDate].every(Boolean)) return;

        function updateScheduleLimits() {
            endDate.min = startDate.value || '';
            setupDate.max = startDate.value || '';
            collectionDate.min = endDate.value || startDate.value || '';

            const sameDay = !endDate.value || endDate.value === startDate.value;
            endTime.min = sameDay && startTime.value ? startTime.value : '';

            const invalidEndTime = sameDay
                && startTime.value
                && endTime.value
                && endTime.value <= startTime.value;

            endTime.setCustomValidity(
                invalidEndTime ? 'For a one-day job, choose an end time later than the start time.' : ''
            );
        }

        [startDate, endDate, startTime, endTime].forEach(input => {
            input.addEventListener('input', updateScheduleLimits);
            input.addEventListener('change', updateScheduleLimits);
        });

        [setupDate, collectionDate].forEach(input => {
            input.addEventListener('input', updateScheduleLimits);
            input.addEventListener('change', updateScheduleLimits);
        });

        updateScheduleLimits();
    })();
</script>
