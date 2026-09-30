@extends('products.layout')

@section('page_title', 'Purchase Orders')

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Procurement</p><h1>Purchase Orders</h1><p>Track supplier orders and receive stock into your inventory.</p></div>
        <a class="button button-primary" href="{{ route('purchase-orders.create') }}">New purchase order</a>
    </div>

    <nav class="product-tabs" aria-label="Supplier sections">
        <a class="product-tab" href="{{ route('suppliers.index') }}">Suppliers</a>
        <a class="product-tab active" href="{{ route('purchase-orders.index') }}">Purchase Orders</a>
    </nav>

    <section class="stat-grid" aria-label="Purchase order summary">
        <article class="stat"><span>Open orders</span><strong>{{ number_format($openCount) }}</strong></article>
        <article class="stat"><span>Received orders</span><strong>{{ number_format($receivedCount) }}</strong></article>
        <article class="stat"><span>All purchase orders</span><strong>{{ number_format($openCount + $receivedCount) }}</strong></article>
    </section>

    <section class="panel">
        <form class="toolbar" method="GET" action="{{ route('purchase-orders.index') }}">
            <select name="status" aria-label="Filter by order status">
                <option value="">All statuses</option>
                <option value="ordered" @selected($selectedStatus === 'ordered')>Open</option>
                <option value="received" @selected($selectedStatus === 'received')>Received</option>
            </select>
            <button class="button button-quiet" type="submit">Apply filter</button>
        </form>
        @if ($purchaseOrders->isNotEmpty())
            <div class="table-wrap"><table>
                <thead><tr><th>Order</th><th>Supplier</th><th>Items</th><th>Ordered</th><th>Expected</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach ($purchaseOrders as $purchaseOrder)
                        <tr>
                            <td><span class="product-name">{{ $purchaseOrder->order_number }}</span><span class="product-sku">{{ $purchaseOrder->notes ?: 'No notes' }}</span></td>
                            <td>{{ $purchaseOrder->supplier->name }}</td>
                            <td>{{ number_format($purchaseOrder->items_count) }}</td>
                            <td>{{ $purchaseOrder->ordered_at->format('M j, Y') }}</td>
                            <td>{{ $purchaseOrder->expected_at?->format('M j, Y') ?? '—' }}</td>
                            <td><span class="stock-pill {{ $purchaseOrder->status === 'received' ? '' : 'low' }}">{{ $purchaseOrder->status === 'received' ? 'Received' : 'Open' }}</span></td>
                            <td><a class="button button-quiet" href="{{ route('purchase-orders.show', $purchaseOrder) }}">View order</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
            <div class="table-footer"><span>Showing {{ $purchaseOrders->firstItem() }}–{{ $purchaseOrders->lastItem() }} of {{ number_format($purchaseOrders->total()) }} orders</span><div class="pagination"><a class="button button-quiet" href="{{ $purchaseOrders->previousPageUrl() ?: '#' }}" @if (!$purchaseOrders->previousPageUrl()) aria-disabled="true" @endif>Previous</a><a class="button button-quiet" href="{{ $purchaseOrders->nextPageUrl() ?: '#' }}" @if (!$purchaseOrders->nextPageUrl()) aria-disabled="true" @endif>Next</a></div></div>
        @else
            <div class="empty-state"><strong>No purchase orders yet</strong>Create an order to request stock from a supplier.<p style="margin: 16px 0 0"><a class="button button-primary" href="{{ route('purchase-orders.create') }}">Create purchase order</a></p></div>
        @endif
    </section>
@endsection
