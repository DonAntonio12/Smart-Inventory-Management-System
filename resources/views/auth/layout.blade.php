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
    <style>
        :root {
            color-scheme: light;
            --ink: #17231d;
            --muted: #718078;
            --paper: #f1f3ec;
            --green: #245b43;
            --green-dark: #173a2b;
            --lime: #d3f36b;
            --line: #e6e9e1;
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
        a:focus-visible, button:focus-visible, input:focus-visible { outline: 3px solid #a8cc4e; outline-offset: 3px; }
        .auth-page { width: min(1280px, calc(100% - 64px)); margin: 0 auto; }
        .auth-header { display: flex; height: 88px; align-items: center; justify-content: space-between; }
        .brand { display: inline-flex; align-items: center; gap: 11px; font: 800 19px/1 'Manrope', sans-serif; }
        .brand-mark { display: grid; width: 36px; height: 36px; place-items: center; border-radius: 9px; color: var(--lime); background: var(--green-dark); }
        .brand-mark svg { width: 21px; height: 21px; }
        .home-link { display: inline-flex; align-items: center; gap: 8px; color: #66746b; font-size: 12px; font-weight: 700; }
        .home-link:hover, .text-link:hover { color: var(--green); }
        .home-link svg { width: 15px; height: 15px; }
        .auth-shell { display: grid; min-height: min(690px, calc(100vh - 150px)); grid-template-columns: 1fr 1fr; overflow: hidden; border: 1px solid #e1e5dc; border-radius: 8px; background: #fffefa; box-shadow: 0 18px 55px rgba(34, 54, 40, .08); }
        .auth-story { position: relative; display: flex; min-height: 620px; flex-direction: column; justify-content: space-between; overflow: hidden; padding: 46px 48px 34px; color: #fff; background-color: var(--green-dark); background-image: linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px), linear-gradient(145deg, #204d38, #142f24 78%); background-size: 42px 42px, 42px 42px, auto; }
        .story-eyebrow { display: inline-flex; align-items: center; gap: 9px; color: #d3f36b; font-size: 10px; font-weight: 800; text-transform: uppercase; }
        .story-eyebrow span { width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
        .story-copy { max-width: 400px; margin: 49px 0 30px; }
        .story-copy h1 { margin: 0 0 15px; font: 700 clamp(32px, 3.3vw, 44px)/1.12 'Manrope', sans-serif; }
        .story-copy p { max-width: 355px; margin: 0; color: #c0d0c3; font-size: 14px; line-height: 1.75; }
        .inventory-preview { padding: 17px 18px 15px; border: 1px solid rgba(255,255,255,.15); border-radius: 6px; background: rgba(255,255,255,.07); backdrop-filter: blur(8px); }
        .preview-heading { display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; color: #dce8dc; font-size: 10px; font-weight: 700; }
        .preview-heading span:last-child { color: #a5c1a8; font-size: 9px; font-weight: 500; }
        .preview-bars { display: flex; height: 66px; align-items: flex-end; gap: 8px; padding: 0 2px 11px; border-bottom: 1px solid rgba(255,255,255,.17); }
        .preview-bars i { flex: 1; min-height: 9px; border-radius: 2px 2px 0 0; background: #d3f36b; opacity: .88; }
        .preview-bars i:nth-child(3n) { background: #e89772; }
        .preview-caption { display: flex; justify-content: space-between; margin-top: 10px; color: #a9bdae; font-size: 8px; }
        .story-foot { display: flex; align-items: center; gap: 9px; color: #b4c6b7; font-size: 10px; }
        .story-foot svg { width: 15px; height: 15px; color: #d3f36b; }
        .auth-panel { display: grid; align-items: center; padding: 52px clamp(34px, 6vw, 82px); }
        .auth-form { width: min(100%, 390px); margin: 0 auto; }
        .form-kicker { margin: 0 0 13px; color: var(--green); font-size: 10px; font-weight: 800; text-transform: uppercase; }
        .auth-form h2 { margin: 0 0 9px; font: 700 30px/1.2 'Manrope', sans-serif; }
        .form-intro { margin: 0 0 28px; color: var(--muted); font-size: 13px; line-height: 1.65; }
        .notice { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 20px; padding: 12px 13px; border: 1px solid #d7e8cb; border-radius: 5px; color: #315f3d; background: #f3f8ed; font-size: 12px; line-height: 1.5; }
        .notice svg { width: 16px; height: 16px; flex: 0 0 auto; }
        .field { margin-bottom: 17px; }
        .field label { display: block; margin-bottom: 7px; color: #35433a; font-size: 11px; font-weight: 700; }
        .field input { display: block; width: 100%; height: 46px; padding: 0 13px; border: 1px solid #dce1d8; border-radius: 4px; color: var(--ink); background: #fff; font-size: 13px; transition: border-color .15s, box-shadow .15s; }
        .field input::placeholder { color: #9aa49c; }
        .field input:focus { border-color: #648d68; outline: none; box-shadow: 0 0 0 3px rgba(100, 141, 104, .13); }
        .field-error { margin: 6px 0 0; color: #b04437; font-size: 11px; }
        .form-options { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin: 2px 0 21px; }
        .remember { display: inline-flex; align-items: center; gap: 8px; color: #59665d; font-size: 11px; }
        .remember input { width: 14px; height: 14px; margin: 0; accent-color: var(--green); }
        .text-link { color: var(--green); font-size: 11px; font-weight: 700; }
        .submit-button { display: inline-flex; width: 100%; min-height: 47px; align-items: center; justify-content: center; gap: 9px; border: 0; border-radius: 4px; color: #fff; background: var(--green-dark); font-size: 12px; font-weight: 700; cursor: pointer; transition: background .18s, transform .18s; }
        .submit-button:hover { transform: translateY(-1px); background: var(--green); }
        .submit-button svg { width: 16px; height: 16px; }
        .form-switch { margin: 22px 0 0; color: #718078; text-align: center; font-size: 11px; }
        .form-switch a { color: var(--green); font-weight: 700; }
        .auth-footer { display: flex; justify-content: space-between; padding: 17px 2px 22px; color: #8a958d; font-size: 9px; }
        @media (max-width: 900px) {
            .auth-page { width: min(100% - 40px, 620px); }
            .auth-header { height: 76px; }
            .auth-shell { display: block; min-height: 0; }
            .auth-story { display: none; }
            .auth-panel { min-height: 610px; padding: 55px 42px; }
        }
        @media (max-width: 480px) {
            .auth-page { width: calc(100% - 28px); }
            .auth-header { height: 68px; }
            .brand { gap: 8px; font-size: 17px; }
            .brand-mark { width: 32px; height: 32px; }
            .home-link { font-size: 10px; }
            .auth-panel { min-height: 0; padding: 39px 22px 42px; }
            .auth-form h2 { font-size: 27px; }
            .form-intro { margin-bottom: 24px; }
            .auth-footer { gap: 12px; font-size: 8px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <div class="auth-page">
        <header class="auth-header">
            <a class="brand" href="{{ route('home') }}" aria-label="Stockroom home">
                <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M4 8.5 12 4l8 4.5v8L12 21l-8-4.5v-8Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m4.5 8.7 7.5 4.2 7.5-4.2M12 13v7.5" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></span>
                Stockroom
            </a>
            <a class="home-link" href="{{ route('home') }}"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m14 18-6-6 6-6M8 12h12" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg> Back to home</a>
        </header>

        <main class="auth-shell">
            <section class="auth-story" aria-label="Stockroom inventory preview">
                <div class="story-eyebrow"><span></span> Inventory management workspace</div>
                <div>
                    <div class="story-copy">
                        <h1>Every item, in its place.</h1>
                        <p>A calmer way to keep stock visible, catch low items early, and keep your day moving.</p>
                    </div>
                    <div class="inventory-preview">
                        <div class="preview-heading"><span>Inventory movement</span><span>Last 7 days</span></div>
                        <div class="preview-bars" role="img" aria-label="Inventory movement over seven days">
                            <i style="height: 42%"></i><i style="height: 67%"></i><i style="height: 53%"></i><i style="height: 81%"></i><i style="height: 62%"></i><i style="height: 92%"></i><i style="height: 72%"></i><i style="height: 84%"></i><i style="height: 57%"></i><i style="height: 75%"></i><i style="height: 96%"></i><i style="height: 69%"></i>
                        </div>
                        <div class="preview-caption"><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span></div>
                    </div>
                </div>
                <div class="story-foot"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m12 3 1.9 5.8L20 11l-6.1 2.2L12 19l-1.9-5.8L4 11l6.1-2.2L12 3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg> A clear view of what matters.</div>
            </section>

            <section class="auth-panel">
                <div class="auth-form">
                    <p class="form-kicker">@yield('kicker', 'Secure workspace')</p>
                    <h2>@yield('heading')</h2>
                    <p class="form-intro">@yield('intro')</p>
                    @if (session('status'))
                        <div class="notice" role="status"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>{{ session('status') }}</div>
                    @endif
                    @yield('content')
                </div>
            </section>
        </main>

        <footer class="auth-footer"><span>© {{ date('Y') }} Stockroom</span><span>Inventory, made clearer.</span></footer>
    </div>
</body>
</html>
