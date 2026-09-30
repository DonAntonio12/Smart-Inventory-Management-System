@extends('products.layout')

@php($editing = $supplier->exists)

@section('page_title', $editing ? 'Edit supplier' : 'Add supplier')

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Procurement</p><h1>{{ $editing ? 'Edit supplier' : 'Add supplier' }}</h1><p>Keep supplier contact details current for purchasing and receiving.</p></div>
        <a class="button button-quiet" href="{{ route('suppliers.index') }}">Back to suppliers</a>
    </div>

    <nav class="product-tabs" aria-label="Supplier sections">
        <a class="product-tab active" href="{{ route('suppliers.index') }}">Suppliers</a>
        <a class="product-tab" href="{{ route('purchase-orders.index') }}">Purchase Orders</a>
    </nav>

    <section class="panel form-panel">
        <form method="POST" action="{{ $editing ? route('suppliers.update', $supplier) : route('suppliers.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif
            <div class="form-grid">
                <div class="field full">
                    <label for="name">Supplier name</label>
                    <input id="name" name="name" value="{{ old('name', $supplier->name) }}" maxlength="255" required autofocus placeholder="e.g. Northline Supply">
                    @error('name')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="contact_name">Contact person</label>
                    <input id="contact_name" name="contact_name" value="{{ old('contact_name', $supplier->contact_name) }}" maxlength="255" placeholder="e.g. Alex Rivera">
                    @error('contact_name')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $supplier->email) }}" maxlength="255" autocomplete="email" placeholder="orders@supplier.com">
                    @error('email')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="phone">Phone</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone', $supplier->phone) }}" maxlength="50" autocomplete="tel" placeholder="+63 ...">
                    @error('phone')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="address">Address</label>
                    <input id="address" name="address" value="{{ old('address', $supplier->address) }}" maxlength="2000" placeholder="Street, city, region">
                    @error('address')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field full">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" maxlength="2000" placeholder="Delivery schedule, payment terms, or other notes">{{ old('notes', $supplier->notes) }}</textarea>
                    @error('notes')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="form-actions"><a class="button button-quiet" href="{{ route('suppliers.index') }}">Cancel</a><button class="button button-primary" type="submit">{{ $editing ? 'Save supplier' : 'Add supplier' }}</button></div>
        </form>
    </section>
@endsection
