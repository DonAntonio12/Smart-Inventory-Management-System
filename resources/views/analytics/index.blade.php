@extends('products.layout')

@section('page_title', 'Smart Analytics')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Predictive Intelligence</p>
            <h1>Smart Analytics Overview</h1>
            <p>Data-driven demand forecasting, automated restocking alerts, and inventory velocity metrics.</p>
        </div>
    </div>

    <nav class="product-tabs" aria-label="Smart Analytics sections">
        <a class="product-tab active" href="{{ route('analytics.index') }}">Overview</a>
        <a class="product-tab" href="{{ route('analytics.forecast') }}">Demand Forecast</a>
        <a class="product-tab" href="{{ route('analytics.reorder') }}">Smart Reorder ({{ $criticalReorderCount + $warningReorderCount }})</a>
        <a class="product-tab" href="#sales-trend">Sales Trends</a>
        <a class="product-tab" href="{{ route('analytics.abc-analysis') }}">ABC &amp; Dead Stock</a>
    </nav>

    <div class="stat-grid" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
        <div class="stat">
            <span>30-Day Revenue</span>
            <strong>₱{{ number_format($totalRevenue30, 2) }}</strong>
        </div>
        <div class="stat">
            <span>30-Day Volume Sold</span>
            <strong>{{ number_format($totalUnitsSold30) }} units</strong>
        </div>
        <div class="stat">
            <span>Critical Restock Alerts</span>
            <strong style="color: #b94d41;">{{ number_format($criticalReorderCount) }} items</strong>
        </div>
        <div class="stat">
            <span>Dead Stock Risk</span>
            <strong style="color: #b87925;">{{ number_format($deadStockCount) }} items</strong>
        </div>
    </div>

    <div class="analytics-layout-grid">
        <div class="panel analytics-chart-panel" id="sales-trend">
            <div class="panel-header">
                <h3>14-Day Revenue Velocity Trend</h3>
                <span class="panel-caption">Daily sales volume tracking</span>
            </div>
            <div class="svg-container">
                <svg viewBox="0 0 600 150" class="analytics-chart">
                    <!-- Grid Lines -->
                    <line x1="20" y1="20" x2="580" y2="20" stroke="#f0f2eb" stroke-width="1"/>
                    <line x1="20" y1="75" x2="580" y2="75" stroke="#f0f2eb" stroke-width="1"/>
                    <line x1="20" y1="130" x2="580" y2="130" stroke="#e4e7df" stroke-width="1"/>

                    <!-- Polyline Trend -->
                    @if (trim($trendPoints) !== '')
                        <polyline fill="none" stroke="#245b43" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="{{ $trendPoints }}"/>
                    @endif
                </svg>
                <div class="chart-labels">
                    @foreach ($chartDays as $day)
                        <span>{{ $day['label'] }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="panel analytics-side-panel">
            <div class="panel-header">
                <h3>Top Restock Recommendations</h3>
                <a href="{{ route('analytics.reorder') }}" class="header-link">View All</a>
            </div>
            <div class="reorder-list">
                @forelse ($reorderRecommendations as $item)
                    <div class="reorder-item">
                        <div class="reorder-info">
                            <strong class="item-name">{{ $item->name }}</strong>
                            <span class="item-meta">Stock: <strong>{{ $item->quantity }}</strong> | Velocity: {{ $item->daily_velocity }}/day</span>
                        </div>
                        <div class="reorder-badge-wrap">
                            @if ($item->urgency === 'critical')
                                <span class="urgency-pill critical">Critical ({{ $item->days_remaining }}d)</span>
                            @elseif ($item->urgency === 'warning')
                                <span class="urgency-pill warning">Reorder Soon</span>
                            @else
                                <span class="urgency-pill healthy">Healthy</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-reorders">
                        <span>All products have healthy stock levels.</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .analytics-layout-grid { display: grid; grid-template-columns: minmax(0, 1.8fr) minmax(0, 1.2fr); gap: 16px; margin-top: 16px; }
        .panel-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border-bottom: 1px solid #edf0e9; }
        .panel-header h3 { margin: 0; font: 700 14px 'Manrope', sans-serif; color: #29372e; }
        .panel-caption { color: #829087; font-size: 9px; }
        .header-link { color: var(--green); font-size: 10px; font-weight: 700; }
        .header-link:hover { text-decoration: underline; }
        .svg-container { padding: 16px; }
        .analytics-chart { width: 100%; height: auto; display: block; overflow: visible; }
        .chart-labels { display: flex; justify-content: space-between; margin-top: 8px; padding: 0 4px; color: #829087; font-size: 8px; }
        .reorder-list { display: flex; flex-direction: column; }
        .reorder-item { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 16px; border-bottom: 1px solid #f0f2eb; }
        .reorder-item:last-child { border-bottom: 0; }
        .item-name { display: block; color: #29372e; font-size: 11px; font-weight: 700; }
        .item-meta { display: block; margin-top: 2px; color: #78847c; font-size: 9px; }
        .urgency-pill { display: inline-flex; align-items: center; padding: 3px 8px; border-radius: 12px; font-size: 8px; font-weight: 700; text-transform: uppercase; }
        .urgency-pill.critical { color: #b94d41; background: #faece8; }
        .urgency-pill.warning { color: #b87925; background: #fdf5e6; }
        .urgency-pill.healthy { color: #245b43; background: #e3ebd6; }
        .empty-reorders { padding: 24px; text-align: center; color: #78847c; font-size: 10px; }
        @media (max-width: 850px) {
            .analytics-layout-grid { grid-template-columns: 1fr; }
            .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
        }
    </style>
@endpush
