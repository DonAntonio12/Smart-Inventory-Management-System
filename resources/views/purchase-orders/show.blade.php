@extends('products.layout')

@section('page_title', $purchaseOrder->order_number)

@push('styles')
    <style>
        .po-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
        .po-summary-item { padding: 13px 15px; border: 1px solid #e4e7df; border-radius: 5px; background: #fffefa; }
        .po-summary-item span { display: block; color: #78847c; font-size: 9px; }
        .po-summary-item strong { display: block; margin-top: 6px; color: #29372e; font-size: 11px; }
        .po-total-row { display: flex; justify-content: flex-end; padding: 13px 14px; border-top: 1px solid #edf0e9; font-size: 11px; }
        .po-total-row strong { margin-left: 12px; font: 700 14px 'Manrope', sans-serif; }
        .po-show-actions { display: flex; align-items: center; gap: 8px; }
        .po-show-actions form { margin: 0; }
        .po-status { display: inline-flex; align-items: center; padding: 5px 8px; border-radius: 20px; color: #98691d; background: #faf1df; font-size: 8px; font-weight: 700; }
        .po-status.received { color: #3f784d; background: #eff6e9; }
        @media (max-width: 560px) { .po-summary { grid-template-columns: 1fr; } .po-show-actions { align-items: stretch; flex-direction: column; } }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Purchase Order</p><h1>{{ $purchaseOrder->order_number }}</h1><p>{{ $purchaseOrder->supplier->name }}</p></div>
        <div class="po-show-actions">
            <span class="po-status {{ $purchaseOrder->status === 'received' ? 'received' : '' }}">{{ $purchaseOrder->status === 'received' ? 'Received' : 'Open' }}</span>
            @if ($purchaseOrder->status === 'ordered')
                <form method="POST" action="{{ route('purchase-orders.receive', $purchaseOrder) }}" onsubmit="return confirm('Receive this entire purchase order and add its items to inventory?')">
                    @csrf
                    <button class="button button-primary" type="submit">Receive order</button>
                </form>
            @endif
        </div>
    </div>

    <nav class="product-tabs" aria-label="Supplier sections">
        <a class="product-tab" href="{{ route('suppliers.index') }}">Suppliers</a>
        <a class="product-tab active" href="{{ route('purchase-orders.index') }}">Purchase Orders</a>
    </nav>

    @if ($errors->has('purchase_order'))<div class="flash" role="alert">{{ $errors->first('purchase_order') }}</div>@endif

    <section class="po-summary" aria-label="Purchase order details">
        <div class="po-summary-item"><span>Supplier</span><strong>{{ $purchaseOrder->supplier->name }}</strong></div>
        <div class="po-summary-item"><span>Ordered</span><strong>{{ $purchaseOrder->ordered_at->format('M j, Y') }}</strong></div>
        <div class="po-summary-item"><span>Expected delivery</span><strong>{{ $purchaseOrder->expected_at?->format('M j, Y') ?? 'Not specified' }}</strong></div>
    </section>

    <section class="panel">
        <div class="table-wrap"><table>
            <thead><tr><th>Product</th><th>SKU</th><th>Quantity</th><th>Received</th><th>Unit cost</th><th>Line total</th></tr></thead>
            <tbody>
                @foreach ($purchaseOrder->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->product_sku }}</td>
                        <td>{{ number_format($item->quantity) }}</td>
                        <td>{{ number_format($item->quantity_received) }}</td>
                        <td>₱{{ number_format((float) $item->unit_cost, 2) }}</td>
                        <td>₱{{ number_format($item->quantity * (float) $item->unit_cost, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table></div>
        <div class="po-total-row">Estimated total <strong>₱{{ number_format($purchaseOrder->items->sum(fn ($item) => $item->quantity * (float) $item->unit_cost), 2) }}</strong></div>
    </section>

    @if ($purchaseOrder->notes)<section class="panel" style="margin-top: 14px; padding: 14px"><p class="eyebrow">Order notes</p><p style="margin: 0; color: #526057; font-size: 11px">{{ $purchaseOrder->notes }}</p></section>@endif
    @if ($purchaseOrder->received_at)<p style="margin-top: 12px; color: #728077; font-size: 10px">Received {{ $purchaseOrder->received_at->format('M j, Y g:i A') }}</p>@endif
@endsection
