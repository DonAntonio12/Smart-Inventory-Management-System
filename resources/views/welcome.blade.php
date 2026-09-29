<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f5ef">
    <meta name="description" content="A clearer view of your stock, orders, and inventory performance. Meet Stockroom, your smart inventory management system.">
    <title>Stockroom | Smart inventory, in sync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: light;
            --ink: #17231d;
            --muted: #69766d;
            --paper: #f4f5ef;
            --white: #fffefa;
            --green: #245b43;
            --green-dark: #173a2b;
            --lime: #d3f36b;
            --line: #e7e9e1;
            --coral: #e9785d;
            font-family: 'DM Sans', sans-serif;
            font-synthesis: none;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { margin: 0; background: var(--paper); color: var(--ink); }
        a { color: inherit; text-decoration: none; }
        button, a { -webkit-tap-highlight-color: transparent; }
        a:focus-visible { outline: 3px solid #8eae40; outline-offset: 4px; }

        .page-shell { width: min(1240px, calc(100% - 64px)); margin: 0 auto; }
        .topbar { height: 88px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(23, 35, 29, .09); }
        .brand { display: inline-flex; align-items: center; gap: 11px; font: 800 19px/1 'Manrope', sans-serif; letter-spacing: 0; }
        .brand-mark { display: grid; width: 36px; height: 36px; place-items: center; color: var(--lime); background: var(--green-dark); border-radius: 10px; }
        .brand-mark svg { width: 21px; height: 21px; }
        .nav-links { display: flex; align-items: center; gap: 37px; color: #526057; font-size: 13px; font-weight: 600; }
        .nav-links a:hover, .login-link:hover { color: var(--green); }
        .nav-actions { display: flex; align-items: center; gap: 23px; font-size: 13px; font-weight: 700; }
        .login-link { color: #526057; }
        .nav-actions form { margin: 0; }
        .logout-button { padding: 0; border: 0; color: #526057; background: transparent; font: inherit; cursor: pointer; }
        .logout-button:hover { color: var(--green); }
        .button { display: inline-flex; align-items: center; justify-content: center; gap: 10px; min-height: 46px; padding: 0 19px; border: 1px solid transparent; border-radius: 6px; font-size: 13px; font-weight: 700; transition: transform .2s ease, background .2s ease; }
        .button:hover { transform: translateY(-2px); }
        .button-dark { color: white; background: var(--green-dark); }
        .button-dark:hover { background: var(--green); }
        .button-lime { color: var(--green-dark); background: var(--lime); }
        .button-lime:hover { background: #defa86; }
        .button svg { width: 16px; height: 16px; }

        .hero { display: grid; grid-template-columns: minmax(0, .9fr) minmax(480px, 1.1fr); align-items: center; gap: 66px; padding: 76px 0 83px; }
        .hero-copy { padding: 8px 0 18px; animation: arrive .7s both; }
        .eyebrow { display: inline-flex; align-items: center; gap: 9px; margin-bottom: 23px; color: var(--green); font-size: 11px; font-weight: 800; letter-spacing: 0; text-transform: uppercase; }
        .eyebrow-dot { width: 8px; height: 8px; border-radius: 50%; background: #91b83b; box-shadow: 0 0 0 4px rgba(145, 184, 59, .15); }
        h1, h2, h3, p { margin-top: 0; }
        h1 { max-width: 570px; margin-bottom: 22px; font: 700 clamp(43px, 5vw, 66px)/1.06 'Manrope', sans-serif; letter-spacing: 0; }
        h1 span { color: var(--green); }
        .hero-description { max-width: 475px; margin-bottom: 30px; color: var(--muted); font-size: 16px; line-height: 1.75; }
        .hero-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 20px; }
        .text-link { display: inline-flex; align-items: center; gap: 8px; color: var(--green-dark); font-size: 13px; font-weight: 700; }
        .text-link svg { width: 16px; height: 16px; transition: transform .2s ease; }
        .text-link:hover svg { transform: translateX(3px); }
        .proof-line { display: flex; align-items: center; gap: 12px; margin-top: 38px; color: var(--muted); font-size: 12px; }
        .avatar-stack { display: flex; padding-left: 5px; }
        .avatar { display: grid; width: 27px; height: 27px; margin-left: -5px; place-items: center; border: 2px solid var(--paper); border-radius: 50%; color: #fff; background: #547a60; font-size: 8px; font-weight: 800; }
        .avatar:nth-child(2) { background: #dc9273; }
        .avatar:nth-child(3) { background: #687d9a; }
        .avatar:nth-child(4) { background: #bd9b4d; }
        .proof-line strong { color: var(--ink); }

        .dashboard-wrap { position: relative; min-width: 0; animation: arrive .8s .1s both; }
        .dashboard-wrap::before { position: absolute; z-index: -1; top: -27px; right: -22px; width: 164px; height: 164px; border: 1px solid rgba(36, 91, 67, .15); border-radius: 50%; content: ''; }
        .dashboard { position: relative; overflow: hidden; border: 1px solid #e5e8df; border-radius: 9px; background: var(--white); box-shadow: 0 22px 70px rgba(35, 55, 41, .12); }
        .dash-top { display: flex; min-height: 64px; align-items: center; justify-content: space-between; padding: 0 22px; border-bottom: 1px solid var(--line); }
        .dash-title { font: 700 13px 'Manrope', sans-serif; }
        .dash-tools { display: flex; align-items: center; gap: 13px; color: #66736a; }
        .dash-tool { display: grid; width: 29px; height: 29px; place-items: center; border: 1px solid var(--line); border-radius: 5px; }
        .dash-tool svg { width: 14px; height: 14px; }
        .dash-user { display: grid; width: 29px; height: 29px; place-items: center; border-radius: 50%; color: #fff; background: #d38b67; font-size: 9px; font-weight: 800; }
        .dash-body { padding: 21px 22px 20px; }
        .dash-heading { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 17px; }
        .dash-heading h2 { margin: 0 0 4px; font: 700 18px 'Manrope', sans-serif; }
        .dash-heading p { margin: 0; color: #879188; font-size: 10px; }
        .period { display: inline-flex; align-items: center; gap: 7px; padding: 8px 10px; border: 1px solid var(--line); border-radius: 4px; color: #59655d; font-size: 9px; font-weight: 700; }
        .period svg { width: 12px; height: 12px; }
        .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .stat { min-width: 0; padding: 13px 13px 11px; border: 1px solid var(--line); border-radius: 5px; }
        .stat-label { display: flex; align-items: center; gap: 7px; color: #7c887f; font-size: 9px; font-weight: 600; }
        .stat-icon { display: grid; width: 21px; height: 21px; place-items: center; border-radius: 4px; color: var(--green); background: #edf3e7; }
        .stat-icon.orange { color: #bd684d; background: #fff0e8; }
        .stat-icon.blue { color: #4e6c86; background: #eaf1f5; }
        .stat-icon svg { width: 12px; height: 12px; }
        .stat-value { display: block; margin-top: 11px; font: 700 20px 'Manrope', sans-serif; letter-spacing: 0; }
        .stat-change { display: block; margin-top: 4px; color: #4e9560; font-size: 8px; font-weight: 700; }
        .stat-change.neutral { color: #89938b; }
        .chart-row { display: grid; grid-template-columns: 1.18fr .82fr; gap: 11px; margin-top: 11px; }
        .panel { min-width: 0; padding: 14px; border: 1px solid var(--line); border-radius: 5px; }
        .panel-heading { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 12px; }
        .panel-heading strong { font: 700 10px 'Manrope', sans-serif; }
        .panel-heading span { color: #89938b; font-size: 8px; }
        .chart { position: relative; display: flex; height: 105px; align-items: flex-end; justify-content: space-between; gap: 8px; padding: 0 4px 16px; border-bottom: 1px solid #edf0e9; background: repeating-linear-gradient(to bottom, transparent 0, transparent 25px, #edf0e9 26px); }
        .bar-set { display: flex; height: 100%; flex: 1; align-items: flex-end; justify-content: center; gap: 3px; }
        .bar { width: min(12px, 35%); min-height: 8px; border-radius: 2px 2px 0 0; background: #d8e7c1; }
        .bar.primary { background: #4d8a61; }
        .chart-labels { position: absolute; right: 0; bottom: -15px; left: 0; display: flex; justify-content: space-between; color: #9aa39b; font-size: 7px; }
        .stock-list { display: grid; gap: 12px; }
        .stock-item { display: grid; grid-template-columns: 25px minmax(0, 1fr) auto; align-items: center; gap: 7px; }
        .product-thumb { display: grid; width: 25px; height: 25px; place-items: center; border-radius: 4px; color: #55745e; background: #edf1e8; }
        .product-thumb.orange { color: #ad7353; background: #f8ede2; }
        .product-thumb.blue { color: #5e7489; background: #eaf0f3; }
        .product-thumb svg { width: 13px; height: 13px; }
        .product-name { overflow: hidden; color: #46534a; font-size: 8px; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
        .product-meta { display: block; margin-top: 2px; color: #9aa39b; font-size: 7px; }
        .stock-count { color: #46534a; font-size: 8px; font-weight: 700; }
        .dash-foot { display: flex; align-items: center; justify-content: space-between; margin-top: 12px; padding-top: 11px; border-top: 1px solid var(--line); color: #7c887f; font-size: 8px; }
        .live-status { display: inline-flex; align-items: center; gap: 5px; color: #4e9560; font-weight: 700; }
        .live-status::before { width: 6px; height: 6px; border-radius: 50%; background: #76b882; content: ''; }

        .benefits { display: grid; grid-template-columns: 1.05fr repeat(3, 1fr); align-items: center; gap: 28px; padding: 25px 0 29px; border-top: 1px solid rgba(23, 35, 29, .1); }
        .benefits-intro { color: var(--muted); font-size: 11px; line-height: 1.6; }
        .benefits-intro strong { display: block; margin-bottom: 3px; color: var(--ink); font: 700 13px 'Manrope', sans-serif; }
        .benefit { display: flex; align-items: center; gap: 11px; }
        .benefit-icon { display: grid; width: 34px; height: 34px; flex: 0 0 auto; place-items: center; border: 1px solid #dfe5d9; border-radius: 6px; color: var(--green); background: #fbfcf8; }
        .benefit-icon svg { width: 16px; height: 16px; }
        .benefit strong { display: block; margin-bottom: 3px; font-size: 11px; }
        .benefit span { display: block; color: #7a867d; font-size: 10px; }
        .footer { display: flex; align-items: center; justify-content: space-between; padding: 19px 0 25px; border-top: 1px solid rgba(23, 35, 29, .08); color: #89938b; font-size: 10px; }
        .footer-note { display: flex; align-items: center; gap: 7px; }
        .footer-note svg { width: 13px; height: 13px; color: var(--green); }

        @keyframes arrive { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 980px) {
            .page-shell { width: min(100% - 44px, 760px); }
            .nav-links { gap: 20px; }
            .hero { grid-template-columns: 1fr; gap: 45px; padding: 63px 0 55px; }
            .hero-copy { max-width: 660px; }
            .dashboard-wrap { width: min(100%, 650px); margin: 0 auto; }
            .benefits { grid-template-columns: repeat(3, 1fr); gap: 22px 16px; }
            .benefits-intro { grid-column: 1 / -1; }
        }
        @media (max-width: 620px) {
            .page-shell { width: calc(100% - 36px); }
            .topbar { height: 72px; }
            .brand { font-size: 17px; }
            .nav-links { display: none; }
            .nav-actions { gap: 13px; font-size: 12px; }
            .nav-actions .button { min-height: 39px; padding: 0 12px; }
            .hero { gap: 36px; padding: 49px 0 42px; }
            h1 { max-width: 470px; font-size: 45px; }
            .hero-description { font-size: 14px; }
            .proof-line { margin-top: 27px; }
            .dashboard-wrap::before { top: -14px; right: -10px; width: 92px; height: 92px; }
            .dash-top { min-height: 54px; padding: 0 13px; }
            .dash-body { padding: 15px 12px 13px; }
            .dash-heading h2 { font-size: 15px; }
            .period { padding: 7px; font-size: 8px; }
            .stat-grid { gap: 6px; }
            .stat { padding: 9px 7px; }
            .stat-label { gap: 4px; font-size: 7px; }
            .stat-icon { width: 18px; height: 18px; }
            .stat-value { margin-top: 9px; font-size: 16px; }
            .stat-change { font-size: 7px; }
            .chart-row { gap: 6px; }
            .panel { padding: 10px 8px; }
            .panel-heading strong { font-size: 9px; }
            .chart { gap: 3px; }
            .stock-list { gap: 10px; }
            .stock-item { grid-template-columns: 21px minmax(0, 1fr) auto; gap: 5px; }
            .product-thumb { width: 21px; height: 21px; }
            .product-name { font-size: 7px; }
            .product-meta, .stock-count { font-size: 6px; }
            .benefits { grid-template-columns: 1fr; gap: 17px; padding: 22px 0; }
            .benefits-intro { grid-column: auto; }
            .benefit { gap: 10px; }
            .footer { align-items: flex-start; gap: 12px; font-size: 9px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <header class="page-shell topbar">
        <a class="brand" href="{{ url('/') }}" aria-label="Stockroom home">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M4 8.5 12 4l8 4.5v8L12 21l-8-4.5v-8Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m4.5 8.7 7.5 4.2 7.5-4.2M12 13v7.5M8 6.2l8 4.6" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
            </span>
            Stockroom
        </a>
        <nav class="nav-links" aria-label="Main navigation">
            <a href="#overview">Overview</a>
            <a href="#features">Features</a>
            <a href="#contact">Contact</a>
        </nav>
        <div class="nav-actions">
            @if (Route::has('login'))
                @auth
                    <a class="login-link" href="{{ route('dashboard') }}">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout-button" type="submit">Log out</button>
                    </form>
                @else
                    <a class="login-link" href="{{ route('login') }}">Log in</a>
                    @if (Route::has('register'))
                        <a class="button button-dark" href="{{ route('register') }}">Get started</a>
                    @endif
                @endauth
            @else
                <a class="button button-dark" href="#features">Explore system</a>
            @endif
        </div>
    </header>

    <main>
        <section class="page-shell hero" id="overview">
            <div class="hero-copy">
                <div class="eyebrow"><span class="eyebrow-dot"></span> Smart inventory, in sync</div>
                <h1>Know what’s in stock. <span>Before you need it.</span></h1>
                <p class="hero-description">Take control of your inventory from one clear, reliable workspace. Track stock, spot trends, and keep every order moving.</p>
                <div class="hero-actions">
                    @if (Route::has('register'))
                        <a class="button button-dark" href="{{ route('register') }}">Get started
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    @else
                        <a class="button button-dark" href="#features">Explore the system
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    @endif
                    <a class="text-link" href="#features">See what’s inside
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </div>
                <div class="proof-line">
                    <div class="avatar-stack" aria-hidden="true"><span class="avatar">JM</span><span class="avatar">AL</span><span class="avatar">KC</span><span class="avatar">RS</span></div>
                    <span>One clear view for <strong>every moving part.</strong></span>
                </div>
            </div>

            <div class="dashboard-wrap" aria-label="Preview of the inventory dashboard">
                <section class="dashboard">
                    <div class="dash-top">
                        <div class="brand" style="font-size: 13px; gap: 8px;">
                            <span class="brand-mark" style="width: 27px; height: 27px; border-radius: 7px;"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 8.5 12 4l8 4.5v8L12 21l-8-4.5v-8Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m4.5 8.7 7.5 4.2 7.5-4.2M12 13v7.5" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></span>
                            Stockroom
                        </div>
                        <div class="dash-tools">
                            <span class="dash-tool" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9m-8 13h4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                            <span class="dash-user" aria-hidden="true">JD</span>
                        </div>
                    </div>
                    <div class="dash-body">
                        <div class="dash-heading">
                            <div><h2>Good morning, Jamie</h2><p>Here’s what’s happening with your inventory.</p></div>
                            <span class="period"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M16 3v4M8 3v4M3 10h18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg> Last 30 days</span>
                        </div>
                        <div class="stat-grid">
                            <div class="stat">
                                <span class="stat-label"><span class="stat-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m4 7 8-4 8 4v10l-8 4-8-4V7Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m4.5 7.3 7.5 4.2 7.5-4.2M12 12v8" stroke="currentColor" stroke-width="1.7"/></svg></span> Total products</span>
                                <strong class="stat-value">1,284</strong><span class="stat-change">↑ 8.2% this month</span>
                            </div>
                            <div class="stat">
                                <span class="stat-label"><span class="stat-icon orange"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3 2.8 19h18.4L12 3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M12 9v4m0 3h.01" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span> Low stock</span>
                                <strong class="stat-value">12</strong><span class="stat-change neutral">Needs attention</span>
                            </div>
                            <div class="stat">
                                <span class="stat-label"><span class="stat-icon blue"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6h16l-1.5 15h-13L4 6Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 9V6a3 3 0 0 1 6 0v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span> Open orders</span>
                                <strong class="stat-value">36</strong><span class="stat-change">↑ 4.6% this month</span>
                            </div>
                        </div>
                        <div class="chart-row">
                            <div class="panel">
                                <div class="panel-heading"><strong>Stock movement</strong><span>Incoming &amp; outgoing</span></div>
                                <div class="chart" role="img" aria-label="Bar chart showing weekly stock movement">
                                    <div class="bar-set"><i class="bar" style="height: 39%"></i><i class="bar primary" style="height: 55%"></i></div>
                                    <div class="bar-set"><i class="bar" style="height: 58%"></i><i class="bar primary" style="height: 43%"></i></div>
                                    <div class="bar-set"><i class="bar" style="height: 48%"></i><i class="bar primary" style="height: 70%"></i></div>
                                    <div class="bar-set"><i class="bar" style="height: 72%"></i><i class="bar primary" style="height: 54%"></i></div>
                                    <div class="bar-set"><i class="bar" style="height: 53%"></i><i class="bar primary" style="height: 82%"></i></div>
                                    <div class="bar-set"><i class="bar" style="height: 85%"></i><i class="bar primary" style="height: 66%"></i></div>
                                    <div class="bar-set"><i class="bar" style="height: 65%"></i><i class="bar primary" style="height: 92%"></i></div>
                                    <div class="chart-labels"><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span></div>
                                </div>
                            </div>
                            <div class="panel">
                                <div class="panel-heading"><strong>Low stock alert</strong><span>View all →</span></div>
                                <div class="stock-list">
                                    <div class="stock-item"><span class="product-thumb"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 4h10l1 16H6L7 4Zm3 0V2h4v2" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg></span><span class="product-name">Ceramic mug<span class="product-meta">Home goods</span></span><span class="stock-count">3 left</span></div>
                                    <div class="stock-item"><span class="product-thumb orange"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 8h16v12H4V8Zm3 0V5h10v3M8 12h8" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg></span><span class="product-name">Canvas tote<span class="product-meta">Accessories</span></span><span class="stock-count">5 left</span></div>
                                    <div class="stock-item"><span class="product-thumb blue"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 8h14l-1 12H6L5 8Zm4 0V5a3 3 0 0 1 6 0v3" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg></span><span class="product-name">Desk organizer<span class="product-meta">Office</span></span><span class="stock-count">8 left</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="dash-foot"><span>Updated just now</span><span class="live-status">All systems operational</span></div>
                    </div>
                </section>
            </div>
        </section>

        <section class="page-shell benefits" id="features" aria-label="Inventory management features">
            <div class="benefits-intro"><strong>A better handle on every item.</strong>Less guesswork. More room to grow.</div>
            <div class="benefit"><span class="benefit-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 19V5m0 14h17M8 15l4-4 3 2 5-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span><div><strong>Live stock insights</strong><span>See inventory at a glance</span></div></div>
            <div class="benefit"><span class="benefit-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3v18m7-14H9.5a3.5 3.5 0 1 0 0 7h5a3.5 3.5 0 1 1 0 7H5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span><div><strong>Smarter decisions</strong><span>Plan with useful trends</span></div></div>
            <div class="benefit"><span class="benefit-icon"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span><div><strong>Fewer stock surprises</strong><span>Catch low items earlier</span></div></div>
        </section>
    </main>

    <footer class="page-shell footer" id="contact">
        <span>© {{ date('Y') }} Stockroom. Inventory, made clearer.</span>
        <span class="footer-note"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m12 3 1.9 5.8L20 11l-6.1 2.2L12 19l-1.9-5.8L4 11l6.1-2.2L12 3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg> Built for better business days</span>
    </footer>
</body>
</html>
