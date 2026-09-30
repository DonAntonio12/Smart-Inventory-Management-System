@extends('products.layout')

@section('page_title', 'Roles & Permissions')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Team Management</p>
            <h1>Roles &amp; Permissions</h1>
            <p>Define security roles, access levels, and view assigned team members.</p>
        </div>
        <a class="button button-primary" href="{{ route('roles.create') }}">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Create Role
        </a>
    </div>

    <nav class="product-tabs" aria-label="User management sections">
        <a class="product-tab" href="{{ route('users.index') }}">Users</a>
        <a class="product-tab active" href="{{ route('roles.index') }}">Roles &amp; Permissions ({{ $totalRoles }})</a>
    </nav>

    @if ($errors->has('role'))
        <div class="flash flash-error" role="alert">{{ $errors->first('role') }}</div>
    @endif

    <div class="stat-grid">
        <div class="stat">
            <span>Configured Roles</span>
            <strong>{{ number_format($totalRoles) }}</strong>
        </div>
        <div class="stat">
            <span>Users with Roles</span>
            <strong>{{ number_format($totalAssignedUsers) }}</strong>
        </div>
        <div class="stat">
            <span>Unassigned Users</span>
            <strong>{{ number_format($unassignedUsers) }}</strong>
        </div>
    </div>

    <div class="group-grid">
        @forelse ($roles as $role)
            <div class="role-card-item">
                <div class="role-header">
                    <div>
                        <div class="role-name-wrap">
                            <span class="role-title-text">{{ $role->name }}</span>
                            @if (in_array(strtolower($role->name), ['admin', 'administrator'], true))
                                <span class="system-badge">System Role</span>
                            @endif
                        </div>
                        <p class="role-description-text">{{ $role->description ?: 'No description provided.' }}</p>
                    </div>
                    <div class="role-count-badge">
                        <strong>{{ $role->users_count }}</strong>
                        <span>{{ Str::plural('user', $role->users_count) }}</span>
                    </div>
                </div>

                @if ($role->users->isNotEmpty())
                    <div class="assigned-users-list">
                        <span class="assigned-label">Assigned:</span>
                        <div class="user-avatars">
                            @foreach ($role->users as $u)
                                <span class="mini-avatar" title="{{ $u->name }} ({{ $u->email }})">{{ strtoupper(substr($u->name, 0, 1)) }}</span>
                            @endforeach
                            @if ($role->users_count > 5)
                                <span class="more-users">+{{ $role->users_count - 5 }}</span>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="role-card-actions">
                    <a class="button button-quiet" href="{{ route('roles.edit', $role) }}">Edit Role</a>
                    @if (!in_array(strtolower($role->name), ['admin', 'administrator'], true))
                        <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Are you sure you want to delete the role {{ addslashes($role->name) }}?');">
                            @csrf
                            @method('DELETE')
                            <button class="button button-danger" type="submit">Delete</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="panel full-width" style="grid-column: 1 / -1;">
                <div class="empty-state">
                    <strong>No roles defined yet</strong>
                    <p>Create your first system role to manage user permissions and access control.</p>
                    <a class="button button-primary" href="{{ route('roles.create') }}" style="margin-top: 12px;">Create First Role</a>
                </div>
            </div>
        @endforelse
    </div>

    @if ($roles->hasPages())
        <div class="table-footer" style="margin-top: 16px;">
            <span>Showing {{ $roles->firstItem() ?? 0 }} to {{ $roles->lastItem() ?? 0 }} of {{ $roles->total() }} roles</span>
            <div class="pagination">
                @if ($roles->onFirstPage())
                    <span class="button button-quiet" aria-disabled="true">Previous</span>
                @else
                    <a class="button button-quiet" href="{{ $roles->previousPageUrl() }}">Previous</a>
                @endif

                @if ($roles->hasMorePages())
                    <a class="button button-quiet" href="{{ $roles->nextPageUrl() }}">Next</a>
                @else
                    <span class="button button-quiet" aria-disabled="true">Next</span>
                @endif
            </div>
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .flash-error { border-color: #f5c6cb; color: #721c24; background: #f8d7da; }
        .role-card-item { display: flex; flex-direction: column; justify-content: space-between; padding: 18px; border: 1px solid #e4e7df; border-radius: 6px; background: var(--white); }
        .role-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
        .role-name-wrap { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .role-title-text { font: 700 15px/1.2 'Manrope', sans-serif; color: #29372e; }
        .system-badge { padding: 2px 6px; border-radius: 3px; color: #173a2b; background: #d3f36b; font-size: 8px; font-weight: 800; text-transform: uppercase; }
        .role-description-text { margin: 6px 0 0; color: #78847c; font-size: 10px; line-height: 1.4; }
        .role-count-badge { text-align: right; flex: 0 0 auto; }
        .role-count-badge strong { display: block; font: 700 18px 'Manrope', sans-serif; color: var(--green); }
        .role-count-badge span { display: block; color: #829087; font-size: 8px; }
        .assigned-users-list { display: flex; align-items: center; gap: 8px; margin: 8px 0 14px; padding-top: 10px; border-top: 1px dashed #eaede6; }
        .assigned-label { color: #829087; font-size: 9px; font-weight: 600; }
        .user-avatars { display: flex; align-items: center; gap: 4px; }
        .mini-avatar { display: grid; width: 22px; height: 22px; place-items: center; border-radius: 50%; color: #173a2b; background: #e3ebd6; font-size: 9px; font-weight: 800; }
        .more-users { color: #78847c; font-size: 9px; font-weight: 700; margin-left: 2px; }
        .role-card-actions { display: flex; align-items: center; gap: 6px; margin-top: auto; padding-top: 12px; border-top: 1px solid #edf0e9; }
        .role-card-actions .button { min-height: 31px; padding: 0 10px; font-size: 9px; }
    </style>
@endpush
