@extends('products.layout')

@section('page_title', 'Reports')

@push('styles')
    <style>
        .report-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 13px; }
        .report-link { display: flex; min-height: 168px; flex-direction: column; padding: 18px; border: 1px solid #e4e7df; border-radius: 6px; background: #fffefa; transition: transform .16s ease, border-color .16s ease; }
        .report-link:hover { transform: translateY(-2px); border-color: #9cb59a; }
        .report-icon { display: grid; width: 35px; height: 35px; margin-bottom: 17px; place-items: center; border-radius: 5px; color: #245b43; background: #edf3e8; }
        .report-icon svg { width: 18px; height: 18px; }
        .report-link h2 { margin: 0; font: 700 15px 'Manrope', sans-serif; }
        .report-link p { margin: 6px 0 14px; color: #7a867d; font-size: 10px; line-height: 1.55; }
        .report-link span:last-child { margin-top: auto; color: #376a46; font-size: 10px; font-weight: 700; }
        .report-section-title { margin: 25px 0 12px; font: 700 12px 'Manrope', sans-serif; }
        @media (max-width: 600px) { .report-grid { grid-template-columns: 1fr; } }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Business intelligence</p><h1>Reports</h1><p>Review sales performance and current inventory position.</p></div>
    </div>

    <section class="stat-grid" aria-label="Report overview">
        <article class="stat"><span>Sales this month</span><strong>₱{{ number_format((float) $monthSales, 2) }}</strong></article>
        <article class="stat"><span>Sales recorded this month</span><strong>{{ number_format($monthOrderCount) }}</strong></article>
        <article class="stat"><span>Products in catalog</span><strong>{{ number_format($totalProducts) }}</strong></article>
        <article class="stat"><span>Units on hand</span><strong>{{ number_format($totalQuantity) }}</strong></article>
        <article class="stat"><span>Low stock</span><strong>{{ number_format($lowStockCount) }}</strong></article>
        <article class="stat"><span>Out of stock</span><strong>{{ number_format($outOfStockCount) }}</strong></article>
    </section>

    <h2 class="report-section-title">Available reports</h2>
    <section class="report-grid" aria-label="Available reports">
        <a class="report-link" href="{{ route('reports.sales') }}">
            <span class="report-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 19V5m0 14h17M8 15l4-4 3 2 5-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            <h2>Sales Report</h2><p>Filter sales by date, compare daily totals, and see which products are selling.</p><span>Open sales report →</span>
        </a>
        <a class="report-link" href="{{ route('reports.inventory') }}">
            <span class="report-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m4 7 8-4 8 4v10l-8 4-8-4V7Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m4.5 7.3 7.5 4.2 7.5-4.2M12 12v8" stroke="currentColor" stroke-width="1.7"/></svg></span>
            <h2>Inventory Report</h2><p>Review current units, product categories, reorder levels, and stock status.</p><span>Open inventory report →</span>
        </a>
    </section>
@endsection
