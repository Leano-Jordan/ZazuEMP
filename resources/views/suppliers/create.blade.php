<x-app-layout>
<x-slot:title>New supplier</x-slot:title><x-slot:heading>New supplier</x-slot:heading>
<section class="zazu-card"><div class="zazu-card-header"><div class="zazu-eyebrow">Supplier register</div><div class="zazu-card-title mt-1">Supplier details</div></div>
<form method="POST" action="{{ route('suppliers.store') }}" class="zazu-form p-5">@csrf
<div class="zazu-form-grid"><label class="zazu-field"><span>Name</span><input name="name" value="{{ old('name') }}" required>@error('name')<span class="zazu-field-error">{{ $message }}</span>@enderror</label>
<label class="zazu-field"><span>Contact name</span><input name="contact_name" value="{{ old('contact_name') }}"></label>
<label class="zazu-field"><span>Email</span><input type="email" name="email" value="{{ old('email') }}"></label>
<label class="zazu-field"><span>Phone</span><input name="phone" value="{{ old('phone') }}"></label></div>
<label class="zazu-field mt-4"><span>Notes</span><textarea name="notes" rows="4">{{ old('notes') }}</textarea></label>
<div class="flex gap-2 mt-5"><button class="zazu-btn zazu-btn-primary">Save supplier</button><a href="{{ route('suppliers.index') }}" class="zazu-btn zazu-btn-ghost">Cancel</a></div>
</form></section></x-app-layout>
