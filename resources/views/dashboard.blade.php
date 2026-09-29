<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#173a2b">
    <meta name="description" content="Inventory and sales overview for your Stockroom workspace.">
    <title>Dashboard | Stockroom</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
            --amber: #b87925;
            font-family: 'DM Sans', sans-serif;
            font-synthesis: none;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        * { box-sizing: border-box; }
        body { min-width: 320px; min-height: 100vh; margin: 0; color: var(--ink); background: var(--paper); }
        a { color: inherit; text-decoration: none; }
        button, input { font: inherit; }
        a:focus-visible, button:focus-visible { outline: 3px solid #a9ca56; outline-offset: 3px; }
        .app-shell { min-height: 100vh; }
        .sidebar { position: fixed; inset: 0 auto 0 0; z-index: 5; display: flex; width: 268px; flex-direction: column; overflow-y: auto; padding: 23px 14px 16px; color: #f6f8f1; background: var(--deep); scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.22) transparent; }
        .brand { display: inline-flex; align-items: center; gap: 10px; padding: 0 7px; font: 800 18px/1 'Manrope', sans-serif; }
        .brand-mark { display: grid; width: 34px; height: 34px; place-items: center; border-radius: 8px; color: var(--lime); background: rgba(255,255,255,.1); }
        .brand-mark svg { width: 20px; height: 20px; }
        .side-caption { margin: 28px 9px 9px; color: #93a99a; font-size: 9px; font-weight: 800; text-transform: uppercase; }
        .side-nav { display: grid; flex: 1; align-content: start; gap: 3px; overflow-y: auto; }
        .side-link { display: flex; min-height: 42px; align-items: center; gap: 11px; padding: 0 11px; border-radius: 5px; color: #c5d1c8; font-size: 12px; font-weight: 600; }
        .side-link svg { width: 17px; height: 17px; opacity: .8; }
        .side-link:not(.unavailable):hover { color: #fff; background: rgba(255,255,255,.07); }
        .side-link.active { color: var(--deep); background: var(--lime); }
        .side-link.active svg { opacity: 1; }
        .side-link.unavailable { color: #829688; cursor: not-allowed; }
        .side-group { min-width: 0; }
        .side-group-toggle { width: 100%; cursor: pointer; list-style: none; text-align: left; }
        .side-group-toggle::-webkit-details-marker { display: none; }
        .side-group-toggle .side-chevron { width: 13px; height: 13px; margin-left: auto; opacity: .55; transition: transform .18s ease; }
        .side-group[open] .side-chevron { transform: rotate(180deg); }
        .side-subnav { display: grid; gap: 2px; margin: 2px 0 6px 31px; padding-left: 10px; border-left: 1px solid rgba(220,235,221,.2); }
        .side-sub-link { display: flex; min-height: 31px; align-items: center; padding: 0 8px; border-radius: 4px; color: #b5c5b9; font-size: 10px; font-weight: 500; }
        .side-sub-link:not(.unavailable):hover { color: #fff; background: rgba(255,255,255,.07); }
        .side-sub-link.unavailable { color: #829688; cursor: default; }
        .side-bottom { margin-top: auto; }
        .workspace-tag { margin: 0 4px 14px; padding: 13px 11px; border: 1px solid rgba(255,255,255,.13); border-radius: 5px; background: rgba(255,255,255,.045); }
        .workspace-tag span { display: block; color: #9eb2a3; font-size: 9px; }
        .workspace-tag strong { display: block; margin-top: 4px; font-size: 11px; }
        .side-user { display: flex; align-items: center; gap: 10px; padding: 14px 6px 0; border-top: 1px solid rgba(255,255,255,.12); }
        .user-avatar { display: grid; width: 32px; height: 32px; flex: 0 0 auto; place-items: center; border-radius: 50%; color: var(--deep); background: var(--lime); font-size: 10px; font-weight: 800; }
        .user-copy { min-width: 0; flex: 1; }
        .user-copy strong, .user-copy span { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .user-copy strong { font-size: 10px; }
        .user-copy span { margin-top: 3px; color: #9eb2a3; font-size: 9px; }
        .logout-button { display: grid; width: 30px; height: 30px; place-items: center; border: 0; border-radius: 4px; color: #c5d1c8; background: transparent; cursor: pointer; }
        .logout-button:hover { color: #fff; background: rgba(255,255,255,.1); }
        .logout-button svg { width: 16px; height: 16px; }
        .main-area { min-height: 100vh; margin-left: 268px; }
        .topbar { display: flex; min-height: 72px; align-items: center; justify-content: flex-end; gap: 16px; padding: 0 36px; border-bottom: 1px solid #e3e6de; background: rgba(255,254,250,.74); }
        .topbar-date { display: flex; align-items: center; gap: 8px; color: #6f7c72; font-size: 11px; }
        .topbar-date svg { width: 15px; height: 15px; }
        .content { width: min(1440px, 100%); margin: 0 auto; padding: 29px 36px 42px; }
        .page-heading { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; margin-bottom: 24px; }
        .page-heading .kicker { margin: 0 0 7px; color: var(--green); font-size: 9px; font-weight: 800; text-transform: uppercase; }
        .page-heading h1 { margin: 0; font: 700 27px/1.2 'Manrope', sans-serif; }
        .page-heading p { margin: 7px 0 0; color: var(--muted); font-size: 12px; }
        .today-pill { display: inline-flex; flex: 0 0 auto; align-items: center; gap: 8px; padding: 9px 12px; border: 1px solid #e2e6dc; border-radius: 5px; color: #617066; background: #fffefa; font-size: 10px; font-weight: 600; }
        .today-pill svg { width: 14px; height: 14px; }
        .metric-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 13px; margin-bottom: 14px; }
        .metric { position: relative; min-height: 112px; overflow: hidden; padding: 16px 17px; border: 1px solid #e4e7df; border-radius: 6px; background: var(--white); }
        .metric::after { position: absolute; top: 0; right: 0; width: 3px; height: 100%; background: var(--metric-accent, #5b9168); content: ''; }
        .metric-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .metric-label { color: #748077; font-size: 10px; font-weight: 600; }
        .metric-icon { display: grid; width: 27px; height: 27px; place-items: center; border-radius: 5px; color: var(--metric-accent, #477b56); background: var(--metric-tint, #edf3e8); }
        .metric-icon svg { width: 15px; height: 15px; }
        .metric-value { display: block; margin-top: 10px; font: 700 23px/1 'Manrope', sans-serif; }
        .metric-note { display: block; margin-top: 6px; color: #929c94; font-size: 9px; }
        .metric.warning { --metric-accent: #b87925; --metric-tint: #faf1df; }
        .metric.critical { --metric-accent: #b94d41; --metric-tint: #f9e9e5; }
        .metric.blue { --metric-accent: #4e718c; --metric-tint: #eaf0f4; }
        .metric.lilac { --metric-accent: #716b52; --metric-tint: #f1efe5; }
        .content-grid { display: grid; grid-template-columns: minmax(0, 1.55fr) minmax(285px, .85fr); gap: 14px; margin-bottom: 14px; }
        .panel { min-width: 0; border: 1px solid #e4e7df; border-radius: 6px; background: var(--white); }
        .panel-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 18px 13px; border-bottom: 1px solid #edf0e9; }
        .panel-title { margin: 0; font: 700 13px 'Manrope', sans-serif; }
        .panel-subtitle { margin: 4px 0 0; color: #89948c; font-size: 9px; }
        .panel-meta { color: #879188; font-size: 9px; }
        .chart-wrap { padding: 13px 17px 14px; }
        .sales-chart { display: block; width: 100%; height: auto; overflow: visible; }
        .sales-chart line.grid { stroke: #edf0e9; stroke-width: 1; }
        .sales-chart polyline { fill: none; stroke: #3d8053; stroke-width: 3; stroke-linecap: round; stroke-linejoin: round; }
        .sales-chart circle { fill: var(--white); stroke: #3d8053; stroke-width: 2.5; }
        .chart-axis { display: flex; justify-content: space-between; padding: 0 3px; color: #929d94; font-size: 8px; }
        .chart-legend { display: flex; align-items: center; gap: 7px; color: #6f7b72; font-size: 9px; }
        .chart-legend::before { width: 7px; height: 7px; border-radius: 50%; background: #4d8a61; content: ''; }
        .notification-list { display: grid; }
        .notification { display: grid; grid-template-columns: 30px minmax(0, 1fr) auto; align-items: center; gap: 10px; padding: 12px 16px; border-bottom: 1px solid #eff1eb; }
        .notification:last-child { border-bottom: 0; }
        .notification-icon { display: grid; width: 29px; height: 29px; place-items: center; border-radius: 5px; color: #a64a3e; background: #faece8; }
        .notification.warning .notification-icon { color: #a17123; background: #faf3e4; }
        .notification.notice .notification-icon { color: #4e718c; background: #edf3f6; }
        .notification-icon svg { width: 15px; height: 15px; }
        .notification-copy { min-width: 0; }
        .notification-copy strong { display: block; font-size: 10px; }
        .notification-copy span { display: block; overflow: hidden; margin-top: 3px; color: #78847c; font-size: 9px; text-overflow: ellipsis; white-space: nowrap; }
        .notification-sku { color: #9aa39b; font-size: 8px; }
        .empty-state { padding: 28px 18px; color: #77837a; text-align: center; font-size: 10px; line-height: 1.6; }
        .empty-state strong { display: block; margin-bottom: 4px; color: #39473e; font-size: 11px; }
        .lower-grid { display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(0, .9fr); gap: 14px; margin-bottom: 14px; }
        .category-list { display: grid; gap: 14px; padding: 17px 18px; }
        .category-row { display: grid; grid-template-columns: minmax(90px, .8fr) minmax(90px, 1.5fr) 35px; align-items: center; gap: 12px; }
        .category-name { overflow: hidden; color: #526057; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; }
        .category-track { height: 7px; overflow: hidden; border-radius: 6px; background: #edf0e9; }
        .category-bar { height: 100%; min-width: 2px; border-radius: inherit; background: #5b9168; }
        .category-row:nth-child(2n) .category-bar { background: #b8cf73; }
        .category-row:nth-child(3n) .category-bar { background: #e29a76; }
        .category-count { color: #5b685f; text-align: right; font-size: 9px; font-weight: 700; }
        .expiry-list { display: grid; }
        .expiry-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 17px; border-bottom: 1px solid #eff1eb; }
        .expiry-row:last-child { border-bottom: 0; }
        .expiry-product { min-width: 0; }
        .expiry-product strong { display: block; overflow: hidden; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; }
        .expiry-product span { display: block; margin-top: 3px; color: #879188; font-size: 8px; }
        .expiry-date { flex: 0 0 auto; color: #ae663a; font-size: 9px; font-weight: 700; }
        .transaction-table-wrap { overflow-x: auto; }
        .transaction-table { width: 100%; border-collapse: collapse; text-align: left; white-space: nowrap; }
        .transaction-table th { padding: 10px 16px; color: #929c94; font-size: 8px; font-weight: 700; text-transform: uppercase; }
        .transaction-table td { padding: 12px 16px; border-top: 1px solid #eff1eb; color: #566259; font-size: 9px; }
        .transaction-primary { color: #354239 !important; font-weight: 700; }
        .transaction-type { display: inline-flex; align-items: center; gap: 6px; }
        .transaction-dot { width: 6px; height: 6px; border-radius: 50%; background: #56845e; }
        .transaction-dot.restock { background: #7091a5; }
        .transaction-dot.adjustment { background: #c88c4f; }
        .transaction-amount { color: #354239 !important; font-weight: 700; }
        .dashboard-footer { display: flex; justify-content: space-between; gap: 12px; padding: 17px 0 0; color: #879188; font-size: 9px; }
        @media (max-width: 1120px) {
            .sidebar { width: 230px; }
            .main-area { margin-left: 230px; }
            .topbar { padding: 0 25px; }
            .content { padding: 25px; }
            .content-grid { grid-template-columns: minmax(0, 1.3fr) minmax(260px, .9fr); }
        }
        @media (max-width: 850px) {
            .sidebar { position: static; width: auto; min-height: 0; padding: 13px 18px; }
            .side-caption, .side-bottom { display: none; }
            .sidebar { flex-direction: row; align-items: center; justify-content: space-between; }
            .brand { padding: 0; }
            .side-nav { display: flex; flex: 1; align-content: initial; align-items: center; justify-content: flex-end; gap: 4px; overflow: visible; flex-wrap: wrap; }
            .side-group { position: relative; }
            .side-subnav { position: absolute; z-index: 10; top: 100%; left: 0; min-width: 190px; margin: 2px 0 0; padding: 7px; border: 1px solid rgba(255,255,255,.12); border-radius: 5px; background: var(--deep); box-shadow: 0 12px 28px rgba(0,0,0,.22); }
            .side-link { min-height: 36px; padding: 0 9px; font-size: 10px; }
            .side-link svg { width: 15px; height: 15px; }
            .side-sub-link { min-height: 34px; }
            .main-area { margin-left: 0; min-height: calc(100vh - 60px); }
            .topbar { min-height: 54px; }
            .metric-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media (max-width: 650px) {
            .sidebar { align-items: stretch; flex-direction: column; gap: 12px; padding: 12px 14px 10px; overflow: visible; }
            .side-nav { display: grid; width: 100%; flex: initial; grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; justify-content: initial; gap: 3px; overflow: visible; }
            .side-group { min-width: 0; }
            .side-group-toggle { min-width: 0; }
            .side-group-toggle > span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .side-subnav { position: static; min-width: 0; margin: 2px 0 5px 12px; padding: 2px 0 2px 8px; border: 0; border-left: 1px solid rgba(220,235,221,.2); border-radius: 0; background: transparent; box-shadow: none; }
            .side-link { min-width: 0; flex: initial; padding: 0 8px; }
            .content { padding: 22px 14px 30px; }
            .topbar { justify-content: flex-start; padding: 0 15px; }
            .page-heading { align-items: flex-start; flex-direction: column; gap: 12px; margin-bottom: 18px; }
            .page-heading h1 { font-size: 23px; }
            .metric-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
            .metric { min-height: 99px; padding: 12px; }
            .metric-label { font-size: 9px; }
            .metric-value { font-size: 20px; }
            .content-grid, .lower-grid { grid-template-columns: 1fr; gap: 10px; }
            .content-grid { margin-bottom: 10px; }
            .notification { padding: 11px 13px; }
            .panel-header { padding: 14px 13px 11px; }
            .transaction-table th, .transaction-table td { padding-right: 12px; padding-left: 12px; }
            .dashboard-footer { flex-direction: column; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <div class="app-shell" id="top">
        <aside class="sidebar">
            <a class="brand" href="{{ route('dashboard') }}">
                <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M4 8.5 12 4l8 4.5v8L12 21l-8-4.5v-8Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m4.5 8.7 7.5 4.2 7.5-4.2M12 13v7.5" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></span>
                Stockroom
            </a>
            <p class="side-caption">Workspace</p>
            <nav class="side-nav" aria-label="Dashboard navigation">
                <a class="side-link active" href="#top" aria-current="page"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="1.5" stroke="currentColor" stroke-width="1.7"/><rect x="13" y="3" width="8" height="5" rx="1.5" stroke="currentColor" stroke-width="1.7"/><rect x="13" y="10" width="8" height="11" rx="1.5" stroke="currentColor" stroke-width="1.7"/><rect x="3" y="13" width="8" height="8" rx="1.5" stroke="currentColor" stroke-width="1.7"/></svg>Dashboard / Home / Overview</a>

                <details class="side-group">
                    <summary class="side-link side-group-toggle"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16l-1 13H5L4 7Zm4 0V5a4 4 0 0 1 8 0v2" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg><span>Products</span><svg class="side-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></summary>
                    <div class="side-subnav">
                        <span class="side-sub-link unavailable" aria-disabled="true">All Products</span>
                        <span class="side-sub-link unavailable" aria-disabled="true">Categories</span>
                        <span class="side-sub-link unavailable" aria-disabled="true">Brands</span>
                    </div>
                </details>

                <details class="side-group">
                    <summary class="side-link side-group-toggle"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m4 7 8-4 8 4v10l-8 4-8-4V7Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m4.5 7.3 7.5 4.2 7.5-4.2M12 12v8" stroke="currentColor" stroke-width="1.7"/></svg><span>Inventory</span><svg class="side-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></summary>
                    <div class="side-subnav">
                        <a class="side-sub-link" href="#inventory-summary">Stock Overview</a>
                        <span class="side-sub-link unavailable" aria-disabled="true">Stock In</span>
                        <span class="side-sub-link unavailable" aria-disabled="true">Stock Out</span>
                        <span class="side-sub-link unavailable" aria-disabled="true">Stock Adjustment</span>
                        <a class="side-sub-link" href="#notifications">Low Stock</a>
                        <a class="side-sub-link" href="#expiring-products">Expiring Products</a>
                    </div>
                </details>

                <details class="side-group">
                    <summary class="side-link side-group-toggle"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 8h18l-2 12H5L3 8Zm4 0 2-5h6l2 5" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 12h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg><span>Suppliers</span><svg class="side-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></summary>
                    <div class="side-subnav">
                        <span class="side-sub-link unavailable" aria-disabled="true">Suppliers</span>
                        <span class="side-sub-link unavailable" aria-disabled="true">Purchase Orders</span>
                    </div>
                </details>

                <details class="side-group">
                    <summary class="side-link side-group-toggle"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6h16l-1.5 15h-13L4 6Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 9V6a3 3 0 0 1 6 0v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg><span>Sales</span><svg class="side-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></summary>
                    <div class="side-subnav">
                        <span class="side-sub-link unavailable" aria-disabled="true">New Sale</span>
                        <a class="side-sub-link" href="#recent-transactions">Transactions</a>
                    </div>
                </details>

                <details class="side-group">
                    <summary class="side-link side-group-toggle"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.4-6.4-1.4 1.4M7 17l-1.4 1.4m12.8 0L17 17M7 7 5.6 5.6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.7"/></svg><span>Smart Analytics</span><svg class="side-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></summary>
                    <div class="side-subnav">
                        <span class="side-sub-link unavailable" aria-disabled="true">Demand Forecast</span>
                        <span class="side-sub-link unavailable" aria-disabled="true">Smart Reorder</span>
                        <a class="side-sub-link" href="#sales-chart">Sales Trends</a>
                    </div>
                </details>

                <span class="side-link unavailable" aria-disabled="true"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 19V5m0 14h17M8 15l4-4 3 2 5-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>Reports</span>
                <a class="side-link" href="#notifications"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9m-8 13h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>Notifications</a>
                <span class="side-link unavailable" aria-disabled="true"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="9" cy="8" r="3" stroke="currentColor" stroke-width="1.7"/><path d="M3 20v-2a6 6 0 0 1 12 0v2m2-9a3 3 0 1 0 0-6m1 9a5 5 0 0 1 3 4v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>Users &amp; Roles</span>
                <span class="side-link unavailable" aria-disabled="true"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/><path d="m19.4 15 .1.1a1.7 1.7 0 0 1-2.4 2.4l-.1-.1a1.7 1.7 0 0 0-2.9 1.2v.2a1.7 1.7 0 0 1-3.4 0v-.2a1.7 1.7 0 0 0-2.9-1.2l-.1.1a1.7 1.7 0 0 1-2.4-2.4l.1-.1a1.7 1.7 0 0 0-1.2-2.9H4a1.7 1.7 0 0 1 0-3.4h.2a1.7 1.7 0 0 0 1.2-2.9l-.1-.1a1.7 1.7 0 0 1 2.4-2.4l.1.1a1.7 1.7 0 0 0 2.9-1.2V2a1.7 1.7 0 0 1 3.4 0v.2a1.7 1.7 0 0 0 2.9 1.2l.1-.1a1.7 1.7 0 0 1 2.4 2.4l-.1.1a1.7 1.7 0 0 0 1.2 2.9h.2a1.7 1.7 0 0 1 0 3.4h-.2a1.7 1.7 0 0 0-1.2 2.9Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>Settings</span>
            </nav>
            <div class="side-bottom">
                <div class="workspace-tag"><span>Current workspace</span><strong>My inventory</strong></div>
                <div class="side-user">
                    <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="user-copy"><strong>{{ auth()->user()->name }}</strong><span>{{ auth()->user()->email }}</span></span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout-button" type="submit" aria-label="Log out" title="Log out"><svg viewBox="0 0 24 24" fill="none"><path d="M10 17l5-5-5-5m5 5H3m9-9h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="main-area">
            <header class="topbar">
                <span class="topbar-date"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M16 3v4M8 3v4M3 10h18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>{{ now()->format('l, F j, Y') }}</span>
            </header>

            <main class="content">
                <div class="page-heading">
                    <div><p class="kicker">Overview</p><h1>Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }}, {{ auth()->user()->name }}</h1><p>Here’s what’s happening across your inventory today.</p></div>
                    <span class="today-pill"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3v3m0 12v3m9-9h-3M6 12H3m15.4-6.4-2.1 2.1M7.7 16.3l-2.1 2.1m12.8 0-2.1-2.1M7.7 7.7 5.6 5.6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.7"/></svg>Live inventory overview</span>
                </div>

                <section class="metric-grid" aria-label="Inventory metrics">
                    <article class="metric">
                        <div class="metric-top"><span class="metric-label">Total products</span><span class="metric-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m4 7 8-4 8 4v10l-8 4-8-4V7Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m4.5 7.3 7.5 4.2 7.5-4.2M12 12v8" stroke="currentColor" stroke-width="1.7"/></svg></span></div>
                        <strong class="metric-value">{{ number_format($totalProducts) }}</strong><span class="metric-note">Items tracked in your catalog</span>
                    </article>
                    <article class="metric blue">
                        <div class="metric-top"><span class="metric-label">Inventory quantity</span><span class="metric-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 19V5m0 14h17M8 15l4-4 3 2 5-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span></div>
                        <strong class="metric-value">{{ number_format($totalQuantity) }}</strong><span class="metric-note">Units currently on hand</span>
                    </article>
                    <article class="metric warning">
                        <div class="metric-top"><span class="metric-label">Low stock</span><span class="metric-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3 2.8 19h18.4L12 3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M12 9v4m0 3h.01" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span></div>
                        <strong class="metric-value">{{ number_format($lowStockCount) }}</strong><span class="metric-note">Products at or below reorder level</span>
                    </article>
                    <article class="metric critical">
                        <div class="metric-top"><span class="metric-label">Out of stock</span><span class="metric-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="m9 9 6 6m0-6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span></div>
                        <strong class="metric-value">{{ number_format($outOfStockCount) }}</strong><span class="metric-note">Products that need replenishment</span>
                    </article>
                    <article class="metric lilac">
                        <div class="metric-top"><span class="metric-label">Expiring soon</span><span class="metric-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span></div>
                        <strong class="metric-value">{{ number_format($expiringCount) }}</strong><span class="metric-note">Expiring within the next 30 days</span>
                    </article>
                    <article class="metric">
                        <div class="metric-top"><span class="metric-label">Today's sales</span><span class="metric-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3v18m7-14H9.5a3.5 3.5 0 1 0 0 7h5a3.5 3.5 0 1 1 0 7H5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span></div>
                        <strong class="metric-value">₱{{ number_format((float) $todaySales, 2) }}</strong><span class="metric-note">Recorded sales for today</span>
                    </article>
                </section>

                <section class="content-grid">
                    <article class="panel" id="sales-chart">
                        <header class="panel-header"><div><h2 class="panel-title">Sales performance</h2><p class="panel-subtitle">Daily sales for the last 7 days</p></div><span class="chart-legend">Sales</span></header>
                        <div class="chart-wrap">
                            <svg class="sales-chart" viewBox="0 0 620 175" role="img" aria-label="Sales chart for the last seven days">
                                <line class="grid" x1="28" y1="20" x2="592" y2="20"/><line class="grid" x1="28" y1="64" x2="592" y2="64"/><line class="grid" x1="28" y1="108" x2="592" y2="108"/><line class="grid" x1="28" y1="152" x2="592" y2="152"/>
                                <polyline points="{{ $salesChartPoints }}"/>
                                @foreach ($salesChart as $day)
                                    <circle cx="{{ $day['x'] }}" cy="{{ $day['y'] }}" r="4"><title>{{ $day['label'] }}: ₱{{ number_format($day['amount'], 2) }}</title></circle>
                                @endforeach
                            </svg>
                            <div class="chart-axis">@foreach ($salesChart as $day)<span>{{ $day['label'] }}</span>@endforeach</div>
                        </div>
                    </article>

                    <article class="panel" id="notifications">
                        <header class="panel-header"><div><h2 class="panel-title">Notifications</h2><p class="panel-subtitle">Items that may need your attention</p></div><span class="panel-meta">{{ $notifications->count() }} alerts</span></header>
                        @if ($notifications->isNotEmpty())
                            <div class="notification-list">
                                @foreach ($notifications as $notification)
                                    <div class="notification {{ $notification['type'] }}">
                                        <span class="notification-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true">@if ($notification['type'] === 'critical')<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="m9 9 6 6m0-6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>@elseif ($notification['type'] === 'warning')<path d="M12 3 2.8 19h18.4L12 3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M12 9v4m0 3h.01" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>@else<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>@endif</svg></span>
                                        <span class="notification-copy"><strong>{{ $notification['title'] }}</strong><span>{{ $notification['message'] }}</span></span>
                                        <span class="notification-sku">{{ $notification['product'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state"><strong>All clear for now</strong>No low-stock, out-of-stock, or expiring items need attention.</div>
                        @endif
                    </article>
                </section>

                <section class="lower-grid">
                    <article class="panel" id="inventory-summary">
                        <header class="panel-header"><div><h2 class="panel-title">Inventory summary</h2><p class="panel-subtitle">Units on hand by category</p></div><span class="panel-meta">{{ number_format($totalQuantity) }} units</span></header>
                        @if ($inventorySummary->isNotEmpty())
                            <div class="category-list">
                                @foreach ($inventorySummary as $category)
                                    <div class="category-row"><span class="category-name">{{ $category->category }}</span><span class="category-track"><span class="category-bar" style="display: block; width: {{ max(2, (int) round($category->quantity / $inventoryMaximum * 100)) }}%"></span></span><span class="category-count">{{ number_format($category->quantity) }}</span></div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state"><strong>No inventory to summarize</strong>Product quantities will appear here once products are added.</div>
                        @endif
                    </article>

                    <article class="panel" id="expiring-products">
                        <header class="panel-header"><div><h2 class="panel-title">Expiring products</h2><p class="panel-subtitle">Within the next 30 days</p></div><span class="panel-meta">{{ $expiringCount }} items</span></header>
                        @if ($expiringProducts->isNotEmpty())
                            <div class="expiry-list">
                                @foreach ($expiringProducts as $product)
                                    <div class="expiry-row"><span class="expiry-product"><strong>{{ $product->name }}</strong><span>{{ $product->sku }} · {{ number_format($product->quantity) }} units</span></span><span class="expiry-date">{{ $product->expiration_date->format('M j') }}</span></div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state"><strong>No upcoming expirations</strong>Products expiring within 30 days will be listed here.</div>
                        @endif
                    </article>
                </section>

                <section class="panel" id="recent-transactions">
                    <header class="panel-header"><div><h2 class="panel-title">Recent transactions</h2><p class="panel-subtitle">Latest recorded inventory activity</p></div><span class="panel-meta">{{ $recentTransactions->count() }} recent</span></header>
                    @if ($recentTransactions->isNotEmpty())
                        <div class="transaction-table-wrap">
                            <table class="transaction-table">
                                <thead><tr><th>Reference</th><th>Activity</th><th>Product</th><th>Quantity</th><th>Amount</th><th>Date</th></tr></thead>
                                <tbody>
                                    @foreach ($recentTransactions as $transaction)
                                        <tr>
                                            <td class="transaction-primary">{{ $transaction->reference ?: 'TRX-'.$transaction->id }}</td>
                                            <td><span class="transaction-type"><i class="transaction-dot {{ $transaction->type }}"></i>{{ ucfirst($transaction->type) }}</span></td>
                                            <td>{{ $transaction->description ?: ($transaction->product?->name ?? 'Inventory activity') }}</td>
                                            <td>{{ $transaction->quantity > 0 ? '+' : '' }}{{ number_format($transaction->quantity) }}</td>
                                            <td class="transaction-amount">₱{{ number_format((float) $transaction->amount, 2) }}</td>
                                            <td>{{ $transaction->occurred_at->format('M j, g:i A') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state"><strong>No transactions yet</strong>Sales and inventory activity will appear here as transactions are recorded.</div>
                    @endif
                </section>

                <footer class="dashboard-footer"><span>Stockroom inventory overview</span><span>Updated {{ now()->format('g:i A') }}</span></footer>
            </main>
        </div>
    </div>
</body>
</html>
