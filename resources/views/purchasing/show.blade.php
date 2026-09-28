<x-app-layout>
<x-slot:title>{{ $purchaseOrder->reference }}</x-slot:title><x-slot:heading>{{ $purchaseOrder->reference }}</x-slot:heading>
<x-slot:headerAction><a href="{{ route('purchasing.index') }}" class="zazu-btn zazu-btn-secondary">All orders</a></x-slot:headerAction>
<section class="zazu-detail-grid"><div class="zazu-card"><div class="zazu-card-header"><div class="zazu-eyebrow">Purchase order</div><div class="zazu-card-title mt-1">{{ $purchaseOrder->supplier->name }}</div></div><div class="p-5 grid gap-3"><div><strong>Status:</strong> {{ ucfirst($purchaseOrder->status) }}</div><div><strong>Job:</strong> {{ $purchaseOrder->event?->name ?: 'General purchase' }}</div><div><strong>Expected:</strong> {{ $purchaseOrder->expected_at?->format('d M Y') ?: 'Not set' }}</div><div><strong>Total:</strong> {{ $purchaseOrder->currency }} {{ $purchaseOrder->total_amount }}</div></div></div>
<section class="zazu-panel">
    <div class="zazu-eyebrow">Receiving</div>
    <div class="zazu-panel-title mt-1">Receive goods</div>
    <div class="zazu-panel-copy">Record what actually arrived. Zazu prevents receiving more than ordered and keeps partial receipts in inventory history.</div>
    <form method="POST" action="{{ route('purchasing.receive', $purchaseOrder) }}" class="zazu-form mt-4">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
        @foreach($purchaseOrder->items as $item)
            <label class="zazu-field">
                <span class="zazu-label">{{ $item->description }}</span>
                <span class="zazu-field-help">Ordered {{ number_format((float)$item->quantity, 2) }} {{ $item->unit ?: 'units' }} · Received {{ number_format((float)$item->received_quantity, 2) }}</span>
                <input type="number" name="received_quantity[{{ $item->id }}]" min="0" max="{{ $item->quantity - $item->received_quantity }}" step="0.01" class="zazu-input" value="0">
            </label>
        @endforeach
        @if(in_array($purchaseOrder->status, ['ordered','received'], true))
            <button class="zazu-btn zazu-btn-primary">Record receipt</button>
        @endif
    </form>
</section>

@php($nextStatuses = \App\Models\PurchaseOrder::STATUS_TRANSITIONS[$purchaseOrder->status] ?? [])
<div class="zazu-panel">
    <div class="zazu-panel-title">Change status</div>
    @if ($nextStatuses)
        <form method="POST" action="{{ route('purchasing.status',$purchaseOrder) }}" class="flex flex-wrap items-center gap-2 mt-3">
            @csrf @method('PATCH')
            <label class="sr-only" for="purchase-order-status">Next status</label>
            <select id="purchase-order-status" name="status" aria-describedby="purchase-order-status-help">
                @foreach($nextStatuses as $status)
                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button class="zazu-btn zazu-btn-primary">Save status</button>
        </form>
        <div id="purchase-order-status-help" class="zazu-panel-copy mt-2">Only valid next steps are shown for the current order state.</div>
    @else
        <div class="zazu-panel-copy mt-3">This order is {{ strtolower($purchaseOrder->status) }} and has no further status changes.</div>
    @endif
</div></section>
<section class="zazu-card zazu-list mt-5"><div class="zazu-card-header"><div class="zazu-card-title">Order lines</div></div>@foreach($purchaseOrder->items as $item)<div class="zazu-list-item"><div class="zazu-list-main"><div class="zazu-list-title">{{ $item->description }}</div><div class="zazu-list-meta">{{ number_format((float)$item->quantity,2) }} {{ $item->unit }}</div></div><div class="zazu-list-side"><div class="zazu-side-primary">{{ $purchaseOrder->currency }} {{ $item->line_total }}</div></div></div>@endforeach</section>
</x-app-layout>
