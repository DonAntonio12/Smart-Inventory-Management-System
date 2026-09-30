@extends('products.layout')

@section('page_title', 'Sales Report')

@push('styles')
    <style>
        .report-filter { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 9px; margin-bottom: 15px; padding: 13px; border: 1px solid #e4e7df; border-radius: 5px; background: #fffefa; }
        .report-filter .field { min-width: 150px; }
        .report-filter label { display: block; margin-bottom: 5px; color: #66736a; font-size: 9px; font-weight: 700; }
        .report-filter input { height: 35px; padding: 0 9px; border: 1px solid #dfe4da; border-radius: 4px; font-size: 10px; }
        .report-grid { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(280px, .8fr); gap: 13px; margin-bottom: 14px; }
        .report-panel { min-width: 0; border: 1px solid #e4e7df; border-radius: 5px; background: #fffefa; }
        .report-panel-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 15px 11px; border-bottom: 1px solid #edf0e9; }
        .report-panel-header h2 { margin: 0; font: 700 12px 'Manrope', sans-serif; }
        .report-panel-header p { margin: 4px 0 0; color: #879188; font-size: 9px; }
        .daily-bars { display: grid; gap: 9px; padding: 15px; }
        .daily-row { display: grid; grid-template-columns: 54px minmax(50px, 1fr) 90px; align-items: center; gap: 10px; }
        .daily-date { color: #69766d; font-size: 9px; }
        .daily-track { height: 9px; overflow: hidden; border-radius: 5px; background: #eef1e9; }
        .daily-fill { height: 100%; min-width: 2px; border-radius: inherit; background: #5b9168; }
        .daily-amount { color: #344239; text-align: right; font-size: 9px; font-weight: 700; }
        .report-table-wrap { overflow-x: auto; }
        .report-table { width: 100%; border-collapse: collapse; text-align: left; white-space: nowrap; }
        .report-table th { padding: 10px 14px; color: #89948c; font-size: 8px; font-weight: 700; text-transform: uppercase; }
        .report-table td { padding: 11px 14px; border-top: 1px solid #eff1eb; color: #59655d; font-size: 10px; }
        .report-table strong { color: #344239; }
        .range-label { color: #7f8b82; font-size: 9px; }
        .report-empty { padding: 24px 16px; color: #77837a; text-align: center; font-size: 10px; }
        @media (max-width: 900px) { .report-grid { grid-template-columns: 1fr; } }
        @media (max-width: 560px) {
            .report-filter { align-items: stretch; }
            .report-filter .field, .report-filter input { width: 100%; }
            .report-filter .button { align-self: flex-start; }
            .daily-row { grid-template-columns: 44px minmax(40px, 1fr) 78px; gap: 7px; }
        }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Reports</p><h1>Sales Report</h1><p>Sales totals, order volume, and product performance for a date range.</p></div>
        <a class="button button-primary" href="{{ route('reports.sales.export', ['from' => $from, 'to' => $to]) }}">Download CSV</a>
    </div>

    <nav class="product-tabs" aria-label="Report sections">
        <a class="product-tab" href="{{ route('reports.index') }}">Reports</a>
        <a class="product-tab active" href="{{ route('reports.sales') }}">Sales</a>
        <a class="product-tab" href="{{ route('reports.inventory') }}">Inventory</a>
    </nav>

    <form class="report-filter" method="GET" action="{{ route('reports.sales') }}">
        <div class="field"><label for="from">From</label><input id="from" name="from" type="date" value="{{ $from }}" required></div>
        <div class="field"><label for="to">To</label><input id="to" name="to" type="date" value="{{ $to }}" required></div>
        <button class="button button-quiet" type="submit">Apply dates</button>
        <span class="range-label">{{ \Illuminate\Support\Carbon::parse($from)->format('M j, Y') }} – {{ \Illuminate\Support\Carbon::parse($to)->format('M j, Y') }}</span>
    </form>

    <section class="stat-grid" aria-label="Sales report summary">
        <article class="stat"><span>Total sales</span><strong>₱{{ number_format($totalSales, 2) }}</strong></article>
        <article class="stat"><span>Sales recorded</span><strong>{{ number_format($saleCount) }}</strong></article>
        <article class="stat"><span>Units sold</span><strong>{{ number_format($unitsSold) }}</strong></article>
        <article class="stat"><span>Average sale</span><strong>₱{{ number_format($averageSale, 2) }}</strong></article>
    </section>

    <section class="report-grid">
        <article class="report-panel">
            <header class="report-panel-header"><div><h2>Sales by day</h2><p>Daily sales within the selected period</p></div></header>
            @if ($dailySales->contains(fn ($day) => $day->total_sales > 0))
                <div class="daily-bars">
                    @foreach ($dailySales as $day)
                        <div class="daily-row"><span class="daily-date">{{ $day->label }}</span><span class="daily-track"><span class="daily-fill" style="display:block;width:{{ max(1, (int) round($day->total_sales / $salesMaximum * 100)) }}%"></span></span><span class="daily-amount">₱{{ number_format($day->total_sales, 2) }}</span></div>
                    @endforeach
                </div>
            @else
                <div class="report-empty">No sales in this date range.</div>
            @endif
        </article>

        <article class="report-panel">
            <header class="report-panel-header"><div><h2>Top products</h2><p>Ranked by sales amount</p></div></header>
            @if ($topProducts->isNotEmpty())
                <div class="report-table-wrap"><table class="report-table"><thead><tr><th>Product</th><th>Units</th><th>Sales</th></tr></thead><tbody>
                    @foreach ($topProducts as $topProduct)
                        <tr><td>{{ $topProduct->product?->name ?? 'Deleted product' }}</td><td>{{ number_format($topProduct->units_sold) }}</td><td><strong>₱{{ number_format((float) $topProduct->total_sales, 2) }}</strong></td></tr>
                    @endforeach
                </tbody></table></div>
            @else
                <div class="report-empty">No product sales in this date range.</div>
            @endif
        </article>
    </section>

    <section class="report-panel">
        <header class="report-panel-header"><div><h2>Daily breakdown</h2><p>Sales references and units sold by date</p></div></header>
        <div class="report-table-wrap"><table class="report-table"><thead><tr><th>Date</th><th>Sales</th><th>Units</th><th>Sales amount</th></tr></thead><tbody>
            @foreach ($dailySales as $day)
                <tr><td>{{ \Illuminate\Support\Carbon::parse($day->date)->format('M j, Y') }}</td><td>{{ number_format($day->sale_count) }}</td><td>{{ number_format($day->units_sold) }}</td><td><strong>₱{{ number_format($day->total_sales, 2) }}</strong></td></tr>
            @endforeach
        </tbody></table></div>
    </section>
@endsection
