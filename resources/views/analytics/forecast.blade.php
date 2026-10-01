@extends('products.layout')

@section('page_title', 'Demand Forecasting')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Predictive Intelligence</p>
            <h1>Demand Forecast Analytics</h1>
            <p>Projected demand for 7, 14, and 30-day windows based on 30-day sales velocity.</p>
        </div>
    </div>

    <nav class="product-tabs" aria-label="Smart Analytics sections">
        <a class="product-tab" href="{{ route('analytics.index') }}">Overview</a>
        <a class="product-tab active" href="{{ route('analytics.forecast') }}">Demand Forecast</a>
        <a class="product-tab" href="{{ route('analytics.reorder') }}">Smart Reorder</a>
        <a class="product-tab" href="{{ route('analytics.index') }}#sales-trend">Sales Trends</a>
        <a class="product-tab" href="{{ route('analytics.abc-analysis') }}">ABC &amp; Dead Stock</a>
    </nav>

    <section class="panel">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Product &amp; SKU</th>
                        <th>Category</th>
                        <th>On Hand</th>
                        <th>Sales Velocity</th>
                        <th>7-Day Forecast</th>
                        <th>14-Day Forecast</th>
                        <th>30-Day Forecast</th>
                        <th>Est. Days Remaining</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($forecastProducts as $item)
                        <tr>
                            <td>
                                <span class="product-name">{{ $item->name }}</span>
                                <span class="product-sku">{{ $item->sku }}</span>
                            </td>
                            <td>{{ $item->category }}</td>
                            <td><strong>{{ number_format($item->quantity) }}</strong></td>
                            <td>
                                <span>{{ $item->daily_velocity }} / day</span>
                                <span class="sub-detail">({{ $item->units_sold_30 }} in 30d)</span>
                            </td>
                            <td>{{ number_format($item->forecast_7_days) }} units</td>
                            <td>{{ number_format($item->forecast_14_days) }} units</td>
                            <td>{{ number_format($item->forecast_30_days) }} units</td>
                            <td>
                                @if ($item->stockout_risk_days === 999)
                                    <span class="risk-pill no-sales">No Sales</span>
                                @elseif ($item->stockout_risk_days <= 3)
                                    <span class="risk-pill critical">{{ $item->stockout_risk_days }} days left</span>
                                @elseif ($item->stockout_risk_days <= 7)
                                    <span class="risk-pill warning">{{ $item->stockout_risk_days }} days left</span>
                                @else
                                    <span class="risk-pill healthy">{{ $item->stockout_risk_days }} days left</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <strong>No catalog products found</strong>
                                    <p>Add products to your catalog to generate demand forecasts.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($forecastProducts->hasPages())
            <div class="table-footer">
                <span>Showing {{ $forecastProducts->firstItem() ?? 0 }} to {{ $forecastProducts->lastItem() ?? 0 }} of {{ $forecastProducts->total() }} products</span>
                <div class="pagination">
                    @if ($forecastProducts->onFirstPage())
                        <span class="button button-quiet" aria-disabled="true">Previous</span>
                    @else
                        <a class="button button-quiet" href="{{ $forecastProducts->previousPageUrl() }}">Previous</a>
                    @endif

                    @if ($forecastProducts->hasMorePages())
                        <a class="button button-quiet" href="{{ $forecastProducts->nextPageUrl() }}">Next</a>
                    @else
                        <span class="button button-quiet" aria-disabled="true">Next</span>
                    @endif
                </div>
            </div>
        @endif
    </section>
@endsection

@push('styles')
    <style>
        .sub-detail { display: block; color: #829087; font-size: 8px; }
        .risk-pill { display: inline-flex; align-items: center; padding: 3px 8px; border-radius: 12px; font-size: 8px; font-weight: 700; }
        .risk-pill.critical { color: #b94d41; background: #faece8; }
        .risk-pill.warning { color: #b87925; background: #fdf5e6; }
        .risk-pill.healthy { color: #245b43; background: #e3ebd6; }
        .risk-pill.no-sales { color: #78847c; background: #eaede6; }
    </style>
@endpush
