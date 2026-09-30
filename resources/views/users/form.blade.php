@extends('products.layout')

@php($editing = $user->exists)

@section('page_title', $editing ? 'Edit User' : 'Add User')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Team Management</p>
            <h1>{{ $editing ? 'Edit User' : 'Add User' }}</h1>
            <p>{{ $editing ? 'Update account details and role assignments.' : 'Create a new team account and assign roles.' }}</p>
        </div>
        <a class="button button-quiet" href="{{ route('users.index') }}">Back to Users</a>
    </div>

    <nav class="product-tabs" aria-label="User management sections">
        <a class="product-tab active" href="{{ route('users.index') }}">Users</a>
        <a class="product-tab" href="{{ route('roles.index') }}">Roles &amp; Permissions</a>
    </nav>

    <section class="panel form-panel">
        <form method="POST" action="{{ $editing ? route('users.update', $user) : route('users.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="form-grid">
                <div class="field full">
                    <label for="name">Full Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" maxlength="255" required autofocus placeholder="e.g. Alex Rivera">
                    @error('name')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="email">Email Address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" maxlength="255" required autocomplete="email" placeholder="alex@example.com">
                    @error('email')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="password">{{ $editing ? 'New Password (Optional)' : 'Password' }}</label>
                    <input id="password" name="password" type="password" value="{{ old('password') }}" minlength="8" placeholder="{{ $editing ? 'Leave blank to keep current password' : 'Minimum 8 characters' }}" {{ $editing ? '' : 'required' }}>
                    @error('password')<p class="field-error">{{ $message }}</p>@enderror
                </div>

                <div class="field full">
                    <label for="roles">Assign Roles</label>
                    <div class="role-grid" id="roles">
                        @forelse ($roles as $role)
                            <label class="role-card">
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked(in_array($role->id, old('roles', $user->roles->pluck('id')->toArray()), true))>
                                <div class="role-info">
                                    <span class="role-title">{{ $role->name }}</span>
                                    @if ($role->description)
                                        <span class="role-desc">{{ $role->description }}</span>
                                    @endif
                                </div>
                            </label>
                        @empty
                            <div class="empty-roles">
                                <span>No roles configured yet.</span>
                                <a href="{{ route('roles.create') }}" class="button button-quiet" style="margin-top: 6px;">Create Role</a>
                            </div>
                        @endforelse
                    </div>
                    @error('roles')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="form-actions">
                <a class="button button-quiet" href="{{ route('users.index') }}">Cancel</a>
                <button class="button button-primary" type="submit">{{ $editing ? 'Save Changes' : 'Create User Account' }}</button>
            </div>
        </form>
    </section>
@endsection

@push('styles')
    <style>
        .role-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; padding: 12px; border: 1px solid #dfe4da; border-radius: 5px; background: #fffefa; }
        .role-card { display: flex; align-items: flex-start; gap: 10px; padding: 10px; border: 1px solid #e5e8e0; border-radius: 4px; background: #fff; cursor: pointer; transition: border-color .15s ease, background .15s ease; }
        .role-card:hover { border-color: #70926e; background: #f8faf5; }
        .role-card input { width: 16px; height: 16px; margin-top: 2px; accent-color: var(--green); cursor: pointer; }
        .role-info { min-width: 0; flex: 1; }
        .role-title { display: block; color: #29372e; font-size: 11px; font-weight: 700; }
        .role-desc { display: block; margin-top: 3px; color: #78847c; font-size: 9px; line-height: 1.3; }
        .empty-roles { padding: 14px; text-align: center; color: #78847c; font-size: 10px; }
        @media (max-width: 560px) {
            .role-grid { grid-template-columns: 1fr; }
        }
    </style>
@endpush