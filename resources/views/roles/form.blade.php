@extends('products.layout')

@php($editing = $role->exists)

@section('page_title', $editing ? 'Edit Role' : 'Create Role')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Team Management</p>
            <h1>{{ $editing ? 'Edit Role' : 'Create Role' }}</h1>
            <p>{{ $editing ? 'Update role designation and description.' : 'Define a new security role for team members.' }}</p>
        </div>
        <a class="button button-quiet" href="{{ route('roles.index') }}">Back to Roles</a>
    </div>

    <nav class="product-tabs" aria-label="User management sections">
        <a class="product-tab" href="{{ route('users.index') }}">Users</a>
        <a class="product-tab active" href="{{ route('roles.index') }}">Roles &amp; Permissions</a>
    </nav>

    <section class="panel form-panel">
        <form method="POST" action="{{ $editing ? route('roles.update', $role) : route('roles.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="form-grid">
                <div class="field full">
                    <label for="name">Role Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $role->name) }}" maxlength="255" required autofocus placeholder="e.g. Inventory Manager, Cashier, Auditor">
                    @error('name')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div class="field full">
                    <label for="description">Description (Optional)</label>
                    <textarea id="description" name="description" rows="3" class="text-area-field" placeholder="Describe the responsibilities and scope of this role...">{{ old('description', $role->description) }}</textarea>
                    @error('description')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="form-actions">
                <a class="button button-quiet" href="{{ route('roles.index') }}">Cancel</a>
                <button class="button button-primary" type="submit">{{ $editing ? 'Save Role Details' : 'Create Role' }}</button>
            </div>
        </form>
    </section>
@endsection

@push('styles')
    <style>
        .text-area-field { display: block; width: 100%; padding: 10px; border: 1px solid #dfe4da; border-radius: 4px; color: var(--ink); background: #fff; font-size: 11px; font-family: inherit; resize: vertical; }
        .text-area-field:focus { border-color: #70926e; outline: none; box-shadow: 0 0 0 3px rgba(112,146,110,.12); }
    </style>
@endpush
