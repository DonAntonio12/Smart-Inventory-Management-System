@extends('products.layout')

@section('page_title', $title)

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Inventory</p><h1>{{ $title }}</h1><p>{{ $description }}</p></div>
        <a class="button button-quiet" href="{{ route('inventory.index') }}">Stock Overview</a>
    </div>

    <nav class="product-tabs" aria-label="Inventory sections">
        <a class="product-tab" href="{{ route('inventory.index') }}">Stock Overview</a>
        <a class="product-tab" href="{{ route('inventory.stock-in') }}">Stock In</a>
        <a class="product-tab" href="{{ route('inventory.stock-out') }}">Stock Out</a>
        <a class="product-tab" href="{{ route('inventory.adjustment') }}">Adjustment</a>
        <a class="product-tab {{ $kind === 'low' ? 'active' : '' }}" href="{{ route('inventory.low-stock') }}">Low Stock</a>
        <a class="product-tab {{ $kind === 'expiring' ? 'active' : '' }}" href="{{ route('inventory.expiring-products') }}">Expiring</a>
    </nav>

    <section class="panel">
        @if ($products->isNotEmpty())
            <div class="table-wrap"><table>
                <thead><tr><th>Product</th><th>Category</th><th>Brand</th><th>On hand</th><th>{{ $kind === 'low' ? 'Reorder at' : 'Expires on' }}</th>@if ($kind === 'expiring')<th>Expiry status</th>@endif<th>Action</th></tr></thead>
                <tbody>
                    @foreach ($products as $product)
                        @php($expired = $kind === 'expiring' && $product->expiration_date->lt(today()))
                        <tr>
                            <td><span class="product-name">{{ $product->name }}</span><span class="product-sku">{{ $product->sku }}</span></td>
                            <td>{{ $product->category ?: 'Uncategorized' }}</td>
                            <td>{{ $product->brand ?: '—' }}</td>
                            <td><span class="stock-pill {{ $product->quantity === 0 ? 'out' : 'low' }}">{{ number_format($product->quantity) }} units</span></td>
                            <td>{{ $kind === 'low' ? number_format($product->low_stock_threshold).' units' : $product->expiration_date->format('M j, Y') }}</td>
                            @if ($kind === 'expiring')<td><span class="stock-pill {{ $expired ? 'out' : 'low' }}">{{ $expired ? 'Expired' : ($product->expiration_date->isToday() ? 'Expires today' : 'Expires in '.today()->diffInDays($product->expiration_date).' days') }}</span></td>@endif
                            <td><a class="button button-quiet" href="{{ route('products.edit', $product) }}">Edit product</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
            <div class="table-footer"><span>Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ number_format($products->total()) }} products</span><div class="pagination"><a class="button button-quiet" href="{{ $products->previousPageUrl() ?: '#' }}" @if (!$products->previousPageUrl()) aria-disabled="true" @endif>Previous</a><a class="button button-quiet" href="{{ $products->nextPageUrl() ?: '#' }}" @if (!$products->nextPageUrl()) aria-disabled="true" @endif>Next</a></div></div>
        @else
            <div class="empty-state"><strong>Nothing needs attention</strong>{{ $kind === 'low' ? 'No products are at or below their reorder threshold.' : 'No in-stock products are expired or expiring within the next 30 days.' }}</div>
        @endif
    </section>
@endsection
