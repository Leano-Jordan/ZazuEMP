<x-app-layout>
<x-slot:title>New purchase order</x-slot:title><x-slot:heading>New purchase order</x-slot:heading>
<section class="zazu-card"><div class="zazu-card-header"><div class="zazu-eyebrow">Buying</div><div class="zazu-card-title mt-1">Order details</div></div>
<form method="POST" action="{{ route('purchasing.store') }}" class="zazu-form p-5">@csrf
<input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
<div class="zazu-form-grid"><label class="zazu-field"><span>Supplier</span><select name="supplier_id" required><option value="">Choose supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(old('supplier_id')==$supplier->id)>{{ $supplier->name }}</option>@endforeach</select></label>
<label class="zazu-field"><span>Currency</span><input name="currency" value="{{ old('currency',$currency) }}" maxlength="3" required></label>
<label class="zazu-field"><span>Expected date</span><input type="date" name="expected_at" value="{{ old('expected_at') }}"></label></div>
<div class="zazu-form-section mt-5"><div class="zazu-form-section-title">Order line</div><div class="zazu-form-grid mt-3">
<label class="zazu-field"><span>Description</span><input name="description[]" required></label>
<label class="zazu-field"><span>Quantity</span><input type="number" step="0.01" min="0.01" name="quantity[]" required></label>
<label class="zazu-field"><span>Unit</span><input name="unit[]" value="unit"></label>
<label class="zazu-field"><span>Unit price</span><input type="number" step="0.01" min="0" name="unit_price[]" required></label>
<label class="zazu-field"><span>Catalogue item (optional)</span><select name="capability_id[]"><option value="">Not linked</option>@foreach($catalogue as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach</select></label></div></div>
<label class="zazu-field mt-4"><span>Notes</span><textarea name="notes" rows="3">{{ old('notes') }}</textarea></label>
<div class="flex gap-2 mt-5"><button class="zazu-btn zazu-btn-primary">Create purchase order</button><a href="{{ route('purchasing.index') }}" class="zazu-btn zazu-btn-ghost">Cancel</a></div>
</form></section></x-app-layout>
