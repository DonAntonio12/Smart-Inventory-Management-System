@extends('products.layout')

@section('page_title', $title)

@php
    $activeRoute = match ($type) {
        'stock_in' => 'inventory.stock-in',
        'stock_out' => 'inventory.stock-out',
        default => 'inventory.adjustment',
    };
    $storeRoute = match ($type) {
        'stock_in' => 'inventory.stock-in.store',
        'stock_out' => 'inventory.stock-out.store',
        default => 'inventory.adjustment.store',
    };
    $submitLabel = match ($type) {
        'stock_in' => 'Receive stock',
        'stock_out' => 'Issue stock',
        default => 'Save adjustment',
    };
@endphp

@push('styles')
    <style>
        .movement-layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(280px, .82fr); gap: 14px; align-items: start; }
        .movement-form { padding: 19px; }
        .movement-form h2 { margin: 0 0 6px; font: 700 14px 'Manrope', sans-serif; }
        .movement-form > p { margin: 0 0 18px; color: #7c887f; font-size: 10px; line-height: 1.6; }
        .current-quantity { display: flex; align-items: center; justify-content: space-between; margin-top: 13px; padding: 11px 12px; border: 1px solid #e7ebe3; border-radius: 4px; color: #77837a; background: #f7f8f3; font-size: 10px; }
        .current-quantity strong { color: #29372e; font: 700 16px 'Manrope', sans-serif; }
        .field select, .field textarea { display: block; width: 100%; padding: 0 10px; border: 1px solid #dfe4da; border-radius: 4px; color: var(--ink); background: #fff; font-size: 11px; }
        .field select { height: 39px; }
        .field textarea { min-height: 82px; padding-top: 10px; resize: vertical; }
        .movement-recent { margin-top: 14px; }
        .movement-recent .inventory-panel-heading { padding: 15px; }
        .form-help { margin-top: 6px; color: #88938b; font-size: 9px; }
        @media (max-width: 760px) { .movement-layout { grid-template-columns: 1fr; } }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Inventory</p><h1>{{ $title }}</h1><p>{{ $type === 'stock_in' ? 'Record received products and add them to available stock.' : ($type === 'stock_out' ? 'Record issued or sold products and reduce available stock.' : 'Reconcile the system quantity with a verified physical count.') }}</p></div>
        <a class="button button-quiet" href="{{ route('inventory.index') }}">Stock Overview</a>
    </div>

    <nav class="product-tabs" aria-label="Inventory sections">
        <a class="product-tab" href="{{ route('inventory.index') }}">Stock Overview</a>
        <a class="product-tab {{ $type === 'stock_in' ? 'active' : '' }}" href="{{ route('inventory.stock-in') }}">Stock In</a>
        <a class="product-tab {{ $type === 'stock_out' ? 'active' : '' }}" href="{{ route('inventory.stock-out') }}">Stock Out</a>
        <a class="product-tab {{ $type === 'adjustment' ? 'active' : '' }}" href="{{ route('inventory.adjustment') }}">Adjustment</a>
        <a class="product-tab" href="{{ route('inventory.low-stock') }}">Low Stock</a>
        <a class="product-tab" href="{{ route('inventory.expiring-products') }}">Expiring</a>
    </nav>

    <div class="movement-layout">
        <section class="panel movement-form">
            <h2>{{ $submitLabel }}</h2>
            <p>The product quantity and movement record are saved together.</p>
            <form method="POST" action="{{ route($storeRoute) }}">
                @csrf
                <div class="field">
                    <label for="product_id">Product</label>
                    <select id="product_id" name="product_id" required>
                        <option value="">Choose a product</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" data-current-quantity="{{ $product->quantity }}" @selected((string) old('product_id') === (string) $product->id)>{{ $product->name }} ({{ $product->sku }})</option>
                        @endforeach
                    </select>
                    @error('product_id')<p class="field-error">{{ $message }}</p>@enderror
                    <div class="current-quantity"><span>Current quantity</span><strong data-current-quantity-display>—</strong></div>
                </div>
                <div class="field" style="margin-top: 16px">
                    <label for="{{ $type === 'adjustment' ? 'new_quantity' : 'quantity' }}">{{ $type === 'adjustment' ? 'New counted quantity' : 'Quantity' }}</label>
                    <input id="{{ $type === 'adjustment' ? 'new_quantity' : 'quantity' }}" name="{{ $type === 'adjustment' ? 'new_quantity' : 'quantity' }}" type="number" min="{{ $type === 'adjustment' ? 0 : 1 }}" step="1" value="{{ old($type === 'adjustment' ? 'new_quantity' : 'quantity') }}" required>
                    <p class="form-help">{{ $type === 'adjustment' ? 'Enter the verified quantity currently on the shelf.' : ($type === 'stock_out' ? 'Must not exceed the available quantity.' : 'Enter the number of units received.') }}</p>
                    @error($type === 'adjustment' ? 'new_quantity' : 'quantity')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field" style="margin-top: 16px">
                    <label for="description">{{ $type === 'adjustment' ? 'Reason for adjustment' : 'Notes' }}{{ $type === 'adjustment' ? '' : ' (optional)' }}</label>
                    <textarea id="description" name="description" maxlength="255" placeholder="{{ $type === 'stock_in' ? 'Supplier delivery, purchase order, etc.' : ($type === 'stock_out' ? 'Sale, damaged item, internal use, etc.' : 'Explain why the system count is changing.') }}" @required($type === 'adjustment')>{{ old('description') }}</textarea>
                    @error('description')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-actions"><button class="button button-primary" type="submit">{{ $submitLabel }}</button></div>
            </form>
        </section>

        <section class="panel">
            <header class="inventory-panel-heading"><div><h2>Recent {{ str_replace('_', ' ', $type) }}</h2><p>Latest movements of this type</p></div></header>
            @if ($recentTransactions->isNotEmpty())
                <div class="table-wrap"><table><thead><tr><th>Product</th><th>Quantity</th><th>Date</th></tr></thead><tbody>
                    @foreach ($recentTransactions as $transaction)
                        <tr><td><span class="product-name">{{ $transaction->product?->name ?? 'Deleted product' }}</span><span class="product-sku">{{ $transaction->description ?: $transaction->product?->sku }}</span></td><td>{{ $transaction->quantity > 0 ? '+' : '' }}{{ number_format($transaction->quantity) }}</td><td>{{ $transaction->occurred_at->format('M j, g:i A') }}</td></tr>
                    @endforeach
                </tbody></table></div>
            @else
                <div class="empty-state"><strong>No movements yet</strong>New entries will appear here.</div>
            @endif
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        const productSelect = document.getElementById('product_id');
        const currentQuantityDisplay = document.querySelector('[data-current-quantity-display]');

        const updateCurrentQuantity = () => {
            const selectedOption = productSelect.selectedOptions[0];
            currentQuantityDisplay.textContent = selectedOption?.dataset.currentQuantity ?? '—';
        };

        productSelect.addEventListener('change', updateCurrentQuantity);
        updateCurrentQuantity();
    </script>
@endpush
