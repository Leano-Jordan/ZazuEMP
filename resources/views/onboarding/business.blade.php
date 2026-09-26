<x-app-layout>
    <x-slot:title>Business setup</x-slot:title>
    <x-slot:heading>Set up your business</x-slot:heading>

    <section class="zazu-onboarding-shell">
        <div class="zazu-onboarding-main">
            <div class="zazu-onboarding-progress">
                <span class="active">✓</span><i></i><span class="active">2</span><i></i><span>✓</span>
            </div>
            <div class="zazu-eyebrow">Almost ready</div>
            <h2 class="zazu-onboarding-title">Tell Zazu about your business</h2>
            <p class="zazu-onboarding-copy">This information is saved to your workspace and reused on quotes, invoices and business records where applicable. You can edit it later.</p>

            <form method="POST" action="{{ route('onboarding.business.store') }}" class="zazu-form-section">
                @csrf
                <div class="zazu-form-grid">
                    <label class="zazu-field">
                        <span class="zazu-label">Business name <span class="zazu-required">*</span></span>
                        <input name="name" value="{{ old('name', $business->name) }}" class="zazu-input" required>
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">Currency <span class="zazu-required">*</span></span>
                        <select name="currency" class="zazu-select" required>
                            @foreach ($currencies as $code => $label)
                                <option value="{{ $code }}" @selected(old('currency', $business->currency ?? 'ZAR') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">Business email</span>
                        <input name="email" type="email" value="{{ old('email', $business->email) }}" class="zazu-input">
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">Phone</span>
                        <input name="phone" value="{{ old('phone', $business->phone) }}" class="zazu-input">
                    </label>
                    <label class="zazu-field zazu-field-wide">
                        <span class="zazu-label">Business address</span>
                        <input name="address" value="{{ old('address', $business->address) }}" class="zazu-input">
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">Website</span>
                        <input name="website" type="url" value="{{ old('website', $business->website) }}" class="zazu-input" placeholder="https://">
                    </label>
                    <label class="zazu-field">
                        <span class="zazu-label">Tax / registration number</span>
                        <input name="tax_number" value="{{ old('tax_number', $business->tax_number) }}" class="zazu-input">
                    </label>
                </div>
                <div class="zazu-actionbar">
                    <a href="{{ route('dashboard') }}" class="zazu-btn zazu-btn-ghost">Skip for now</a>
                    <button class="zazu-btn zazu-btn-primary">Finish setup</button>
                </div>
            </form>
        </div>
        <aside class="zazu-onboarding-aside">
            <div class="zazu-context-card">
                <div class="zazu-context-title">One-time information</div>
                <div class="zazu-context-copy">Zazu stores these details once so you do not have to repeatedly type them into operational records.</div>
            </div>
        </aside>
    </section>
</x-app-layout>