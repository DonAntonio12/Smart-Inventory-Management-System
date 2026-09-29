@extends('products.layout')

@php($editing = $product->exists)

@section('page_title', $editing ? 'Edit product' : 'Add product')

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Product catalog</p><h1>{{ $editing ? 'Edit product' : 'Add product' }}</h1><p>{{ $editing ? 'Update product details and inventory levels.' : 'Add an item to your inventory catalog.' }}</p></div>
        <a class="button button-quiet" href="{{ route('products.index') }}">Back to products</a>
    </div>

    <nav class="product-tabs" aria-label="Product sections">
        <a class="product-tab active" href="{{ route('products.index') }}">All Products</a>
        <a class="product-tab" href="{{ route('products.categories') }}">Categories</a>
        <a class="product-tab" href="{{ route('products.brands') }}">Brands</a>
    </nav>

    <section class="panel form-panel">
        <form method="POST" action="{{ $editing ? route('products.update', $product) : route('products.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif
            <div class="form-grid">
                <div class="field full">
                    <label for="name">Product name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $product->name) }}" placeholder="e.g. Insulated travel mug" maxlength="255" required autofocus>
                    @error('name')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="sku">SKU</label>
                    <input id="sku" name="sku" type="text" value="{{ old('sku', $product->sku) }}" placeholder="e.g. MUG-100" maxlength="255" required>
                    @error('sku')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="category">Category</label>
                    <input id="category" name="category" type="text" value="{{ old('category', $product->category) }}" placeholder="e.g. Drinkware" list="category-options" maxlength="120">
                    <datalist id="category-options">@foreach ($categories as $category)<option value="{{ $category }}">@endforeach</datalist>
                    @error('category')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="brand">Brand</label>
                    <input id="brand" name="brand" type="text" value="{{ old('brand', $product->brand) }}" placeholder="e.g. Northline" list="brand-options" maxlength="120">
                    <datalist id="brand-options">@foreach ($brands as $brand)<option value="{{ $brand }}">@endforeach</datalist>
                    @error('brand')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="quantity">Quantity on hand</label>
                    <input id="quantity" name="quantity" type="number" value="{{ old('quantity', $product->quantity ?? 0) }}" min="0" step="1" required>
                    @error('quantity')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="low_stock_threshold">Low-stock threshold</label>
                    <input id="low_stock_threshold" name="low_stock_threshold" type="number" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}" min="0" step="1" required>
                    @error('low_stock_threshold')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="expiration_date">Expiration date</label>
                    <input id="expiration_date" name="expiration_date" type="date" value="{{ old('expiration_date', $product->expiration_date?->format('Y-m-d')) }}">
                    @error('expiration_date')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="form-actions"><a class="button button-quiet" href="{{ route('products.index') }}">Cancel</a><button class="button button-primary" type="submit">{{ $editing ? 'Save changes' : 'Create product' }}</button></div>
        </form>
    </section>
@endsection
