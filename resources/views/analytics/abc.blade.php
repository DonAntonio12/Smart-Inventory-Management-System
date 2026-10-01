@extends('products.layout')

@section('page_title', 'ABC & Dead Stock Analysis')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Predictive Intelligence</p>
            <h1>ABC &amp; Dead Stock Analysis</h1>
            <p>Classify inventory by revenue impact (Class A: 70%, Class B: 20%, Class C: 10%) and identify stagnant capital.</p>
        </div>
    </div>

    <nav class="product-tabs" aria-label="Smart Analytics sections">
        <a class="product-tab" href="{{ route('analytics.index') }}">Overview</a>
        <a class="product-tab" href="{{ route('analytics.forecast') }}">Demand Forecast</a>
        <a class="product-tab" href="{{ route('analytics.reorder') }}">Smart Reorder</a>
        <a class="product-tab" href="{{ route('analytics.index') }}#sales-trend">Sales Trends</a>
        <a class="product-tab active" href="{{ route('analytics.abc-analysis') }}">ABC &amp; Dead Stock</a>
    </nav>

    <div class="stat-grid">
        <div class="stat">
            <span>Class A Items (Top 70% Revenue)</span>
            <strong style="color: #245b43;">{{ number_format($classA->count()) }} products</strong>
        </div>
        <div class="stat">
            <span>Class B &amp; C Items</span>
            <strong>{{ number_format($classB->count() + $classC->count()) }} products</strong>
        </div>
        <div class="stat">
            <span>Dead Stock Risk (60 Days Unsold)</span>
            <strong style="color: #b87925;">{{ number_format($deadStockCount) }} products</strong>
        </div>
    </div>

    <div class="abc-grid">
        <!-- Class A Panel -->
        <section class="panel">
            <div class="panel-header">
                <h3>Class A — High Value Drivers (Top 70% Revenue)</h3>
                <span class="class-tag class-a">Class A</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>On Hand</th>
                            <th>60-Day Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($classA as $item)
                            <tr>
                                <td>
                                    <span class="product-name">{{ $item->name }}</span>
                                    <span class="product-sku">{{ $item->sku }}</span>
                                </td>
                                <td>{{ number_format($item->quantity) }}</td>
                                <td><strong>₱{{ number_format($item->revenue_60, 2) }}</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-state">No Class A products yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Dead Stock Risk Panel -->
        <section class="panel">
            <div class="panel-header">
                <h3>Dead Stock Risk (0 Sales in Last 60 Days)</h3>
                <span class="class-tag dead-stock">Dead Stock</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Stagnant Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($deadStock as $item)
                            <tr>
                                <td>
                                    <span class="product-name">{{ $item->name }}</span>
                                    <span class="product-sku">{{ $item->sku }}</span>
                                </td>
                                <td>{{ $item->category }}</td>
                                <td><strong style="color: #b87925;">{{ number_format($item->quantity) }} units</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-state">No dead stock identified. Excellent turnover!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .abc-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin-top: 16px; }
        .panel-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border-bottom: 1px solid #edf0e9; }
        .panel-header h3 { margin: 0; font: 700 13px 'Manrope', sans-serif; color: #29372e; }
        .class-tag { padding: 3px 8px; border-radius: 4px; font-size: 8px; font-weight: 800; text-transform: uppercase; }
        .class-tag.class-a { color: #245b43; background: #e3ebd6; }
        .class-tag.dead-stock { color: #b87925; background: #fdf5e6; }
        @media (max-width: 850px) {
            .abc-grid { grid-template-columns: 1fr; }
        }
    </style>
@endpush
