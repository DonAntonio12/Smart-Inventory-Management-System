@extends('products.layout')

@section('page_title', 'Stock Overview')

@push('styles')
    <style>
        .inventory-stats { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .inventory-expiry { display: block; color: inherit; text-decoration: none; border-color: #ecdcd3; transition: transform .16s ease, border-color .16s ease; }
        .inventory-expiry:hover { transform: translateY(-1px); border-color: #d69a83; }
        .stat.inventory-expiry > span { color: #9f5745; }
        .stat.inventory-expiry > strong { color: #a34839; }
        .stat.inventory-expiry > small { display: block; margin-top: 5px; color: #879188; font-size: 8px; }
        .inventory-shortcuts { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-bottom: 17px; }
        .inventory-shortcut { display: flex; min-height: 67px; align-items: center; gap: 12px; padding: 12px 14px; border: 1px solid #e4e7df; border-radius: 5px; background: #fffefa; transition: border-color .16s ease, transform .16s ease; }
        .inventory-shortcut:hover { transform: translateY(-1px); border-color: #9db79a; }
        .shortcut-icon { display: grid; width: 34px; height: 34px; flex: 0 0 auto; place-items: center; border-radius: 5px; color: #245b43; background: #edf3e8; }
        .shortcut-icon.out { color: #a96b2d; background: #faf1df; }
        .shortcut-icon.adjust { color: #4e718c; background: #eaf0f4; }
        .shortcut-icon svg { width: 17px; height: 17px; }
        .inventory-shortcut strong { display: block; color: #29372e; font-size: 10px; }
        .inventory-shortcut span:last-child { display: block; margin-top: 3px; color: #879188; font-size: 9px; }
        .inventory-panel-heading { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 15px 15px 11px; }
        .inventory-panel-heading h2 { margin: 0; font: 700 13px 'Manrope', sans-serif; }
        .inventory-panel-heading p { margin: 4px 0 0; color: #879188; font-size: 9px; }
        .movement-type { color: #4f805a; font-weight: 700; }
        .movement-type.stock_out { color: #a85c40; }
        .movement-type.adjustment { color: #56758a; }
        @media (max-width: 850px) { .inventory-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 560px) { .inventory-shortcuts { grid-template-columns: 1fr; } }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Inventory</p><h1>Stock Overview</h1><p>Current stock health and recent inventory activity.</p></div>
        <a class="button button-primary" href="{{ route('inventory.adjustment') }}">Record a count</a>
    </div>

    <nav class="product-tabs" aria-label="Inventory sections">
        <a class="product-tab active" href="{{ route('inventory.index') }}">Stock Overview</a>
        <a class="product-tab" href="{{ route('inventory.stock-in') }}">Stock In</a>
        <a class="product-tab" href="{{ route('inventory.stock-out') }}">Stock Out</a>
        <a class="product-tab" href="{{ route('inventory.adjustment') }}">Adjustment</a>
        <a class="product-tab" href="{{ route('inventory.low-stock') }}">Low Stock</a>
        <a class="product-tab" href="{{ route('inventory.expiring-products') }}">Expiring</a>
    </nav>

    <section class="stat-grid inventory-stats" aria-label="Inventory summary">
        <article class="stat"><span>Total products</span><strong>{{ number_format($totalProducts) }}</strong></article>
        <article class="stat"><span>Total units</span><strong>{{ number_format($totalQuantity) }}</strong></article>
        <article class="stat"><span>Low stock</span><strong>{{ number_format($lowStockCount) }}</strong></article>
        <article class="stat"><span>Out of stock</span><strong>{{ number_format($outOfStockCount) }}</strong></article>
        <a class="stat inventory-expiry" href="{{ route('inventory.expiring-products') }}" aria-label="View {{ $expiringCount }} expired or expiring products"><span>Expired / expiring</span><strong>{{ number_format($expiringCount) }}</strong><small>Expired or due within 30 days</small></a>
    </section>

    <section class="inventory-shortcuts" aria-label="Stock movement actions">
        <a class="inventory-shortcut" href="{{ route('inventory.stock-in') }}"><span class="shortcut-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 19V5m-6 6 6-6 6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span><span><strong>Receive stock</strong><span>Record incoming units</span></span></a>
        <a class="inventory-shortcut" href="{{ route('inventory.stock-out') }}"><span class="shortcut-icon out"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14m6-6-6 6-6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span><span><strong>Issue stock</strong><span>Record outgoing units</span></span></a>
        <a class="inventory-shortcut" href="{{ route('inventory.adjustment') }}"><span class="shortcut-icon adjust"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M7 4v6m10 4v6M4 17h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span><span><strong>Adjust count</strong><span>Correct a stock count</span></span></a>
    </section>

    <section class="panel" aria-labelledby="inventory-products-heading">
        <header class="inventory-panel-heading"><div><h2 id="inventory-products-heading">Current stock</h2><p>Every product and its available quantity</p></div><a class="button button-quiet" href="{{ route('products.index') }}">All products</a></header>
        @if ($products->isNotEmpty())
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Product</th><th>Category</th><th>Brand</th><th>On hand</th><th>Reorder at</th><th>Stock status</th><th>Expiry status</th></tr></thead>
                    <tbody>
                        @foreach ($products as $product)
                            @php($stockClass = $product->quantity === 0 ? 'out' : ($product->quantity <= $product->low_stock_threshold ? 'low' : ''))
                            @php($isExpired = $product->expiration_date?->lt(today()) ?? false)
                            @php($expiresSoon = $product->expiration_date && $product->expiration_date->lte(today()->addDays(30)))
                            <tr>
                                <td><span class="product-name">{{ $product->name }}</span><span class="product-sku">{{ $product->sku }}</span></td>
                                <td>{{ $product->category ?: 'Uncategorized' }}</td>
                                <td>{{ $product->brand ?: '—' }}</td>
                                <td>{{ number_format($product->quantity) }}</td>
                                <td>{{ number_format($product->low_stock_threshold) }}</td>
                                <td><span class="stock-pill {{ $stockClass }}">{{ $product->quantity === 0 ? 'Out of stock' : ($product->quantity <= $product->low_stock_threshold ? 'Low stock' : 'In stock') }}</span></td>
                                <td>@if ($product->expiration_date)<span class="stock-pill {{ $isExpired ? 'out' : ($expiresSoon ? 'low' : '') }}">{{ $isExpired ? 'Expired' : ($expiresSoon ? 'Expiring soon' : 'Not expiring') }}</span>@else—@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="table-footer"><span>Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ number_format($products->total()) }} products</span><div class="pagination"><a class="button button-quiet" href="{{ $products->previousPageUrl() ?: '#' }}" @if (!$products->previousPageUrl()) aria-disabled="true" @endif>Previous</a><a class="button button-quiet" href="{{ $products->nextPageUrl() ?: '#' }}" @if (!$products->nextPageUrl()) aria-disabled="true" @endif>Next</a></div></div>
        @else
            <div class="empty-state"><strong>No products to show</strong>Add products to your catalog to see stock levels here.</div>
        @endif
    </section>

    <section class="panel" style="margin-top: 14px" aria-labelledby="inventory-activity-heading">
        <header class="inventory-panel-heading"><div><h2 id="inventory-activity-heading">Recent stock activity</h2><p>Latest recorded movements</p></div></header>
        @if ($recentTransactions->isNotEmpty())
            <div class="table-wrap"><table><thead><tr><th>Product</th><th>Movement</th><th>Details</th><th>Quantity</th><th>Date</th></tr></thead><tbody>
                @foreach ($recentTransactions as $transaction)
                    <tr><td>{{ $transaction->product?->name ?? 'Deleted product' }}</td><td><span class="movement-type {{ $transaction->type }}">{{ str_replace('_', ' ', ucfirst($transaction->type)) }}</span></td><td>{{ $transaction->description ?: '—' }}</td><td>{{ $transaction->quantity > 0 ? '+' : '' }}{{ number_format($transaction->quantity) }}</td><td>{{ $transaction->occurred_at->format('M j, Y g:i A') }}</td></tr>
                @endforeach
            </tbody></table></div>
        @else
            <div class="empty-state"><strong>No stock movements yet</strong>Stock receipts, issues, and adjustments will be recorded here.</div>
        @endif
    </section>
@endsection
