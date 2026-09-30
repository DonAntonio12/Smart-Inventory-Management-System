@extends('products.layout')

@section('page_title', 'All Products')

@push('styles')
    <style>
        .delete-dialog { width: min(450px, calc(100% - 32px)); max-width: none; padding: 0; border: 1px solid #dfe4da; border-radius: 7px; color: #18231d; background: #fffefa; box-shadow: 0 24px 80px rgba(12, 28, 18, .28); }
        .delete-dialog::backdrop { background: rgba(15, 31, 22, .58); backdrop-filter: blur(2px); }
        .delete-dialog[open] { animation: dialog-enter .16s ease-out; }
        .delete-dialog-content { padding: 23px 24px 20px; }
        .delete-dialog-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
        .delete-dialog-icon { display: grid; width: 38px; height: 38px; place-items: center; border-radius: 8px; color: #a94438; background: #faece8; }
        .delete-dialog-icon svg { width: 19px; height: 19px; }
        .delete-dialog-close { display: grid; width: 30px; height: 30px; place-items: center; border: 0; border-radius: 4px; color: #748077; background: transparent; font-size: 20px; cursor: pointer; }
        .delete-dialog-close:hover { color: #26342b; background: #f0f2ec; }
        .delete-dialog h2 { margin: 18px 0 8px; font: 700 19px/1.3 'Manrope', sans-serif; }
        .delete-dialog p { margin: 0; color: #6f7b72; font-size: 12px; line-height: 1.65; }
        .delete-dialog-product { color: #29372e; font-weight: 700; }
        .delete-dialog-actions { display: flex; justify-content: flex-end; gap: 8px; padding: 14px 24px; border-top: 1px solid #e9ece5; background: #fafbf7; }
        @keyframes dialog-enter { from { opacity: 0; transform: translateY(7px) scale(.99); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @media (max-width: 480px) {
            .delete-dialog-content { padding: 20px 18px 17px; }
            .delete-dialog-actions { padding: 12px 18px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .delete-dialog[open] { animation: none; }
        }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Product catalog</p><h1>All Products</h1><p>Keep product details, stock levels, and reorder points in one place.</p></div>
        <a class="button button-primary" href="{{ route('products.create') }}"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>Add product</a>
    </div>

    <nav class="product-tabs" aria-label="Product sections">
        <a class="product-tab active" href="{{ route('products.index') }}">All Products</a>
        <a class="product-tab" href="{{ route('products.categories') }}">Categories</a>
        <a class="product-tab" href="{{ route('products.brands') }}">Brands</a>
    </nav>

    <section class="stat-grid" aria-label="Product summary">
        <article class="stat"><span>Products in catalog</span><strong>{{ number_format($productCount) }}</strong></article>
        <article class="stat"><span>Total units in stock</span><strong>{{ number_format($unitCount) }}</strong></article>
        <article class="stat"><span>Products to reorder</span><strong>{{ number_format($lowStockCount) }}</strong></article>
    </section>

    <section class="panel">
        <form class="toolbar" method="GET" action="{{ route('products.index') }}">
            <input type="search" name="search" value="{{ $search }}" placeholder="Search name, SKU, category, or brand" aria-label="Search products">
            <select name="category" aria-label="Filter by category">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}" @selected($selectedCategory === $category)>{{ $category }}</option>
                @endforeach
                <option value="__uncategorized" @selected($selectedCategory === '__uncategorized')>Uncategorized</option>
            </select>
            <select name="brand" aria-label="Filter by brand">
                <option value="">All brands</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand }}" @selected($selectedBrand === $brand)>{{ $brand }}</option>
                @endforeach
                <option value="__unbranded" @selected($selectedBrand === '__unbranded')>Unbranded</option>
            </select>
            <select name="stock" aria-label="Filter by stock status">
                <option value="">Any stock level</option>
                <option value="low" @selected($selectedStock === 'low')>Low stock</option>
                <option value="out" @selected($selectedStock === 'out')>Out of stock</option>
            </select>
            <button class="button button-quiet" type="submit">Apply filters</button>
        </form>

        @if ($products->isNotEmpty())
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Product</th><th>Category</th><th>Brand</th><th>On hand</th><th>Reorder at</th><th>Expiration / status</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach ($products as $product)
                            @php
                                $stockState = $product->quantity === 0 ? 'out' : ($product->quantity <= $product->low_stock_threshold ? 'low' : '');
                                $stockLabel = $product->quantity === 0 ? 'Out of stock' : ($product->quantity <= $product->low_stock_threshold ? 'Low stock' : 'In stock');
                                $isExpired = $product->expiration_date?->lt(today()) ?? false;
                                $expiresSoon = $product->expiration_date && $product->expiration_date->lte(today()->addDays(30));
                            @endphp
                            <tr>
                                <td><span class="product-name">{{ $product->name }}</span><span class="product-sku">{{ $product->sku }}</span></td>
                                <td>{{ $product->category ?: 'Uncategorized' }}</td>
                                <td>{{ $product->brand ?: '—' }}</td>
                                <td><span class="stock-pill {{ $stockState }}">{{ number_format($product->quantity) }} · {{ $stockLabel }}</span></td>
                                <td>{{ number_format($product->low_stock_threshold) }}</td>
                                <td>
                                    @if ($product->expiration_date)
                                        <span>{{ $product->expiration_date->format('M j, Y') }}</span>
                                        <span class="stock-pill {{ $isExpired ? 'out' : ($expiresSoon ? 'low' : '') }}">{{ $isExpired ? 'Expired' : ($expiresSoon ? 'Expiring soon' : 'Not expiring') }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <a class="button button-quiet" href="{{ route('products.edit', $product) }}">Edit</a>
                                        <button class="button button-danger" type="button" data-delete-open data-product-name="{{ $product->name }}" data-delete-action="{{ route('products.destroy', $product) }}" aria-label="Delete {{ $product->name }}">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="table-footer">
                <span>Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ number_format($products->total()) }} products</span>
                <div class="pagination">
                    <a class="button button-quiet" href="{{ $products->previousPageUrl() ?: '#' }}" @if (!$products->previousPageUrl()) aria-disabled="true" @endif>Previous</a>
                    <a class="button button-quiet" href="{{ $products->nextPageUrl() ?: '#' }}" @if (!$products->nextPageUrl()) aria-disabled="true" @endif>Next</a>
                </div>
            </div>
        @else
            <div class="empty-state"><strong>{{ $search || $selectedCategory || $selectedBrand || $selectedStock ? 'No matching products' : 'Your catalog is ready' }}</strong>{{ $search || $selectedCategory || $selectedBrand || $selectedStock ? 'Try changing your search or filters.' : 'Add your first product to start tracking stock.' }}
                @unless ($productCount)<p style="margin: 16px 0 0"><a class="button button-primary" href="{{ route('products.create') }}">Add your first product</a></p>@endunless
            </div>
        @endif
    </section>

    <dialog class="delete-dialog" id="delete-product-dialog" aria-labelledby="delete-dialog-title" aria-describedby="delete-dialog-description">
        <div class="delete-dialog-content">
            <div class="delete-dialog-top">
                <span class="delete-dialog-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M4 7h16m-10 4v6m4-6v6M6 7l1 14h10l1-14M9 7V4h6v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                <button class="delete-dialog-close" type="button" data-delete-cancel aria-label="Close dialog">×</button>
            </div>
            <h2 id="delete-dialog-title">Delete this product?</h2>
            <p id="delete-dialog-description">You’re about to remove <span class="delete-dialog-product" data-delete-product-name>this product</span> from your catalog. This action can’t be undone.</p>
        </div>
        <form class="delete-dialog-actions" id="delete-product-form" method="POST" action="">
            @csrf
            @method('DELETE')
            <button class="button button-quiet" type="button" data-delete-cancel>Cancel</button>
            <button class="button button-danger" type="submit">Delete product</button>
        </form>
    </dialog>

    <script>
        const deleteDialog = document.getElementById('delete-product-dialog');
        const deleteForm = document.getElementById('delete-product-form');
        const deleteProductName = deleteDialog.querySelector('[data-delete-product-name]');
        let deleteTrigger = null;

        document.querySelectorAll('[data-delete-open]').forEach((button) => {
            button.addEventListener('click', () => {
                deleteTrigger = button;
                deleteProductName.textContent = button.dataset.productName;
                deleteForm.action = button.dataset.deleteAction;
                deleteDialog.showModal();
                deleteDialog.querySelector('[data-delete-cancel]').focus();
            });
        });

        deleteDialog.querySelectorAll('[data-delete-cancel]').forEach((button) => {
            button.addEventListener('click', () => deleteDialog.close());
        });

        deleteDialog.addEventListener('close', () => deleteTrigger?.focus());
    </script>
@endsection
