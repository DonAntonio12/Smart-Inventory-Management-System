@extends('products.layout')

@section('page_title', 'Users & Roles')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Team Management</p>
            <h1>Users &amp; Accounts</h1>
            <p>Manage team member accounts, authentication details, and role assignments.</p>
        </div>
        <a class="button button-primary" href="{{ route('users.create') }}">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14m-7-7h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Add User
        </a>
    </div>

    <nav class="product-tabs" aria-label="User management sections">
        <a class="product-tab active" href="{{ route('users.index') }}">Users ({{ $totalUsers }})</a>
        <a class="product-tab" href="{{ route('roles.index') }}">Roles &amp; Permissions ({{ $totalRoles }})</a>
    </nav>

    @if ($errors->has('user'))
        <div class="flash flash-error" role="alert">{{ $errors->first('user') }}</div>
    @endif

    <div class="stat-grid">
        <div class="stat">
            <span>Total Accounts</span>
            <strong>{{ number_format($totalUsers) }}</strong>
        </div>
        <div class="stat">
            <span>Assigned Roles</span>
            <strong>{{ number_format($assignedUsers) }}</strong>
        </div>
        <div class="stat">
            <span>Configured Roles</span>
            <strong>{{ number_format($totalRoles) }}</strong>
        </div>
    </div>

    <section class="panel">
        <form class="toolbar" method="GET" action="{{ route('users.index') }}">
            <input type="search" name="search" value="{{ $search }}" placeholder="Search by name or email..." aria-label="Search users">
            <select name="role" aria-label="Filter by role" onchange="this.form.submit()">
                <option value="">All Roles</option>
                <option value="__unassigned" @selected($selectedRole === '__unassigned')>Unassigned Users</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @selected((string)$selectedRole === (string)$role->id)>{{ $role->name }}</option>
                @endforeach
            </select>
            @if ($search !== '' || filled($selectedRole))
                <a class="button button-quiet" href="{{ route('users.index') }}">Clear Filters</a>
            @endif
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email Address</th>
                        <th>Assigned Roles</th>
                        <th>Joined Date</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <span class="avatar-badge">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    <div>
                                        <span class="product-name">{{ $user->name }}</span>
                                        @if ($user->id === auth()->id())
                                            <span class="you-badge">You</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <div class="role-badges">
                                    @forelse ($user->roles as $role)
                                        <span class="role-pill">{{ $role->name }}</span>
                                    @empty
                                        <span class="role-pill unassigned">No Role</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>{{ $user->created_at?->format('M j, Y') ?? '—' }}</td>
                            <td>
                                <div class="row-actions" style="justify-content: flex-end;">
                                    <a class="button button-quiet" href="{{ route('users.edit', $user) }}">Edit</a>
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Are you sure you want to remove {{ addslashes($user->name) }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="button button-danger" type="submit">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <strong>No user accounts found</strong>
                                    <p>Try adjusting your search criteria or add a new team member.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="table-footer">
                <span>Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users</span>
                <div class="pagination">
                    @if ($users->onFirstPage())
                        <span class="button button-quiet" aria-disabled="true">Previous</span>
                    @else
                        <a class="button button-quiet" href="{{ $users->previousPageUrl() }}">Previous</a>
                    @endif

                    @if ($users->hasMorePages())
                        <a class="button button-quiet" href="{{ $users->nextPageUrl() }}">Next</a>
                    @else
                        <span class="button button-quiet" aria-disabled="true">Next</span>
                    @endif
                </div>
            </div>
        @endif
    </section>
@endsection

@push('styles')
    <style>
        .flash-error { border-color: #f5c6cb; color: #721c24; background: #f8d7da; }
        .user-cell { display: flex; align-items: center; gap: 10px; }
        .avatar-badge { display: grid; width: 32px; height: 32px; flex: 0 0 auto; place-items: center; border-radius: 50%; color: #173a2b; background: #d3f36b; font-size: 11px; font-weight: 800; }
        .you-badge { display: inline-block; margin-top: 2px; padding: 1px 5px; border-radius: 3px; color: #245b43; background: #e2f2e6; font-size: 8px; font-weight: 700; text-transform: uppercase; }
        .role-badges { display: flex; flex-wrap: wrap; gap: 4px; }
        .role-pill { display: inline-flex; align-items: center; padding: 3px 8px; border-radius: 12px; color: #173a2b; background: #e3ebd6; font-size: 9px; font-weight: 700; }
        .role-pill.unassigned { color: #78847c; background: #eaede6; }
    </style>
@endpush
