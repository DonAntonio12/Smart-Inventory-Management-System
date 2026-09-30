@extends('products.layout')

@section('page_title', 'Inventory Report')

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Reports</p><h1>Inventory Report</h1><p>Current product quantities and stock status as of {{ now()->format('M j, Y') }}.</p></div>
        <a class="button button-primary" href="{{ route('reports.inventory.export') }}">Download CSV</a>
    </div>

    <nav class="product-tabs" aria-label="Report sections">
        <a class="product-tab" href="{{ route('reports.index') }}">Reports</a>
        <a class="product-tab" href="{{ route('reports.sales') }}">Sales</a>
        <a class="product-tab active" href="{{ route('reports.inventory') }}">Inventory</a>
    </nav>

    <section class="stat-grid" aria-label="Inventory report summary">
        <article class="stat"><span>Products</span><strong>{{ number_format($totalProducts) }}</strong></article>
        <article class="stat"><span>Units on hand</span><strong>{{ number_format($totalQuantity) }}</strong></article>
        <article class="stat"><span>Low stock</span><strong>{{ number_format($lowStockCount) }}</strong></article>
        <article class="stat"><span>Out of stock</span><strong>{{ number_format($outOfStockCount) }}</strong></article>
    </section>

    <section class="panel" style="margin-bottom: 14px">
        <header class="inventory-panel-heading"><div><h2>Units by category</h2><p>Current on-hand quantities grouped by category</p></div></header>
        @if ($categories->isNotEmpty())
            <div class="group-grid" style="padding: 13px">
                @foreach ($categories as $category)
                    <article class="group-row"><span><span class="group-name">{{ $category->name }}</span><span class="group-meta">{{ number_format($category->product_count) }} {{ $category->product_count === 1 ? 'product' : 'products' }}</span></span><span class="group-count"><strong>{{ number_format($category->unit_count) }}</strong><span>units</span></span></article>
                @endforeach
            </div>
        @else
            <div class="empty-state"><strong>No inventory to summarize</strong>Add products to populate this report.</div>
        @endif
    </section>

    <section class="panel">
        @if ($products->isNotEmpty())
            <div class="table-wrap"><table>
                <thead><tr><th>Product</th><th>Category</th><th>Brand</th><th>On hand</th><th>Reorder at</th><th>Stock status</th><th>Expiration</th><th>Expiry status</th></tr></thead>
                <tbody>
                    @foreach ($products as $product)
                        @php($status = $product->quantity === 0 ? 'Out of stock' : ($product->quantity <= $product->low_stock_threshold ? 'Low stock' : 'In stock'))
                        @php($isExpired = $product->expiration_date?->lt(today()) ?? false)
                        @php($expiresSoon = $product->expiration_date && $product->expiration_date->lte(today()->addDays(30)))
                        <tr>
                            <td><span class="product-name">{{ $product->name }}</span><span class="product-sku">{{ $product->sku }}</span></td>
                            <td>{{ $product->category ?: 'Uncategorized' }}</td>
                            <td>{{ $product->brand ?: '—' }}</td>
                            <td>{{ number_format($product->quantity) }}</td>
                            <td>{{ number_format($product->low_stock_threshold) }}</td>
                            <td><span class="stock-pill {{ $product->quantity === 0 ? 'out' : ($product->quantity <= $product->low_stock_threshold ? 'low' : '') }}">{{ $status }}</span></td>
                            <td>{{ $product->expiration_date?->format('M j, Y') ?? '—' }}</td>
                            <td>@if ($product->expiration_date)<span class="stock-pill {{ $isExpired ? 'out' : ($expiresSoon ? 'low' : '') }}">{{ $isExpired ? 'Expired' : ($expiresSoon ? 'Expiring soon' : 'Not expiring') }}</span>@else—@endif</td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
            <div class="table-footer"><span>Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ number_format($products->total()) }} products</span><div class="pagination"><a class="button button-quiet" href="{{ $products->previousPageUrl() ?: '#' }}" @if (!$products->previousPageUrl()) aria-disabled="true" @endif>Previous</a><a class="button button-quiet" href="{{ $products->nextPageUrl() ?: '#' }}" @if (!$products->nextPageUrl()) aria-disabled="true" @endif>Next</a></div></div>
        @else
            <div class="empty-state"><strong>No products in inventory</strong>Add products to populate this report.</div>
        @endif
    </section>
@endsection
