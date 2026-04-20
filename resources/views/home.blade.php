@extends('layouts.app')

@php $activePage = 'home'; @endphp
@section('seo_title', config('app.name') . ' | Get Cash for Your Old Games')
@section('meta_description', 'Turn your old games into cash. Browse thousands of titles, check what they\'re worth, and get a free collection quote. Fast, easy, free pickup.')
@section('canonical', route('home'))

@push('head_meta')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@graph": [
        {
            "@type": "Organization",
            "name": "{{ config('app.name') }}",
            "url": "{{ route('home') }}",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ asset('img/og-default.jpg') }}"
            },
            "contactPoint": {
                "@type": "ContactPoint",
                "contactType": "customer support",
                "url": "{{ route('contact') }}"
            }
        },
        {
            "@type": "WebSite",
            "name": "{{ config('app.name') }}",
            "url": "{{ route('home') }}",
            "potentialAction": {
                "@type": "SearchAction",
                "target": {
                    "@type": "EntryPoint",
                    "urlTemplate": "{{ route('search') }}?q={search_term_string}"
                },
                "query-input": "required name=search_term_string"
            }
        }
    ]
}
</script>
@endpush

@section('content')

<!-- ===== HERO ===== -->
<section class="hero">
    <div class="hero__bg"></div>
    <div class="hero__grid-lines"></div>
    <div class="container">
        <div class="hero__content">
            <p class="hero__eyebrow">Turn Your Games Into Cash</p>
            <h1 class="hero__title">
                Get <span>Cash</span><br>For Your<br>Old Games
            </h1>
            <p class="hero__desc">
                Browse thousands of titles, check what your games are worth, and sell them fast.
                Free collection from your door — we'll be in touch within 24 hours.
            </p>
            <div class="hero__actions">
                <a href="{{ route('search') }}" class="btn btn--primary">💰 See What Your Games Are Worth</a>
            </div>
        </div>
    </div>
</section>

<!-- ===== PLATFORM STRIP ===== -->
<div class="container">
    <div class="platform-strip">
        @foreach(config('igdb.platforms') as $pName => $pData)
        <a href="{{ route('platform.show', ['id' => $pData['id'], 'name' => $pData['slug'] ?? $pName]) }}" class="platform-pill">
            <span class="icon">{{ $pData['icon'] }}</span>
            {{ $pName }}
        </a>
        @endforeach
        @foreach(config('igdb.genres') as $gName => $gId)
        <a href="{{ route('genre.show', ['id' => $gId, 'name' => $gName]) }}" class="platform-pill">
            {{ $gName }}
        </a>
        @endforeach
    </div>
</div>






<!-- ===== RECENTLY VIEWED / DISCOVER ===== -->
@if(!empty($games))
<section class="section">
    <div class="container">
        <div class="section-header fade-up">
            <h2 class="section-title">{{ $sectionTitle }}</h2>
            <a href="{{ route('search') }}" class="section-link">Browse All →</a>
        </div>
        <div class="games-grid games-grid--large fade-up">
            @foreach($games as $game)
            <x-game-card :game="$game" />
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- ===== HOW IT WORKS ===== -->
<section class="section" style="background:rgba(255,255,255,0.02); border-top:1px solid var(--border); border-bottom:1px solid var(--border);">
    <div class="container">
        <div class="section-header fade-up" style="margin-bottom:2rem;">
            <h2 class="section-title">How It Works</h2>
        </div>
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:2rem;" class="fade-up">
            <div style="text-align:center;">
                <div style="font-size:2rem; margin-bottom:0.75rem;">🔍</div>
                <h3 style="font-size:1rem; font-weight:700; margin-bottom:0.5rem;">1. Search Your Games</h3>
                <p style="color:var(--text-muted); font-size:0.9rem; line-height:1.65;">Browse by platform, genre, or search for a specific title to instantly see what we'll pay for it.</p>
            </div>
            <div style="text-align:center;">
                <div style="font-size:2rem; margin-bottom:0.75rem;">🛒</div>
                <h3 style="font-size:1rem; font-weight:700; margin-bottom:0.5rem;">2. Add to Your Basket</h3>
                <p style="color:var(--text-muted); font-size:0.9rem; line-height:1.65;">Select the platform and condition for each game and add them to your cash basket. Prices update in real time.</p>
            </div>
            <div style="text-align:center;">
                <div style="font-size:2rem; margin-bottom:0.75rem;">📦</div>
                <h3 style="font-size:1rem; font-weight:700; margin-bottom:0.5rem;">3. Submit Your Quote</h3>
                <p style="color:var(--text-muted); font-size:0.9rem; line-height:1.65;">Once you're happy with the total, submit your quote. We'll confirm by email and arrange free collection from your door.</p>
            </div>
            <div style="text-align:center;">
                <div style="font-size:2rem; margin-bottom:0.75rem;">💷</div>
                <h3 style="font-size:1rem; font-weight:700; margin-bottom:0.5rem;">4. Get Paid</h3>
                <p style="color:var(--text-muted); font-size:0.9rem; line-height:1.65;">After we've received and checked your games, we'll pay you quickly. No waiting around, no hidden deductions.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== ABOUT / SEO TEXT ===== -->
<section class="section">
    <div class="container">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:3rem; align-items:start;" class="fade-up">
            <div>
                <h2 style="font-size:1.2rem; font-weight:700; margin-bottom:0.85rem;">Sell Your Games for Cash — Fast, Easy, Free</h2>
                <p style="color:var(--text-muted); font-size:0.95rem; line-height:1.75; margin-bottom:1rem;">
                    {{ config('app.name') }} makes it simple to turn your old video game collection into cash. Whether you've got a handful of titles gathering dust or a full shelf of games you've moved on from, we'll give you a fair, instant price for every one of them — with no auction fees, no private buyer negotiation, and no trips to the post office.
                </p>
                <p style="color:var(--text-muted); font-size:0.95rem; line-height:1.75;">
                    We buy games across every major platform, including PlayStation 5, PlayStation 4, Xbox Series X, Xbox One, Nintendo Switch, and more. Browse by platform or genre, check the cash value of your titles, and submit a quote in minutes. We arrange free door-to-door collection anywhere in the UK — all you have to do is pack your games and hand them over.
                </p>
            </div>
            <div>
                <h2 style="font-size:1.2rem; font-weight:700; margin-bottom:0.85rem;">Why Sell With Us?</h2>
                <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:0.75rem;">
                    @foreach([
                        ['icon'=>'✅', 'text'=>'Instant cash prices — no haggling, no auctions'],
                        ['icon'=>'🚚', 'text'=>'Free collection from your door anywhere in the UK'],
                        ['icon'=>'💷', 'text'=>'Paid quickly after your games are received and checked'],
                        ['icon'=>'🎮', 'text'=>'Thousands of titles across all major platforms and genres'],
                        ['icon'=>'📧', 'text'=>'Email confirmation and updates every step of the way'],
                        ['icon'=>'🔒', 'text'=>'Secure account, private quote history, no junk mail'],
                    ] as $point)
                    <li style="display:flex; gap:0.75rem; align-items:flex-start; color:var(--text-muted); font-size:0.92rem; line-height:1.55;">
                        <span style="flex-shrink:0; margin-top:0.05rem;">{{ $point['icon'] }}</span>
                        <span>{{ $point['text'] }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

@endsection
