@extends('products.layout')

@section('page_title', 'New Sale')

@push('styles')
    <style>
        .sales-checkout { width: min(940px, 100%); }
        .sale-heading-actions { display: flex; align-items: center; gap: 8px; }
        .sale-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 21px; }
        .sale-meta .field input, .sale-line select, .sale-line input { display: block; width: 100%; height: 38px; padding: 0 9px; border: 1px solid #dfe4da; border-radius: 4px; color: var(--ink); background: #fff; font-size: 10px; }
        .sale-meta .field label, .sale-line label { display: block; margin-bottom: 6px; color: #435047; font-size: 9px; font-weight: 700; }
        .sale-lines-heading { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 9px; }
        .sale-lines-heading h2 { margin: 0; font: 700 13px 'Manrope', sans-serif; }
        .sale-lines { display: grid; gap: 7px; }
        .sale-line { display: grid; grid-template-columns: minmax(180px, 1.5fr) minmax(76px, .55fr) minmax(100px, .7fr) minmax(100px, .7fr) 32px; align-items: end; gap: 8px; padding: 10px; border: 1px solid #e8ebe4; border-radius: 4px; background: #fbfcf8; }
        .sale-line .field { min-width: 0; }
        .sale-line-stock { display: block; margin-top: 4px; color: #879188; font-size: 8px; }
        .sale-line-remove { display: grid; width: 31px; height: 37px; place-items: center; border: 1px solid #ead8d2; border-radius: 4px; color: #ad5547; background: #fff8f5; font-size: 17px; cursor: pointer; }
        .sale-line-remove:disabled { opacity: .35; cursor: default; }
        .sale-total { display: flex; align-items: center; justify-content: flex-end; gap: 12px; margin-top: 15px; padding-top: 13px; border-top: 1px solid #e9ece5; color: #647168; font-size: 10px; }
        .sale-total strong { color: #26372c; font: 700 21px 'Manrope', sans-serif; }
        .sale-submit { display: flex; justify-content: flex-end; gap: 8px; margin-top: 20px; }
        .sale-note { margin: 16px 0 0; color: #879188; font-size: 9px; }
        @media (max-width: 700px) {
            .sale-line { grid-template-columns: minmax(0, 1fr) minmax(80px, .6fr) 31px; }
            .sale-line .field:first-child { grid-column: 1 / -1; }
            .sale-line-remove { grid-column: 3; grid-row: 2; }
        }
        @media (max-width: 500px) {
            .sale-meta { grid-template-columns: 1fr; }
            .sale-line { grid-template-columns: minmax(0, 1fr) minmax(75px, .6fr) 29px; padding: 8px; gap: 6px; }
            .sale-line .field:nth-child(3), .sale-line .field:nth-child(4) { grid-column: span 1; }
            .sale-line-remove { grid-column: 3; grid-row: 2; }
        }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Sales</p><h1>New Sale</h1><p>Complete a checkout and update product stock in one step.</p></div>
        <div class="sale-heading-actions"><a class="button button-quiet" href="{{ route('sales.transactions') }}">Transactions</a></div>
    </div>

    <nav class="product-tabs" aria-label="Sales sections">
        <a class="product-tab active" href="{{ route('sales.create') }}">New Sale</a>
        <a class="product-tab" href="{{ route('sales.transactions') }}">Transactions</a>
    </nav>

    @if ($products->isEmpty())
        <section class="panel empty-state"><strong>No products available</strong>Add products to your catalog before recording a sale.<p style="margin: 16px 0 0"><a class="button button-primary" href="{{ route('products.create') }}">Add product</a></p></section>
    @else
        @php($saleItems = old('items', [['product_id' => '', 'quantity' => 1, 'unit_price' => '0.00']]))
        <section class="panel form-panel sales-checkout">
            <form method="POST" action="{{ route('sales.store') }}" id="sale-form">
                @csrf
                <div class="sale-meta">
                    <div class="field"><label for="customer_name">Customer name <span style="color:#879188;font-weight:400">(optional)</span></label><input id="customer_name" name="customer_name" value="{{ old('customer_name') }}" maxlength="255" placeholder="Walk-in customer"></div>
                    <div class="field"><label for="notes">Sale notes <span style="color:#879188;font-weight:400">(optional)</span></label><input id="notes" name="notes" value="{{ old('notes') }}" maxlength="255" placeholder="Payment or order note"></div>
                </div>
                <div class="sale-lines-heading"><h2>Items</h2><button class="button button-quiet" id="add-sale-item" type="button">Add item</button></div>
                @error('items')<p class="field-error">{{ $message }}</p>@enderror
                <div class="sale-lines" id="sale-lines">
                    @foreach ($saleItems as $index => $item)
                        <div class="sale-line" data-sale-line>
                            <div class="field">
                                <label for="sale-product-{{ $index }}">Product</label>
                                <select id="sale-product-{{ $index }}" name="items[{{ $index }}][product_id]" data-product-select required>
                                    <option value="">Select a product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" data-stock="{{ $product->quantity }}" @selected((string) ($item['product_id'] ?? '') === (string) $product->id)>{{ $product->name }} · {{ $product->sku }}</option>
                                    @endforeach
                                </select>
                                <span class="sale-line-stock" data-stock-label>Available: —</span>
                                @error('items.'.$index.'.product_id')<p class="field-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="field"><label for="sale-quantity-{{ $index }}">Quantity</label><input id="sale-quantity-{{ $index }}" name="items[{{ $index }}][quantity]" data-sale-quantity type="number" min="1" step="1" value="{{ $item['quantity'] ?? 1 }}" required>@error('items.'.$index.'.quantity')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div class="field"><label for="sale-price-{{ $index }}">Unit price</label><input id="sale-price-{{ $index }}" name="items[{{ $index }}][unit_price]" data-sale-price type="number" min="0" step="0.01" value="{{ $item['unit_price'] ?? '0.00' }}" required>@error('items.'.$index.'.unit_price')<p class="field-error">{{ $message }}</p>@enderror</div>
                            <div class="field"><label>Line total</label><input data-line-total value="₱0.00" readonly aria-label="Line total"></div>
                            <button class="sale-line-remove" type="button" data-remove-sale-line aria-label="Remove sale item" @disabled(count($saleItems) === 1)>×</button>
                        </div>
                    @endforeach
                </div>
                <div class="sale-total"><span>Sale total</span><strong id="sale-total">₱0.00</strong></div>
                <div class="sale-submit"><a class="button button-quiet" href="{{ route('sales.transactions') }}">Cancel</a><button class="button button-primary" type="submit">Complete sale</button></div>
                <p class="sale-note">Stock is checked and updated when the sale is submitted. Insufficient stock prevents the entire sale.</p>
            </form>
        </section>

        <template id="sale-line-template">
            <div class="sale-line" data-sale-line>
                <div class="field"><label for="sale-product-__INDEX__">Product</label><select id="sale-product-__INDEX__" name="items[__INDEX__][product_id]" data-product-select required><option value="">Select a product</option>@foreach ($products as $product)<option value="{{ $product->id }}" data-stock="{{ $product->quantity }}">{{ $product->name }} · {{ $product->sku }}</option>@endforeach</select><span class="sale-line-stock" data-stock-label>Available: —</span></div>
                <div class="field"><label for="sale-quantity-__INDEX__">Quantity</label><input id="sale-quantity-__INDEX__" name="items[__INDEX__][quantity]" data-sale-quantity type="number" min="1" step="1" value="1" required></div>
                <div class="field"><label for="sale-price-__INDEX__">Unit price</label><input id="sale-price-__INDEX__" name="items[__INDEX__][unit_price]" data-sale-price type="number" min="0" step="0.01" value="0.00" required></div>
                <div class="field"><label>Line total</label><input data-line-total value="₱0.00" readonly aria-label="Line total"></div>
                <button class="sale-line-remove" type="button" data-remove-sale-line aria-label="Remove sale item">×</button>
            </div>
        </template>
    @endif
@endsection

@push('scripts')
    <script>
        const saleLines = document.getElementById('sale-lines');
        const saleLineTemplate = document.getElementById('sale-line-template');
        const saleTotal = document.getElementById('sale-total');

        if (saleLines && saleLineTemplate && saleTotal) {
            let nextLineIndex = saleLines.querySelectorAll('[data-sale-line]').length;

            const formatCurrency = (amount) => `₱${amount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            const updateSaleTotals = () => {
                let total = 0;
                saleLines.querySelectorAll('[data-sale-line]').forEach((line) => {
                    const quantity = Number(line.querySelector('[data-sale-quantity]').value) || 0;
                    const price = Number(line.querySelector('[data-sale-price]').value) || 0;
                    const lineTotal = quantity * price;
                    line.querySelector('[data-line-total]').value = formatCurrency(lineTotal);
                    total += lineTotal;

                    const productSelect = line.querySelector('[data-product-select]');
                    const selectedProduct = productSelect.selectedOptions[0];
                    const stockLabel = line.querySelector('[data-stock-label]');
                    stockLabel.textContent = selectedProduct?.dataset.stock ? `Available: ${selectedProduct.dataset.stock}` : 'Available: —';
                });

                saleTotal.textContent = formatCurrency(total);
                saleLines.querySelectorAll('[data-remove-sale-line]').forEach((button) => {
                    button.disabled = saleLines.querySelectorAll('[data-sale-line]').length === 1;
                });
            };

            document.getElementById('add-sale-item').addEventListener('click', () => {
                const markup = saleLineTemplate.innerHTML.replaceAll('__INDEX__', String(nextLineIndex++));
                saleLines.insertAdjacentHTML('beforeend', markup);
                updateSaleTotals();
            });

            saleLines.addEventListener('input', updateSaleTotals);
            saleLines.addEventListener('change', updateSaleTotals);
            saleLines.addEventListener('click', (event) => {
                if (event.target.closest('[data-remove-sale-line]') && saleLines.querySelectorAll('[data-sale-line]').length > 1) {
                    event.target.closest('[data-sale-line]').remove();
                    updateSaleTotals();
                }
            });

            updateSaleTotals();
        }
    </script>
@endpush
