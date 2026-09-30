@extends('products.layout')

@section('page_title', 'Notifications')

@push('styles')
    <style>
        .notification-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-bottom: 16px; }
        .notification-stat { padding: 13px 15px; border: 1px solid #e4e7df; border-radius: 5px; background: #fffefa; }
        .notification-stat span { display: block; color: #78847c; font-size: 9px; }
        .notification-stat strong { display: block; margin-top: 6px; color: #29372e; font: 700 18px 'Manrope', sans-serif; }
        .notification-stat.critical strong { color: #a34839; }
        .notification-tabs { display: flex; flex-wrap: wrap; gap: 4px; margin: 0 0 14px; border-bottom: 1px solid #dde2d9; }
        .notification-tab { display: inline-flex; min-height: 37px; align-items: center; gap: 7px; padding: 0 12px; border-bottom: 2px solid transparent; color: #728077; font-size: 10px; font-weight: 600; }
        .notification-tab:hover { color: #245b43; }
        .notification-tab.active { border-color: #4d8058; color: #245b43; }
        .notification-tab-count { display: inline-grid; min-width: 17px; height: 17px; padding: 0 4px; place-items: center; border-radius: 10px; color: #637168; background: #edf0e9; font-size: 8px; }
        .alert-list { display: grid; }
        .alert-row { display: grid; grid-template-columns: 36px minmax(0, 1fr) auto; align-items: center; gap: 12px; padding: 13px 15px; border-bottom: 1px solid #eff1eb; }
        .alert-row:last-child { border-bottom: 0; }
        .alert-icon { display: grid; width: 34px; height: 34px; place-items: center; border-radius: 5px; color: #ad493d; background: #faece8; }
        .alert-row.warning .alert-icon { color: #9a681c; background: #faf1df; }
        .alert-row.notice .alert-icon { color: #4e718c; background: #eaf0f4; }
        .alert-icon svg { width: 17px; height: 17px; }
        .alert-copy { min-width: 0; }
        .alert-copy strong { display: flex; align-items: center; flex-wrap: wrap; gap: 7px; color: #2c3a31; font-size: 10px; }
        .alert-severity { display: inline-flex; padding: 3px 6px; border-radius: 10px; color: #a34839; background: #faece8; font-size: 7px; font-weight: 700; text-transform: uppercase; }
        .alert-row.warning .alert-severity { color: #90631e; background: #faf1df; }
        .alert-copy p { margin: 4px 0 0; color: #738077; font-size: 10px; line-height: 1.5; }
        .alert-context { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-top: 5px; color: #929c94; font-size: 8px; }
        .alert-action { flex: 0 0 auto; }
        .alert-action svg { width: 13px; height: 13px; }
        .notification-empty { padding: 44px 18px; color: #77837a; text-align: center; font-size: 10px; line-height: 1.6; }
        .notification-empty strong { display: block; margin-bottom: 5px; color: #344239; font: 700 14px 'Manrope', sans-serif; }
        @media (max-width: 600px) {
            .notification-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .alert-row { grid-template-columns: 30px minmax(0, 1fr); gap: 9px; padding: 12px; }
            .alert-icon { width: 29px; height: 29px; }
            .alert-action { grid-column: 2; justify-self: start; }
            .notification-tab { padding: 0 8px; font-size: 9px; }
        }
    </style>
@endpush

@section('content')
    <div class="page-heading">
        <div><p class="eyebrow">Workspace</p><h1>Notifications</h1><p>Active alerts from inventory, expiration dates, and purchase orders.</p></div>
    </div>

    <section class="notification-stats" aria-label="Alert summary">
        <article class="notification-stat"><span>Active alerts</span><strong>{{ number_format($counts['all']) }}</strong></article>
        <article class="notification-stat critical"><span>Critical</span><strong>{{ number_format($alerts->where('severity', 'critical')->count()) }}</strong></article>
        <article class="notification-stat"><span>Needs attention</span><strong>{{ number_format($alerts->where('severity', 'warning')->count()) }}</strong></article>
    </section>

    <nav class="notification-tabs" aria-label="Filter notifications">
        <a class="notification-tab {{ $filter === 'all' ? 'active' : '' }}" href="{{ route('notifications.index', ['type' => 'all']) }}">All <span class="notification-tab-count">{{ $counts['all'] }}</span></a>
        <a class="notification-tab {{ $filter === 'stock' ? 'active' : '' }}" href="{{ route('notifications.index', ['type' => 'stock']) }}">Stock <span class="notification-tab-count">{{ $counts['stock'] }}</span></a>
        <a class="notification-tab {{ $filter === 'expiry' ? 'active' : '' }}" href="{{ route('notifications.index', ['type' => 'expiry']) }}">Expiry <span class="notification-tab-count">{{ $counts['expiry'] }}</span></a>
        <a class="notification-tab {{ $filter === 'orders' ? 'active' : '' }}" href="{{ route('notifications.index', ['type' => 'orders']) }}">Orders <span class="notification-tab-count">{{ $counts['orders'] }}</span></a>
    </nav>

    <section class="panel" aria-label="Active notifications">
        @if ($alerts->isNotEmpty())
            <div class="alert-list">
                @foreach ($alerts as $alert)
                    <article class="alert-row {{ $alert['severity'] }}">
                        <span class="alert-icon" aria-hidden="true">
                            @if ($alert['type'] === 'stock')
                                <svg viewBox="0 0 24 24" fill="none"><path d="M12 3 2.8 19h18.4L12 3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M12 9v4m0 3h.01" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                            @elseif ($alert['type'] === 'expiry')
                                <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="none"><path d="M3 8h18l-2 12H5L3 8Zm4 0 2-5h6l2 5" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 12h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                            @endif
                        </span>
                        <span class="alert-copy">
                            <strong>{{ $alert['title'] }} <span class="alert-severity">{{ $alert['severity'] === 'critical' ? 'Critical' : 'Attention' }}</span></strong>
                            <p>{{ $alert['message'] }}</p>
                            <span class="alert-context"><span>{{ $alert['detail'] }}</span><span>{{ $alert['date']->format('M j, Y') }}</span></span>
                        </span>
                        <a class="button button-quiet alert-action" href="{{ $alert['action'] }}">{{ $alert['action_label'] }}<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                    </article>
                @endforeach
            </div>
        @else
            <div class="notification-empty"><strong>All clear</strong>{{ $filter === 'all' ? 'There are no active notifications right now.' : 'There are no active alerts in this category.' }}</div>
        @endif
    </section>
@endsection
