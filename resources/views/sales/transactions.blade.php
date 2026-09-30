@extends('products.layout')

@section('page_title', 'Sales Transactions')

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Sales</p><h1>Transactions</h1><p>Completed sales with their item count and totals.</p></div>
        <a class="button button-primary" href="{{ route('sales.create') }}">New Sale</a>
    </div>

    <nav class="product-tabs" aria-label="Sales sections">
        <a class="product-tab" href="{{ route('sales.create') }}">New Sale</a>
        <a class="product-tab active" href="{{ route('sales.transactions') }}">Transactions</a>
    </nav>

    <section class="stat-grid" aria-label="Sales summary">
        <article class="stat"><span>Sales recorded</span><strong>{{ number_format($saleCount) }}</strong></article>
        <article class="stat"><span>Today's sales</span><strong>₱{{ number_format((float) $todaySales, 2) }}</strong></article>
        <article class="stat"><span>All-time sales</span><strong>₱{{ number_format((float) $totalSales, 2) }}</strong></article>
    </section>

    <section class="panel">
        @if ($sales->isNotEmpty())
            <div class="table-wrap"><table>
                <thead><tr><th>Sale reference</th><th>Date</th><th>Items</th><th>Units</th><th>Total</th><th>Action</th></tr></thead>
                <tbody>
                    @foreach ($sales as $sale)
                        <tr>
                            <td><span class="product-name">{{ $sale->reference }}</span></td>
                            <td>{{ \Illuminate\Support\Carbon::parse($sale->sold_at)->format('M j, Y g:i A') }}</td>
                            <td>{{ number_format($sale->line_count) }}</td>
                            <td>{{ number_format($sale->units_sold) }}</td>
                            <td><strong>₱{{ number_format((float) $sale->total_amount, 2) }}</strong></td>
                            <td><a class="button button-quiet" href="{{ route('sales.show', $sale->reference) }}">View receipt</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
            <div class="table-footer"><span>Showing {{ $sales->firstItem() }}–{{ $sales->lastItem() }} of {{ number_format($sales->total()) }} sales</span><div class="pagination"><a class="button button-quiet" href="{{ $sales->previousPageUrl() ?: '#' }}" @if (!$sales->previousPageUrl()) aria-disabled="true" @endif>Previous</a><a class="button button-quiet" href="{{ $sales->nextPageUrl() ?: '#' }}" @if (!$sales->nextPageUrl()) aria-disabled="true" @endif>Next</a></div></div>
        @else
            <div class="empty-state"><strong>No sales transactions yet</strong>Completed checkouts will appear here.<p style="margin: 16px 0 0"><a class="button button-primary" href="{{ route('sales.create') }}">Start a sale</a></p></div>
        @endif
    </section>
@endsection
