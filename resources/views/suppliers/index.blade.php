@extends('products.layout')

@section('page_title', 'Suppliers')

@push('styles')
    <style>
        .supplier-dialog { width: min(430px, calc(100% - 32px)); max-width: none; padding: 0; border: 1px solid #dfe4da; border-radius: 7px; color: #18231d; background: #fffefa; box-shadow: 0 24px 80px rgba(12, 28, 18, .28); }
        .supplier-dialog::backdrop { background: rgba(15, 31, 22, .58); backdrop-filter: blur(2px); }
        .supplier-dialog-content { padding: 22px; }
        .supplier-dialog h2 { margin: 0 0 8px; font: 700 18px 'Manrope', sans-serif; }
        .supplier-dialog p { margin: 0; color: #6f7b72; font-size: 11px; line-height: 1.6; }
        .supplier-dialog-actions { display: flex; justify-content: flex-end; gap: 8px; padding: 13px 22px; border-top: 1px solid #e9ece5; background: #fafbf7; }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Procurement</p><h1>Suppliers</h1><p>Manage vendor contacts and see who supplies your purchase orders.</p></div>
        <a class="button button-primary" href="{{ route('suppliers.create') }}">Add supplier</a>
    </div>

    <nav class="product-tabs" aria-label="Supplier sections">
        <a class="product-tab active" href="{{ route('suppliers.index') }}">Suppliers</a>
        <a class="product-tab" href="{{ route('purchase-orders.index') }}">Purchase Orders</a>
    </nav>

    @if ($errors->has('supplier'))<div class="flash" role="alert">{{ $errors->first('supplier') }}</div>@endif

    <section class="stat-grid" aria-label="Supplier summary">
        <article class="stat"><span>Suppliers</span><strong>{{ number_format($supplierCount) }}</strong></article>
        <article class="stat"><span>With purchase history</span><strong>{{ number_format($orderCount) }}</strong></article>
        <article class="stat"><span>Orders on record</span><strong>{{ number_format(\App\Models\PurchaseOrder::query()->count()) }}</strong></article>
    </section>

    <section class="panel">
        <form class="toolbar" method="GET" action="{{ route('suppliers.index') }}">
            <input type="search" name="search" value="{{ $search }}" placeholder="Search supplier, contact, or email" aria-label="Search suppliers">
            <button class="button button-quiet" type="submit">Search</button>
        </form>
        @if ($suppliers->isNotEmpty())
            <div class="table-wrap"><table>
                <thead><tr><th>Supplier</th><th>Contact</th><th>Phone</th><th>Purchase orders</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach ($suppliers as $supplier)
                        <tr>
                            <td><span class="product-name">{{ $supplier->name }}</span><span class="product-sku">{{ $supplier->email ?: 'No email on file' }}</span></td>
                            <td>{{ $supplier->contact_name ?: '—' }}</td>
                            <td>{{ $supplier->phone ?: '—' }}</td>
                            <td>{{ number_format($supplier->purchase_orders_count) }}</td>
                            <td><div class="row-actions">
                                <a class="button button-quiet" href="{{ route('suppliers.edit', $supplier) }}">Edit</a>
                                @if ($supplier->purchase_orders_count === 0)
                                    <button class="button button-danger" type="button" data-supplier-delete-open data-supplier-name="{{ $supplier->name }}" data-delete-action="{{ route('suppliers.destroy', $supplier) }}">Delete</button>
                                @else
                                    <span class="button button-quiet" title="Suppliers with purchase history cannot be deleted">Has orders</span>
                                @endif
                            </div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
            <div class="table-footer"><span>Showing {{ $suppliers->firstItem() }}–{{ $suppliers->lastItem() }} of {{ number_format($suppliers->total()) }} suppliers</span><div class="pagination"><a class="button button-quiet" href="{{ $suppliers->previousPageUrl() ?: '#' }}" @if (!$suppliers->previousPageUrl()) aria-disabled="true" @endif>Previous</a><a class="button button-quiet" href="{{ $suppliers->nextPageUrl() ?: '#' }}" @if (!$suppliers->nextPageUrl()) aria-disabled="true" @endif>Next</a></div></div>
        @else
            <div class="empty-state"><strong>{{ $search ? 'No matching suppliers' : 'No suppliers yet' }}</strong>{{ $search ? 'Try a different search.' : 'Add your suppliers before creating a purchase order.' }}
                @unless ($supplierCount)<p style="margin: 16px 0 0"><a class="button button-primary" href="{{ route('suppliers.create') }}">Add your first supplier</a></p>@endunless
            </div>
        @endif
    </section>

    <dialog class="supplier-dialog" id="delete-supplier-dialog" aria-labelledby="delete-supplier-title" aria-describedby="delete-supplier-description">
        <div class="supplier-dialog-content"><h2 id="delete-supplier-title">Delete supplier?</h2><p id="delete-supplier-description">Remove <strong data-supplier-name>this supplier</strong> from your supplier list? Suppliers with purchase order history cannot be deleted.</p></div>
        <form class="supplier-dialog-actions" id="delete-supplier-form" method="POST" action="">
            @csrf
            @method('DELETE')
            <button class="button button-quiet" type="button" data-supplier-delete-cancel>Cancel</button>
            <button class="button button-danger" type="submit">Delete supplier</button>
        </form>
    </dialog>
@endsection

@push('scripts')
    <script>
        const supplierDeleteDialog = document.getElementById('delete-supplier-dialog');
        const supplierDeleteForm = document.getElementById('delete-supplier-form');
        const supplierName = supplierDeleteDialog.querySelector('[data-supplier-name]');
        let supplierDeleteTrigger = null;

        document.querySelectorAll('[data-supplier-delete-open]').forEach((button) => {
            button.addEventListener('click', () => {
                supplierDeleteTrigger = button;
                supplierName.textContent = button.dataset.supplierName;
                supplierDeleteForm.action = button.dataset.deleteAction;
                supplierDeleteDialog.showModal();
            });
        });

        supplierDeleteDialog.querySelector('[data-supplier-delete-cancel]').addEventListener('click', () => supplierDeleteDialog.close());
        supplierDeleteDialog.addEventListener('close', () => supplierDeleteTrigger?.focus());
    </script>
@endpush
