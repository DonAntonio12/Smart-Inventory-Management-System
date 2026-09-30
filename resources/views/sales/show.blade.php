@extends('products.layout')

@section('page_title', 'Sale Receipt')

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Sales receipt</p><h1>{{ $reference }}</h1><p>{{ $soldAt->format('M j, Y g:i A') }}</p></div>
        <div style="display:flex;gap:8px"><a class="button button-quiet" href="{{ route('sales.transactions') }}">All transactions</a><a class="button button-primary" href="{{ route('sales.create') }}">New Sale</a></div>
    </div>

    <nav class="product-tabs" aria-label="Sales sections">
        <a class="product-tab" href="{{ route('sales.create') }}">New Sale</a>
        <a class="product-tab active" href="{{ route('sales.transactions') }}">Transactions</a>
    </nav>

    <section class="stat-grid" aria-label="Sale details">
        <article class="stat"><span>Customer / notes</span><strong>{{ $description }}</strong></article>
        <article class="stat"><span>Units sold</span><strong>{{ number_format($units) }}</strong></article>
        <article class="stat"><span>Sale total</span><strong>₱{{ number_format((float) $total, 2) }}</strong></article>
    </section>

    <section class="panel"><div class="table-wrap"><table>
        <thead><tr><th>Product</th><th>SKU</th><th>Quantity</th><th>Unit price</th><th>Line total</th></tr></thead>
        <tbody>
            @foreach ($saleItems as $saleItem)
                @php($quantity = abs($saleItem->quantity))
                <tr>
                    <td>{{ $saleItem->product?->name ?? 'Deleted product' }}</td>
                    <td>{{ $saleItem->product?->sku ?? '—' }}</td>
                    <td>{{ number_format($quantity) }}</td>
                    <td>₱{{ number_format($quantity > 0 ? (float) $saleItem->amount / $quantity : 0, 2) }}</td>
                    <td><strong>₱{{ number_format((float) $saleItem->amount, 2) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table></div></section>
@endsection
