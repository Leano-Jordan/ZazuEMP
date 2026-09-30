<x-app-layout>
<x-slot:title>{{ isset($supplier) ? 'Edit supplier' : 'New supplier' }}</x-slot:title>
<x-slot:heading>{{ isset($supplier) ? 'Edit supplier' : 'New supplier' }}</x-slot:heading>
<section class="zazu-command-band zazu-compact-editor-command"><div><div class="zazu-eyebrow">Resources / Supplier register</div><h2 class="zazu-command-title">{{ isset($supplier) ? 'Maintain supplier details' : 'Add a supplier' }}</h2><p class="zazu-command-copy">Keep the relationship record used by purchasing accurate without changing existing purchase history.</p></div></section>
<section class="zazu-card zazu-compact-editor-card"><div class="zazu-card-header"><div class="zazu-eyebrow">Supplier register</div><div class="zazu-card-title mt-1">Supplier details</div></div>
<form method="POST" action="{{ isset($supplier) ? route('suppliers.update', $supplier) : route('suppliers.store') }}" class="zazu-form p-5">@csrf
@if(isset($supplier)) @method('PUT') @endif
<div class="zazu-form-grid"><label class="zazu-field"><span>Name</span><input name="name" value="{{ old('name', $supplier->name ?? '') }}" required>@error('name')<span class="zazu-field-error">{{ $message }}</span>@enderror</label>
<label class="zazu-field"><span>Contact name</span><input name="contact_name" value="{{ old('contact_name', $supplier->contact_name ?? '') }}"></label>
<label class="zazu-field"><span>Email</span><input type="email" name="email" value="{{ old('email', $supplier->email ?? '') }}"></label>
<label class="zazu-field"><span>Phone</span><input name="phone" value="{{ old('phone', $supplier->phone ?? '') }}"></label></div>
<label class="zazu-field mt-4"><span>Notes</span><textarea name="notes" rows="4">{{ old('notes', $supplier->notes ?? '') }}</textarea></label>
<div class="zazu-actionbar zazu-actionbar-sticky"><button class="zazu-btn zazu-btn-primary">{{ isset($supplier) ? 'Save changes' : 'Save supplier' }}</button><a href="{{ route('suppliers.index') }}" class="zazu-btn zazu-btn-ghost">Cancel</a></div>
</form></section></x-app-layout>
