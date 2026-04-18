<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $pageTitle = $__env->yieldContent('title', config('app.name'));
        $seoTitle  = $__env->yieldContent('seo_title') ?: ($pageTitle . ' | ' . config('app.name'));
        $metaDesc  = $__env->yieldContent('meta_description', 'Turn your old games into cash. Browse thousands of titles across every platform, check what they\'re worth, and get a free collection quote today.');
        $ogImage   = $__env->yieldContent('og_image', asset('img/og-default.jpg'));
        $ogType    = $__env->yieldContent('og_type', 'website');
        $canonical = $__env->yieldContent('canonical', url()->current());
    @endphp
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $metaDesc }}">
    <link rel="canonical" href="{{ $canonical }}">

    {{-- Open Graph --}}
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $metaDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    @stack('head_meta')

    {{-- Favicon --}}
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="/favicon.ico" sizes="32x32">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <meta name="theme-color" content="#080810">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
</head>
<body>

<header class="site-header" id="site-header">
    <div class="header-inner">
        <a href="{{ route('home') }}" class="logo">
            <span class="logo-icon">◈</span>
            <span class="logo-text">{{ config('app.name') }}</span>
        </a>

        <nav class="main-nav">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="{{ route('home') }}" class="nav-link {{ ($activePage ?? '') === 'home' ? 'active' : '' }}">Home</a>
                </li>
                <li class="nav-item has-dropdown">
                    <a href="{{ route('platforms.index') }}" class="nav-link">Platforms <span class="chevron">▾</span></a>
                    <div class="dropdown">
                        @foreach(config('igdb.platforms') as $pName => $pData)
                        <a href="{{ route('platform.show', ['id' => $pData['id'], 'name' => $pData['slug'] ?? $pName]) }}" class="dropdown-item">
                            <span class="di-icon">{{ $pData['icon'] }}</span>
                            {{ $pName }}
                        </a>
                        @endforeach
                    </div>
                </li>
                <li class="nav-item has-dropdown">
                    <a href="{{ route('genres.index') }}" class="nav-link">Genres <span class="chevron">▾</span></a>
                    <div class="dropdown">
                        @foreach(config('igdb.genres') as $gName => $gId)
                        <a href="{{ route('genre.show', ['id' => $gId, 'name' => $gName]) }}" class="dropdown-item">
                            {{ $gName }}
                        </a>
                        @endforeach
                    </div>
                </li>
                <li class="nav-item">
                    <a href="{{ route('search') }}" class="nav-link {{ ($activePage ?? '') === 'search' ? 'active' : '' }}">Browse</a>
                </li>
                @if(\App\Models\Setting::get('blog_visible', true))
                <li class="nav-item">
                    <a href="{{ route('blog.index') }}" class="nav-link {{ ($activePage ?? '') === 'blog' ? 'active' : '' }}">Blog</a>
                </li>
                @endif
            </ul>
        </nav>

        <div class="header-right">
            <form class="header-search" action="{{ route('search') }}" method="GET">
                <input type="search" name="q" placeholder="Search games…" class="hs-input" autocomplete="off">
                <button type="submit" class="hs-btn" aria-label="Search">⌕</button>
            </form>
            <a href="{{ route('contact') }}" class="btn btn--outline btn--sm header-contact-btn">Contact Us</a>
            @auth
            @php $basketCount = auth()->user()->cashBasketItems()->count(); @endphp
            <a href="{{ route('cash-basket.index') }}" class="header-basket" aria-label="Cash Basket ({{ $basketCount }} {{ $basketCount === 1 ? 'item' : 'items' }})">
                <svg class="header-basket__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
                @if($basketCount > 0)
                <span class="header-basket__badge">{{ $basketCount > 99 ? '99+' : $basketCount }}</span>
                @endif
            </a>
            <div class="user-menu" id="user-menu">
                <button class="user-menu__trigger" id="user-menu-trigger" aria-expanded="false" aria-haspopup="true">
                    <span class="user-menu__avatar">{{ strtoupper(substr(auth()->user()->username, 0, 1)) }}</span>
                    <span class="user-menu__name">{{ auth()->user()->username }}</span>
                    <span class="user-menu__chevron">▾</span>
                </button>
                <div class="user-menu__dropdown" id="user-menu-dropdown" role="menu">
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="user-menu__item user-menu__item--admin" role="menuitem">⚙ Admin Dashboard</a>
                    <div class="user-menu__divider"></div>
                    @endif
                    <a href="{{ route('profile') }}" class="user-menu__item" role="menuitem">👤 Profile</a>
                    <a href="{{ route('recently-viewed') }}" class="user-menu__item" role="menuitem">🕹️ Recently Viewed</a>
                    <a href="{{ route('wishlist.index') }}" class="user-menu__item" role="menuitem">♡ Wishlist</a>
                    <a href="{{ route('cash-basket.index') }}" class="user-menu__item" role="menuitem">💰 Cash Basket</a>
                    <a href="{{ route('cash-orders.index') }}" class="user-menu__item" role="menuitem">📋 My Quotes</a>
                    <a href="{{ route('security') }}" class="user-menu__item" role="menuitem">🔒 Security</a>
                    <div class="user-menu__divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="user-menu__item user-menu__item--danger" role="menuitem">🚪 Log Out</button>
                    </form>
                </div>
            </div>
            @else
            <div class="header-auth-btns">
                <a href="{{ route('login') }}" class="btn btn--outline btn--sm">Log In</a>
                <a href="{{ route('register') }}" class="btn btn--primary btn--sm">Register</a>
            </div>
            @endauth
            <button class="nav-toggle" id="nav-toggle" aria-label="Menu">☰</button>
        </div>
    </div>
</header>

<main class="site-main">
    @if(session('flash_success'))
    <div class="flash-banner flash-banner--success" role="alert">
        <span class="flash-banner__icon">✓</span>
        <span>{{ session('flash_success') }}</span>
        <button class="flash-banner__close" aria-label="Dismiss">✕</button>
    </div>
    @endif
    @if(session('flash_error'))
    <div class="flash-banner flash-banner--error" role="alert">
        <span class="flash-banner__icon">✕</span>
        <span>{{ session('flash_error') }}</span>
        <button class="flash-banner__close" aria-label="Dismiss">✕</button>
    </div>
    @endif
    @yield('content')
</main>

<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <a href="{{ route('home') }}" class="logo logo--footer">
                <span class="logo-icon">◈</span>
                <span class="logo-text">{{ config('app.name') }}</span>
            </a>
        </div>

        <div class="footer-cols">
            <div class="footer-col">
                <h4 class="footer-heading">Platforms</h4>
                <ul>
                    @foreach(config('igdb.platforms') as $pName => $pData)
                    <li><a href="{{ route('platform.show', ['id' => $pData['id'], 'name' => $pData['slug'] ?? $pName]) }}">{{ $pName }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div class="footer-col">
                <h4 class="footer-heading">Genres</h4>
                <ul>
                    @foreach(array_slice(config('igdb.genres'), 0, 6, true) as $gName => $gId)
                    <li><a href="{{ route('genre.show', ['id' => $gId, 'name' => $gName]) }}">{{ $gName }}</a></li>
                    @endforeach
                </ul>
            </div>
<div class="footer-col">
                <h4 class="footer-heading">Company</h4>
                <ul>
                    <li><a href="{{ route('about') }}">About Us</a></li>
                    <li><a href="{{ route('terms') }}">Terms &amp; Conditions</a></li>
                    <li><a href="{{ route('contact') }}">Contact Us</a></li>
                    <li><a href="{{ route('faq') }}">FAQ</a></li>
                    <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                    <li><a href="{{ route('sitemap') }}">Site Map</a></li>
                    <li><a href="{{ route('gaming-timeline') }}">Gaming Timeline</a></li>
                    <li><a href="{{ route('gaming-legends') }}">Gaming Legends</a></li>
                    <li><a href="{{ route('snake') }}">🐍 Play Snake</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}.</p>
    </div>
</footer>

<nav class="mobile-nav" id="mobile-nav">
    <form class="mobile-search" action="{{ route('search') }}" method="GET" style="width:100%;">
        <input type="search" name="q" placeholder="Search games…" class="mobile-search__input" autocomplete="off">
        <button type="submit" class="mobile-search__btn" aria-label="Search">⌕</button>
    </form>
    <a href="{{ route('home') }}">🏠 Home</a>
    <span class="m-section-title">ACCOUNT</span>
    @auth
    <span style="padding: 0.5rem 1rem; color: var(--text-muted); font-size:0.9rem;">Signed in as <strong style="color:var(--text);">{{ auth()->user()->username }}</strong></span>
    @if(auth()->user()->isAdmin())
    <a href="{{ route('admin.dashboard') }}" style="color:var(--accent);">⚙ Admin Dashboard</a>
    @endif
    <a href="{{ route('profile') }}">👤 Profile</a>
    <a href="{{ route('recently-viewed') }}">🕹️ Recently Viewed</a>
    <a href="{{ route('wishlist.index') }}">♡ Wishlist</a>
    <a href="{{ route('cash-basket.index') }}">💰 Cash Basket</a>
    <a href="{{ route('cash-orders.index') }}">📋 My Quotes</a>
    <a href="{{ route('security') }}">🔒 Security</a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" style="width:100%; text-align:left; padding:0.8rem 1rem; background:none; border:none; border-bottom:1px solid var(--border); color:var(--text-muted); font-size:1rem; cursor:pointer; font-family:'Outfit',sans-serif;">🚪 Log Out</button>
    </form>
    @else
    <a href="{{ route('login') }}">🔑 Log In</a>
    <a href="{{ route('register') }}">📝 Register</a>
    @endauth
    <span class="m-section-title">EXPLORE</span>
    <a href="{{ route('search') }}">🎮 Browse Games</a>
    @if(\App\Models\Setting::get('blog_visible', true))
    <a href="{{ route('blog.index') }}">📝 Blog</a>
    @endif
    <a href="{{ route('contact') }}">✉️ Contact Us</a>
    <span class="m-section-title">PLATFORMS</span>
    @foreach(config('igdb.platforms') as $pName => $pData)
    <a href="{{ route('platform.show', ['id' => $pData['id'], 'name' => $pData['slug'] ?? $pName]) }}">{{ $pData['icon'] }} {{ $pName }}</a>
    @endforeach
    <span class="m-section-title">GENRES</span>
    @foreach(config('igdb.genres') as $gName => $gId)
    <a href="{{ route('genre.show', ['id' => $gId, 'name' => $gName]) }}">{{ $gName }}</a>
    @endforeach
</nav>

<button id="back-to-top" aria-label="Back to top">↑</button>

{{-- Cookie consent banner --}}
<div id="cookie-banner" class="cookie-banner" role="alertdialog" aria-label="Cookie consent" hidden>
    <p class="cookie-banner__text">
        We use essential cookies to keep the site working correctly. No tracking or advertising cookies are used.
        <a href="{{ route('privacy') }}" class="cookie-banner__link">Learn more</a>
    </p>
    <button id="cookie-accept" class="btn btn--primary btn--sm cookie-banner__btn">Accept</button>
</div>

{{-- Custom confirm modal --}}
<div id="c-modal" class="c-modal" role="dialog" aria-modal="true" aria-labelledby="c-modal-message" hidden>
    <div class="c-modal__backdrop"></div>
    <div class="c-modal__box">
        <p id="c-modal-message" class="c-modal__message"></p>
        <div class="c-modal__actions">
            <button id="c-modal-cancel" class="btn btn--outline">Cancel</button>
            <button id="c-modal-ok" class="btn btn--danger">Confirm</button>
        </div>
    </div>
</div>

{{-- Get Cash platform dropdown portal (position:fixed, escapes overflow:hidden on .game-card) --}}
<div id="cash-dropdown"
     class="cash-dropdown"
     role="dialog"
     aria-modal="true"
     aria-label="Select console"
     hidden>
    <div class="cash-dropdown__header">
        <span id="cash-dropdown-title" class="cash-dropdown__title"></span>
        <button type="button" id="cash-dropdown-close" class="cash-dropdown__close" aria-label="Close">✕</button>
    </div>
    <p class="cash-dropdown__subtitle">Select a console</p>
    <div id="cash-dropdown-body" class="cash-dropdown__body"></div>
</div>
<div id="cash-dropdown-backdrop" class="cash-dropdown-backdrop" hidden></div>

<script src="{{ asset('js/main.js') }}?v={{ filemtime(public_path('js/main.js')) }}"></script>
@stack('scripts')
<script>
(function () {
    if (!localStorage.getItem('cookies_accepted')) {
        var banner = document.getElementById('cookie-banner');
        if (banner) banner.hidden = false;
    }
    var btn = document.getElementById('cookie-accept');
    if (btn) {
        btn.addEventListener('click', function () {
            localStorage.setItem('cookies_accepted', '1');
            document.getElementById('cookie-banner').hidden = true;
        });
    }
})();
</script>
</body>
</html>
