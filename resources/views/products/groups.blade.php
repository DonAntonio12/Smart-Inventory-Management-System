@extends('products.layout')

@section('page_title', $title)

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Product catalog</p><h1>{{ $title }}</h1><p>{{ $description }}</p></div>
        <a class="button button-primary" href="{{ route('products.create') }}"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>Add product</a>
    </div>

    <nav class="product-tabs" aria-label="Product sections">
        <a class="product-tab" href="{{ route('products.index') }}">All Products</a>
        <a class="product-tab {{ $title === 'Categories' ? 'active' : '' }}" href="{{ route('products.categories') }}">Categories</a>
        <a class="product-tab {{ $title === 'Brands' ? 'active' : '' }}" href="{{ route('products.brands') }}">Brands</a>
    </nav>

    <section aria-label="{{ $title }}">
        @if ($groups->isNotEmpty())
            <div class="group-grid">
                @foreach ($groups as $group)
                    <a class="group-row" href="{{ route('products.index', [$filterKey => $group->filter_value]) }}">
                        <span><span class="group-name">{{ $group->name }}</span><span class="group-meta">{{ $group->product_count }} {{ $group->product_count === 1 ? 'product' : 'products' }}</span></span>
                        <span class="group-count"><strong>{{ number_format($group->unit_count) }}</strong><span>units</span></span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="panel empty-state"><strong>No {{ strtolower($title) }} yet</strong>These summaries will appear as products are added to your catalog.</div>
        @endif
    </section>
@endsection
