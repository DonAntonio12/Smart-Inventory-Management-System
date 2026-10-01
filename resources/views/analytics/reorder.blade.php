@extends('products.layout')

@section('page_title', 'Smart Reorder Recommendations')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Predictive Intelligence</p>
            <h1>Smart Reorder Engine</h1>
            <p>Automated replenishment calculation based on sales velocity, safety stock, and reorder thresholds.</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a class="button button-quiet" href="{{ route('inventory.stock-in') }}">Stock In</a>
            <a class="button button-primary" href="{{ route('purchase-orders.create') }}">Create Purchase Order</a>
        </div>
    </div>

    <nav class="product-tabs" aria-label="Smart Analytics sections">
        <a class="product-tab" href="{{ route('analytics.index') }}">Overview</a>
        <a class="product-tab" href="{{ route('analytics.forecast') }}">Demand Forecast</a>
        <a class="product-tab active" href="{{ route('analytics.reorder') }}">Smart Reorder ({{ $reorderItems->count() }})</a>
        <a class="product-tab" href="{{ route('analytics.index') }}#sales-trend">Sales Trends</a>
        <a class="product-tab" href="{{ route('analytics.abc-analysis') }}">ABC &amp; Dead Stock</a>
    </nav>

    <div class="stat-grid">
        <div class="stat">
            <span>Out of Stock Items</span>
            <strong style="color: #b94d41;">{{ number_format($outOfStockCount) }}</strong>
        </div>
        <div class="stat">
            <span>Critical Restock</span>
            <strong style="color: #b94d41;">{{ number_format($criticalCount) }}</strong>
        </div>
        <div class="stat">
            <span>Reorder Soon</span>
            <strong style="color: #b87925;">{{ number_format($warningCount) }}</strong>
        </div>
    </div>

    <section class="panel">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Product &amp; SKU</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Reorder Threshold</th>
                        <th>Daily Velocity</th>
                        <th>Est. Days Left</th>
                        <th>Suggested Order Qty</th>
                        <th>Status</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reorderItems as $item)
                        <tr>
                            <td>
                                <span class="product-name">{{ $item->name }}</span>
                                <span class="product-sku">{{ $item->sku }}</span>
                            </td>
                            <td>{{ $item->category }}</td>
                            <td><strong>{{ number_format($item->quantity) }}</strong></td>
                            <td>{{ number_format($item->low_stock_threshold) }}</td>
                            <td>{{ $item->daily_velocity }}/day</td>
                            <td>
                                @if ($item->days_remaining === 999)
                                    <span>—</span>
                                @else
                                    <strong>{{ $item->days_remaining }} days</strong>
                                @endif
                            </td>
                            <td>
                                <span class="suggested-qty">+{{ number_format($item->suggested_reorder) }} units</span>
                            </td>
                            <td>
                                @if ($item->urgency === 'out_of_stock')
                                    <span class="status-pill out">Out of Stock</span>
                                @elseif ($item->urgency === 'critical')
                                    <span class="status-pill critical">Critical</span>
                                @elseif ($item->urgency === 'warning')
                                    <span class="status-pill warning">Reorder Soon</span>
                                @else
                                    <span class="status-pill healthy">Healthy</span>
                                @endif
                            </td>
                            <td>
                                <div class="row-actions" style="justify-content: flex-end;">
                                    <a class="button button-quiet" href="{{ route('inventory.stock-in') }}">Stock In</a>
                                    <a class="button button-primary" href="{{ route('purchase-orders.create') }}">Create PO</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <strong>All stock levels are optimal!</strong>
                                    <p>No products currently require immediate replenishment.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

@push('styles')
    <style>
        .suggested-qty { font-weight: 700; color: #245b43; }
        .status-pill { display: inline-flex; align-items: center; padding: 3px 8px; border-radius: 12px; font-size: 8px; font-weight: 700; text-transform: uppercase; }
        .status-pill.out { color: #b94d41; background: #faece8; }
        .status-pill.critical { color: #b94d41; background: #faece8; }
        .status-pill.warning { color: #b87925; background: #fdf5e6; }
        .status-pill.healthy { color: #245b43; background: #e3ebd6; }
    </style>
@endpush
