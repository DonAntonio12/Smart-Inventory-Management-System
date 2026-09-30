<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#173a2b">
    <title>@yield('page_title') | Stockroom</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @stack('styles')
    <style>
        :root {
            color-scheme: light;
            --ink: #18231d;
            --muted: #78847c;
            --paper: #f3f4ee;
            --white: #fffefa;
            --green: #245b43;
            --deep: #173a2b;
            --lime: #d3f36b;
            --line: #e8eae3;
            --red: #b94d41;
            font-family: 'DM Sans', sans-serif;
            font-synthesis: none;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        * { box-sizing: border-box; }
        body { min-width: 320px; min-height: 100vh; margin: 0; color: var(--ink); background: var(--paper); }
        a { color: inherit; text-decoration: none; }
        button, input, select { font: inherit; }
        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible { outline: 3px solid #a9ca56; outline-offset: 3px; }
        .product-shell { min-height: 100vh; }
        .sidebar { position: fixed; inset: 0 auto 0 0; z-index: 5; display: flex; width: 250px; flex-direction: column; overflow-y: auto; padding: 23px 14px 16px; color: #f6f8f1; background: var(--deep); }
        .brand { display: inline-flex; align-items: center; gap: 10px; padding: 0 7px; font: 800 18px/1 'Manrope', sans-serif; }
        .brand-mark { display: grid; width: 34px; height: 34px; place-items: center; border-radius: 8px; color: var(--lime); background: rgba(255,255,255,.1); }
        .brand-mark svg { width: 20px; height: 20px; }
        .side-caption { margin: 30px 9px 9px; color: #93a99a; font-size: 9px; font-weight: 800; text-transform: uppercase; }
        .side-nav { display: grid; align-content: start; gap: 3px; }
        .side-link { display: flex; min-height: 39px; align-items: center; gap: 10px; padding: 0 10px; border-radius: 5px; color: #c5d1c8; font-size: 11px; font-weight: 600; }
        .side-link svg { width: 16px; height: 16px; flex: 0 0 auto; opacity: .8; }
        .side-link:hover { color: #fff; background: rgba(255,255,255,.07); }
        .side-link.active { color: var(--deep); background: var(--lime); }
        .side-group { min-width: 0; }
        .side-group-toggle { width: 100%; cursor: pointer; list-style: none; text-align: left; }
        .side-group-toggle::-webkit-details-marker { display: none; }
        .side-chevron { width: 13px !important; height: 13px !important; margin-left: auto; transition: transform .18s ease; }
        .side-group[open] .side-chevron { transform: rotate(180deg); }
        .side-subnav { display: grid; gap: 2px; margin: 2px 0 6px 29px; padding-left: 9px; border-left: 1px solid rgba(220,235,221,.2); }
        .side-sub-link { display: flex; min-height: 29px; align-items: center; padding: 0 8px; border-radius: 4px; color: #b5c5b9; font-size: 10px; }
        .side-sub-link:hover { color: #fff; background: rgba(255,255,255,.07); }
        .side-sub-link.active { color: #e0f6a1; background: rgba(255,255,255,.08); }
        .side-muted { color: #829688; cursor: default; }
        .side-bottom { margin-top: auto; padding-top: 20px; }
        .workspace-tag { margin: 0 4px 13px; padding: 11px; border: 1px solid rgba(255,255,255,.13); border-radius: 5px; background: rgba(255,255,255,.045); }
        .workspace-tag span { display: block; color: #9eb2a3; font-size: 9px; }
        .workspace-tag strong { display: block; margin-top: 4px; font-size: 10px; }
        .side-user { display: flex; align-items: center; gap: 9px; padding: 13px 5px 0; border-top: 1px solid rgba(255,255,255,.12); }
        .user-avatar { display: grid; width: 30px; height: 30px; flex: 0 0 auto; place-items: center; border-radius: 50%; color: var(--deep); background: var(--lime); font-size: 10px; font-weight: 800; }
        .user-copy { min-width: 0; flex: 1; }
        .user-copy strong, .user-copy span { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .user-copy strong { font-size: 10px; }
        .user-copy span { margin-top: 3px; color: #9eb2a3; font-size: 9px; }
        .logout-button { display: grid; width: 29px; height: 29px; place-items: center; border: 0; border-radius: 4px; color: #c5d1c8; background: transparent; cursor: pointer; }
        .logout-button:hover { color: white; background: rgba(255,255,255,.1); }
        .logout-button svg { width: 16px; height: 16px; }
        .main-area { min-height: 100vh; margin-left: 250px; }
        .topbar { display: flex; min-height: 68px; align-items: center; justify-content: flex-end; padding: 0 34px; border-bottom: 1px solid #e3e6de; background: rgba(255,254,250,.78); }
        .topbar-user { color: #637066; font-size: 11px; }
        .content { width: min(1440px, 100%); margin: 0 auto; padding: 30px 34px 46px; }
        .page-heading { display: flex; align-items: flex-end; justify-content: space-between; gap: 18px; margin-bottom: 22px; }
        .eyebrow { margin: 0 0 7px; color: var(--green); font-size: 9px; font-weight: 800; text-transform: uppercase; }
        h1, h2, p { margin-top: 0; }
        .page-heading h1 { margin: 0; font: 700 26px/1.2 'Manrope', sans-serif; }
        .page-heading p:last-child { margin: 7px 0 0; color: var(--muted); font-size: 12px; }
        .button { display: inline-flex; min-height: 39px; align-items: center; justify-content: center; gap: 8px; padding: 0 13px; border: 1px solid transparent; border-radius: 4px; font-size: 10px; font-weight: 700; cursor: pointer; transition: background .16s ease, transform .16s ease; }
        .button:hover { transform: translateY(-1px); }
        .button-primary { color: white; background: var(--deep); }
        .button-primary:hover { background: var(--green); }
        .button-quiet { border-color: #e0e4dc; color: #526057; background: #fffefa; }
        .button-quiet:hover { background: #f4f6f0; }
        .button-danger { color: #a94438; background: #faece8; }
        .button svg { width: 15px; height: 15px; }
        .product-tabs { display: flex; gap: 4px; margin: 0 0 18px; border-bottom: 1px solid #dde2d9; }
        .product-tab { display: inline-flex; min-height: 37px; align-items: center; padding: 0 13px; border-bottom: 2px solid transparent; color: #728077; font-size: 10px; font-weight: 600; }
        .product-tab:hover { color: var(--green); }
        .product-tab.active { border-color: #4d8058; color: var(--green); }
        .flash { margin-bottom: 16px; padding: 11px 13px; border: 1px solid #d9e8cd; border-radius: 4px; color: #35613e; background: #f3f8ed; font-size: 11px; }
        .stat-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 11px; margin-bottom: 17px; }
        .stat { padding: 13px 15px; border: 1px solid #e4e7df; border-radius: 5px; background: var(--white); }
        .stat span { display: block; color: #77837a; font-size: 9px; }
        .stat strong { display: block; margin-top: 7px; font: 700 20px 'Manrope', sans-serif; }
        .panel { border: 1px solid #e4e7df; border-radius: 5px; background: var(--white); }
        .toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; padding: 13px; border-bottom: 1px solid #edf0e9; }
        .toolbar input, .toolbar select { min-width: 140px; height: 36px; padding: 0 10px; border: 1px solid #e0e4dc; border-radius: 4px; color: var(--ink); background: #fff; font-size: 10px; }
        .toolbar input[type="search"] { flex: 1; min-width: 180px; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; white-space: nowrap; }
        th { padding: 10px 14px; color: #89948c; font-size: 8px; font-weight: 700; text-transform: uppercase; }
        td { padding: 12px 14px; border-top: 1px solid #eff1eb; color: #59655d; font-size: 10px; }
        .product-name { display: block; color: #29372e; font-size: 10px; font-weight: 700; }
        .product-sku { display: block; margin-top: 3px; color: #929c94; font-size: 8px; }
        .stock-pill { display: inline-flex; align-items: center; padding: 4px 7px; border-radius: 20px; color: #3f784d; background: #eff6e9; font-size: 8px; font-weight: 700; }
        .stock-pill.low { color: #9a681c; background: #faf1df; }
        .stock-pill.out { color: #aa493e; background: #f9eae6; }
        .row-actions { display: flex; align-items: center; gap: 6px; }
        .row-actions .button { min-height: 29px; padding: 0 8px; font-size: 9px; }
        .row-actions form { margin: 0; }
        .table-footer { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 14px; border-top: 1px solid #edf0e9; color: #7c887f; font-size: 9px; }
        .pagination { display: flex; gap: 6px; }
        .pagination .button[aria-disabled="true"] { opacity: .48; pointer-events: none; }
        .empty-state { padding: 43px 18px; color: #77837a; text-align: center; font-size: 11px; line-height: 1.6; }
        .empty-state strong { display: block; margin-bottom: 5px; color: #344239; font: 700 14px 'Manrope', sans-serif; }
        .group-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 11px; }
        .group-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 12px; align-items: center; padding: 15px 16px; border: 1px solid #e4e7df; border-radius: 5px; background: var(--white); }
        .group-name { display: block; overflow: hidden; color: #29372e; font-size: 11px; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
        .group-meta { display: block; margin-top: 5px; color: #859087; font-size: 9px; }
        .group-count { min-width: 70px; text-align: right; }
        .group-count strong { display: block; font: 700 15px 'Manrope', sans-serif; }
        .group-count span { display: block; margin-top: 3px; color: #829087; font-size: 8px; }
        .form-panel { width: min(760px, 100%); padding: 20px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .field { min-width: 0; }
        .field.full { grid-column: 1 / -1; }
        .field label { display: block; margin-bottom: 7px; color: #435047; font-size: 10px; font-weight: 700; }
        .field input { display: block; width: 100%; height: 39px; padding: 0 10px; border: 1px solid #dfe4da; border-radius: 4px; color: var(--ink); background: #fff; font-size: 11px; }
        .field input:focus { border-color: #70926e; outline: none; box-shadow: 0 0 0 3px rgba(112,146,110,.12); }
        .field-error { margin: 5px 0 0; color: #af4b3e; font-size: 9px; }
        .form-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 22px; padding-top: 15px; border-top: 1px solid #edf0e9; }
        @media (max-width: 1080px) {
            .sidebar { width: 225px; }
            .main-area { margin-left: 225px; }
            .content { padding: 25px 23px 38px; }
        }
        @media (max-width: 850px) {
            .sidebar { position: static; width: 100%; max-height: none; overflow: visible; padding: 13px 17px; }
            .sidebar > .side-caption, .side-bottom { display: none; }
            .brand { padding: 0; }
            .product-shell { display: flex; flex-direction: column; }
            .side-nav { display: flex; flex-wrap: wrap; align-items: center; gap: 4px; margin-top: 12px; }
            .side-group { position: relative; }
            .side-link { min-height: 34px; padding: 0 8px; font-size: 10px; }
            .side-subnav { position: absolute; z-index: 10; top: 100%; left: 0; min-width: 170px; margin: 2px 0 0; padding: 7px; border: 1px solid rgba(255,255,255,.12); border-radius: 5px; background: var(--deep); box-shadow: 0 12px 28px rgba(0,0,0,.22); }
            .main-area { margin-left: 0; }
            .topbar { min-height: 52px; }
        }
        @media (max-width: 560px) {
            .sidebar { padding: 12px; }
            .side-nav { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 3px; }
            .side-group { min-width: 0; }
            .side-subnav { position: static; min-width: 0; margin: 2px 0 5px 10px; padding: 2px 0 2px 8px; border: 0; border-left: 1px solid rgba(220,235,221,.2); border-radius: 0; background: transparent; box-shadow: none; }
            .content { padding: 20px 12px 30px; }
            .topbar { justify-content: flex-start; padding: 0 13px; }
            .page-heading { align-items: flex-start; flex-direction: column; }
            .page-heading h1 { font-size: 23px; }
            .stat-grid { gap: 7px; }
            .stat { padding: 11px; }
            .stat strong { font-size: 17px; }
            .toolbar { align-items: stretch; }
            .toolbar input, .toolbar select, .toolbar input[type="search"] { min-width: 100%; }
            .toolbar .button { align-self: flex-start; }
            .group-grid { grid-template-columns: 1fr; }
            .form-panel { padding: 15px; }
            .form-grid { grid-template-columns: 1fr; }
            .field.full { grid-column: auto; }
            .table-footer { align-items: flex-start; flex-direction: column; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
<div class="product-shell">
    <aside class="sidebar">
        <a class="brand" href="{{ route('dashboard') }}">
            <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M4 8.5 12 4l8 4.5v8L12 21l-8-4.5v-8Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m4.5 8.7 7.5 4.2 7.5-4.2M12 13v7.5" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></span>
            Stockroom
        </a>
        <p class="side-caption">Workspace</p>
        <nav class="side-nav" aria-label="Dashboard navigation">
            <a class="side-link" href="{{ route('dashboard') }}"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="1.5" stroke="currentColor" stroke-width="1.7"/><rect x="13" y="3" width="8" height="5" rx="1.5" stroke="currentColor" stroke-width="1.7"/><rect x="13" y="10" width="8" height="11" rx="1.5" stroke="currentColor" stroke-width="1.7"/><rect x="3" y="13" width="8" height="8" rx="1.5" stroke="currentColor" stroke-width="1.7"/></svg>Overview</a>
            <details class="side-group" @if (request()->routeIs('products.*')) open @endif>
                <summary class="side-link side-group-toggle {{ request()->routeIs('products.*') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16l-1 13H5L4 7Zm4 0V5a4 4 0 0 1 8 0v2" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg><span>Products</span><svg class="side-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></summary>
                <div class="side-subnav">
                    <a class="side-sub-link {{ request()->routeIs('products.index', 'products.create', 'products.edit') ? 'active' : '' }}" href="{{ route('products.index') }}">All Products</a>
                    <a class="side-sub-link {{ request()->routeIs('products.categories') ? 'active' : '' }}" href="{{ route('products.categories') }}">Categories</a>
                    <a class="side-sub-link {{ request()->routeIs('products.brands') ? 'active' : '' }}" href="{{ route('products.brands') }}">Brands</a>
                </div>
            </details>
            <details class="side-group" @if (request()->routeIs('inventory.*')) open @endif>
                <summary class="side-link side-group-toggle {{ request()->routeIs('inventory.*') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m4 7 8-4 8 4v10l-8 4-8-4V7Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m4.5 7.3 7.5 4.2 7.5-4.2M12 12v8" stroke="currentColor" stroke-width="1.7"/></svg><span>Inventory</span><svg class="side-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></summary>
                <div class="side-subnav">
                    <a class="side-sub-link {{ request()->routeIs('inventory.index') ? 'active' : '' }}" href="{{ route('inventory.index') }}">Stock Overview</a>
                    <a class="side-sub-link {{ request()->routeIs('inventory.stock-in*') ? 'active' : '' }}" href="{{ route('inventory.stock-in') }}">Stock In</a>
                    <a class="side-sub-link {{ request()->routeIs('inventory.stock-out*') ? 'active' : '' }}" href="{{ route('inventory.stock-out') }}">Stock Out</a>
                    <a class="side-sub-link {{ request()->routeIs('inventory.adjustment*') ? 'active' : '' }}" href="{{ route('inventory.adjustment') }}">Stock Adjustment</a>
                    <a class="side-sub-link {{ request()->routeIs('inventory.low-stock') ? 'active' : '' }}" href="{{ route('inventory.low-stock') }}">Low Stock</a>
                    <a class="side-sub-link {{ request()->routeIs('inventory.expiring-products') ? 'active' : '' }}" href="{{ route('inventory.expiring-products') }}">Expiring Products</a>
                </div>
            </details>
            <details class="side-group" @if (request()->routeIs('suppliers.*', 'purchase-orders.*')) open @endif>
                <summary class="side-link side-group-toggle {{ request()->routeIs('suppliers.*', 'purchase-orders.*') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 8h18l-2 12H5L3 8Zm4 0 2-5h6l2 5" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 12h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg><span>Suppliers</span><svg class="side-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></summary>
                <div class="side-subnav">
                    <a class="side-sub-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">Suppliers</a>
                    <a class="side-sub-link {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}" href="{{ route('purchase-orders.index') }}">Purchase Orders</a>
                </div>
            </details>
            <details class="side-group" @if (request()->routeIs('sales.*')) open @endif>
                <summary class="side-link side-group-toggle {{ request()->routeIs('sales.*') ? 'active' : '' }}"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6h16l-1.5 15h-13L4 6Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 9V6a3 3 0 0 1 6 0v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg><span>Sales</span><svg class="side-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></summary>
                <div class="side-subnav">
                    <a class="side-sub-link {{ request()->routeIs('sales.create', 'sales.store') ? 'active' : '' }}" href="{{ route('sales.create') }}">New Sale</a>
                    <a class="side-sub-link {{ request()->routeIs('sales.transactions', 'sales.show') ? 'active' : '' }}" href="{{ route('sales.transactions') }}">Transactions</a>
                </div>
            </details>
            <a class="side-link" href="{{ route('dashboard') }}#sales-chart"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 19V5m0 14h17M8 15l4-4 3 2 5-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>Smart Analytics</a>
            <a class="side-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 19V5m0 14h17M8 15l4-4 3 2 5-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>Reports</a>
            <a class="side-link" href="{{ route('dashboard') }}#notifications"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9m-8 13h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>Notifications</a>
            <span class="side-link side-muted"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="9" cy="8" r="3" stroke="currentColor" stroke-width="1.7"/><path d="M3 20v-2a6 6 0 0 1 12 0v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>Users &amp; Roles</span>
            <span class="side-link side-muted"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/><path d="M19 15a1.5 1.5 0 0 0 1.5 1.5L19 20l-2-1a7 7 0 0 1-2 1l-.5 2h-4L10 20a7 7 0 0 1-2-1l-2 1-1.5-3.5A1.5 1.5 0 0 0 6 15v-2a1.5 1.5 0 0 0-1.5-1.5L6 8l2 1a7 7 0 0 1 2-1l.5-2h4l.5 2a7 7 0 0 1 2 1l2-1 1.5 3.5A1.5 1.5 0 0 0 19 13v2Z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>Settings</span>
        </nav>
        <div class="side-bottom">
            <div class="workspace-tag"><span>Current workspace</span><strong>My inventory</strong></div>
            <div class="side-user">
                <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                <span class="user-copy"><strong>{{ auth()->user()->name }}</strong><span>{{ auth()->user()->email }}</span></span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-button" type="submit" title="Log out" aria-label="Log out"><svg viewBox="0 0 24 24" fill="none"><path d="M10 17l5-5-5-5m5 5H3m9-9h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                </form>
            </div>
        </div>
    </aside>

    <div class="main-area">
        <header class="topbar"><span class="topbar-user">{{ auth()->user()->name }} · {{ now()->format('M j, Y') }}</span></header>
        <main class="content">
            @if (session('status'))<div class="flash" role="status">{{ session('status') }}</div>@endif
            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
