@extends('products.layout')

@php
    $orderItems = old('items', [['product_id' => '', 'quantity' => 1, 'unit_cost' => '0.00']]);
@endphp

@section('page_title', 'New Purchase Order')

@push('styles')
    <style>
        .po-form { padding: 18px; }
        .po-form h2 { margin: 0 0 14px; font: 700 13px 'Manrope', sans-serif; }
        .po-header-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-bottom: 22px; }
        .po-lines { display: grid; gap: 8px; }
        .po-line { display: grid; grid-template-columns: minmax(180px, 1.5fr) minmax(80px, .55fr) minmax(100px, .7fr) 34px; align-items: end; gap: 8px; padding: 11px; border: 1px solid #e8ebe4; border-radius: 4px; background: #fbfcf8; }
        .po-line .field { min-width: 0; }
        .po-line .field select, .po-line .field input, .po-form textarea { display: block; width: 100%; height: 37px; padding: 0 9px; border: 1px solid #dfe4da; border-radius: 4px; color: var(--ink); background: white; font-size: 10px; }
        .po-form textarea { height: auto; min-height: 72px; padding: 9px; resize: vertical; }
        .po-line label, .po-form > .field label { display: block; margin-bottom: 6px; color: #435047; font-size: 9px; font-weight: 700; }
        .po-line-remove { display: grid; width: 32px; height: 37px; place-items: center; border: 1px solid #ead8d2; border-radius: 4px; color: #ad5547; background: #fff8f5; font-size: 17px; cursor: pointer; }
        .po-line-remove:hover { background: #faece8; }
        .po-line-remove:disabled { opacity: .35; cursor: default; }
        .po-items-heading { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
        .po-items-heading h2 { margin: 0; }
        .po-form .field-error { margin: 5px 0 0; color: #af4b3e; font-size: 9px; }
        .po-subtotal { margin-top: 13px; color: #5e6b61; text-align: right; font-size: 10px; }
        .po-subtotal strong { margin-left: 8px; color: #26372c; font: 700 15px 'Manrope', sans-serif; }
        @media (max-width: 650px) {
            .po-form { padding: 13px; }
            .po-header-fields { grid-template-columns: 1fr; }
            .po-line { grid-template-columns: minmax(0, 1fr) minmax(82px, .7fr) 32px; }
            .po-line .field:first-child { grid-column: 1 / -1; }
            .po-line-remove { grid-column: 3; grid-row: 2; }
        }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Procurement</p><h1>New Purchase Order</h1><p>Select a supplier and add the products you need to replenish.</p></div>
        <a class="button button-quiet" href="{{ route('purchase-orders.index') }}">Back to orders</a>
    </div>

    <nav class="product-tabs" aria-label="Supplier sections">
        <a class="product-tab" href="{{ route('suppliers.index') }}">Suppliers</a>
        <a class="product-tab active" href="{{ route('purchase-orders.index') }}">Purchase Orders</a>
    </nav>

    @if ($suppliers->isEmpty())
        <div class="flash" role="status">Add a supplier before creating a purchase order. <a href="{{ route('suppliers.create') }}"><strong>Add supplier</strong></a></div>
    @elseif ($products->isEmpty())
        <div class="flash" role="status">Add products to your catalog before creating a purchase order. <a href="{{ route('products.create') }}"><strong>Add product</strong></a></div>
    @else
        <section class="panel po-form">
            <form method="POST" action="{{ route('purchase-orders.store') }}" id="purchase-order-form">
                @csrf
                <div class="po-header-fields">
                    <div class="field">
                        <label for="supplier_id">Supplier</label>
                        <select id="supplier_id" name="supplier_id" required>
                            <option value="">Choose a supplier</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected((string) old('supplier_id') === (string) $supplier->id)>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label for="expected_at">Expected delivery</label>
                        <input id="expected_at" name="expected_at" type="date" min="{{ now()->toDateString() }}" value="{{ old('expected_at') }}">
                        @error('expected_at')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="po-items-heading"><h2>Order items</h2><button class="button button-quiet" type="button" id="add-order-item">Add item</button></div>
                @error('items')<p class="field-error">{{ $message }}</p>@enderror
                <div class="po-lines" id="order-items">
                    @foreach ($orderItems as $index => $item)
                        <div class="po-line" data-order-line>
                            <div class="field">
                                <label for="item-product-{{ $index }}">Product</label>
                                <select id="item-product-{{ $index }}" name="items[{{ $index }}][product_id]" required>
                                    <option value="">Choose product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" @selected((string) ($item['product_id'] ?? '') === (string) $product->id)>{{ $product->name }} · {{ $product->sku }}</option>
                                    @endforeach
                                </select>
                                @error('items.'.$index.'.product_id')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="field">
                                <label for="item-quantity-{{ $index }}">Quantity</label>
                                <input id="item-quantity-{{ $index }}" name="items[{{ $index }}][quantity]" type="number" min="1" step="1" value="{{ $item['quantity'] ?? 1 }}" required>
                                @error('items.'.$index.'.quantity')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="field">
                                <label for="item-cost-{{ $index }}">Unit cost</label>
                                <input id="item-cost-{{ $index }}" name="items[{{ $index }}][unit_cost]" type="number" min="0" step="0.01" value="{{ $item['unit_cost'] ?? '0.00' }}" required>
                                @error('items.'.$index.'.unit_cost')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                            <button class="po-line-remove" type="button" data-remove-order-line aria-label="Remove order line" @disabled(count($orderItems) === 1)>×</button>
                        </div>
                    @endforeach
                </div>
                <div class="po-subtotal">Estimated order total <strong id="po-total">₱0.00</strong></div>
                <div class="field" style="margin-top: 18px">
                    <label for="notes">Order notes</label>
                    <textarea id="notes" name="notes" maxlength="2000" placeholder="Delivery instructions, terms, or internal notes">{{ old('notes') }}</textarea>
                    @error('notes')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-actions"><a class="button button-quiet" href="{{ route('purchase-orders.index') }}">Cancel</a><button class="button button-primary" type="submit">Create purchase order</button></div>
            </form>
        </section>

        <template id="order-line-template">
            <div class="po-line" data-order-line>
                <div class="field"><label for="item-product-__INDEX__">Product</label><select id="item-product-__INDEX__" name="items[__INDEX__][product_id]" required><option value="">Choose product</option>@foreach ($products as $product)<option value="{{ $product->id }}">{{ $product->name }} · {{ $product->sku }}</option>@endforeach</select></div>
                <div class="field"><label for="item-quantity-__INDEX__">Quantity</label><input id="item-quantity-__INDEX__" name="items[__INDEX__][quantity]" type="number" min="1" step="1" value="1" required></div>
                <div class="field"><label for="item-cost-__INDEX__">Unit cost</label><input id="item-cost-__INDEX__" name="items[__INDEX__][unit_cost]" type="number" min="0" step="0.01" value="0.00" required></div>
                <button class="po-line-remove" type="button" data-remove-order-line aria-label="Remove order line">×</button>
            </div>
        </template>
    @endif
@endsection

@push('scripts')
    <script>
        const orderLines = document.getElementById('order-items');
        const orderLineTemplate = document.getElementById('order-line-template');
        const orderTotal = document.getElementById('po-total');

        if (orderLines && orderLineTemplate && orderTotal) {
            let nextOrderLineIndex = orderLines.querySelectorAll('[data-order-line]').length;

            const updateOrderTotal = () => {
                const total = [...orderLines.querySelectorAll('[data-order-line]')].reduce((sum, line) => {
                    const quantity = Number(line.querySelector('[name$="[quantity]"]').value) || 0;
                    const cost = Number(line.querySelector('[name$="[unit_cost]"]').value) || 0;

                    return sum + quantity * cost;
                }, 0);
                orderTotal.textContent = `₱${total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                orderLines.querySelectorAll('[data-remove-order-line]').forEach((button) => {
                    button.disabled = orderLines.querySelectorAll('[data-order-line]').length === 1;
                });
            };

            document.getElementById('add-order-item').addEventListener('click', () => {
                const templateMarkup = orderLineTemplate.innerHTML.replaceAll('__INDEX__', String(nextOrderLineIndex++));
                orderLines.insertAdjacentHTML('beforeend', templateMarkup);
                updateOrderTotal();
            });

            orderLines.addEventListener('click', (event) => {
                if (event.target.closest('[data-remove-order-line]') && orderLines.querySelectorAll('[data-order-line]').length > 1) {
                    event.target.closest('[data-order-line]').remove();
                    updateOrderTotal();
                }
            });

            orderLines.addEventListener('input', updateOrderTotal);
            updateOrderTotal();
        }
    </script>
@endpush
