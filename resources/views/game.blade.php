@extends('layouts.app')

@php
    if ($game) {
        $name        = $game['name'] ?? 'Unknown';
        $summary     = $game['summary'] ?? '';
        $storyline   = $game['storyline'] ?? '';
        $releaseDate = isset($game['first_release_date']) ? format_date($game['first_release_date']) : 'TBA';
        $coverId     = $game['cover']['image_id'] ?? null;
        $coverUrl    = $coverId ? igdb_img($coverId, 'cover_big') : asset('img/placeholder.jpg');
        $backdropUrl = $coverId ? igdb_img($coverId, '1080p') : '';

        $platforms   = $game['platforms'] ?? [];
        $genres      = $game['genres'] ?? [];
        $modes       = $game['game_modes'] ?? [];
        $screenshots = $game['screenshots'] ?? [];
        $artworks    = $game['artworks'] ?? [];
        $videos      = $game['videos'] ?? [];
        $similar     = $game['similar_games'] ?? [];
        $websites    = $game['websites'] ?? [];

        $developer = '';
        $publisher = '';
        foreach (($game['involved_companies'] ?? []) as $ic) {
            if (!empty($ic['developer'])) $developer = $ic['company']['name'] ?? '';
            if (!empty($ic['publisher'])) $publisher = $ic['company']['name'] ?? '';
        }

        // $platformsData is computed inline next to the button (see @section content)
        // to avoid any Blade @php / @section scope ordering issues.
    }
@endphp

@section('title', $game ? ($game['name'] ?? 'Game') : 'Game Not Found')

@php
    $seoDesc      = '';
    $seoGenres    = '';
    $seoPlatforms = '';
    if ($game) {
        $seoDesc = $summary
            ? \Illuminate\Support\Str::limit(strip_tags($summary), 160)
            : ($name . ' — browse game info, platforms, ratings, and get a cash quote.');
        $seoGenres    = implode(', ', array_column($genres, 'name'));
        $seoPlatforms = implode(', ', array_column($platforms, 'name'));
    }
@endphp

@if($game)
@section('meta_description', $seoDesc)
@section('canonical', !empty($game['slug']) ? route('game.show', ['slug' => $game['slug']]) : url('/game/' . $game['id']))
@section('og_type', 'game')
@section('og_image', $coverUrl)
@endif

@push('head_meta')
@if($game)
@php
    // VideoGame schema — built as a PHP array so json_encode handles all escaping
    $videoGameSchema = [
        '@context' => 'https://schema.org',
        '@type'    => 'VideoGame',
        'name'     => $name,
    ];
    if ($seoDesc)    $videoGameSchema['description']  = $seoDesc;
    if ($coverUrl)   $videoGameSchema['image']         = $coverUrl;
    if ($releaseDate !== 'TBA' && isset($game['first_release_date'])) {
        $videoGameSchema['datePublished'] = date('Y-m-d', $game['first_release_date']);
    }
    if ($seoGenres)    $videoGameSchema['genre']        = $seoGenres;
    if ($seoPlatforms) $videoGameSchema['gamePlatform'] = $seoPlatforms;
    if ($developer)    $videoGameSchema['author']       = ['@type' => 'Organization', 'name' => $developer];

    // Offer schema — cash trade-in price from this site
    if ($pricing && !$pricing['is_free'] && isset($pricing['price_numeric']) && $pricing['price_numeric'] > 0) {
        $videoGameSchema['offers'] = [
            '@type'         => 'Offer',
            'name'          => 'Cash Trade-In Price',
            'description'   => 'Sell your copy of ' . $name . ' for cash — instant quote, we collect from you.',
            'price'         => number_format((float) $pricing['price_numeric'], 2, '.', ''),
            'priceCurrency' => 'GBP',
            'url'           => !empty($game['slug']) ? route('game.show', ['slug' => $game['slug']]) : url('/game/' . $game['id']),
            'availability'  => 'https://schema.org/InStock',
            'seller'        => [
                '@type' => 'Organization',
                'name'  => config('app.name'),
                'url'   => route('home'),
            ],
        ];
    }

    // BreadcrumbList schema — mirrors the visible breadcrumb nav
    $breadcrumbSchema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home',  'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Games', 'item' => route('search')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $name],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($videoGameSchema,  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
@endpush

@section('content')

@if(!$game && $error)
<div class="container" style="padding: 4rem 0;">
    <div class="error-banner">⚠️ {{ $error }}</div>
</div>
@elseif(!$game)
<div class="container">
    <div class="empty-state">
        <div class="icon">🎮</div>
        <h3>Game not found</h3>
        <p>That game doesn't exist in our database.</p>
        <a href="{{ route('home') }}" class="btn btn--primary" style="margin-top:1.5rem">Back Home</a>
    </div>
</div>
@else

<!-- Lightbox -->
<div class="lightbox" id="lightbox" role="dialog" aria-modal="true">
    <button class="lightbox-close" id="lb-close" aria-label="Close">✕</button>
    <img src="" id="lb-img" alt="Screenshot">
</div>

<!-- ===== BREADCRUMB ===== -->
<nav class="breadcrumb-bar" aria-label="Breadcrumb">
    <div class="container">
        <ol class="breadcrumb">
            <li><a href="{{ route('home') }}">Home</a></li>
            <li><a href="{{ route('search') }}">Games</a></li>
            <li aria-current="page">{{ $name }}</li>
        </ol>
    </div>
</nav>

<!-- ===== GAME HERO ===== -->
<div class="game-detail-hero">
    @if($backdropUrl)
    <div class="gd-backdrop" style="background-image:url('{{ $backdropUrl }}')"></div>
    @endif
    <div class="container">
        <div class="gd-inner">
            <div class="gd-cover">
                <img src="{{ $coverUrl }}" alt="{{ $name }} cover">
            </div>

            <div class="gd-info">
                @if($platforms)
                <div class="gd-platforms">
                    @foreach($platforms as $p)
                    <span class="gd-platform-tag">{{ e($p['abbreviation'] ?? $p['name'] ?? '') }}</span>
                    @endforeach
                </div>
                @endif

                <h1 class="gd-title">{{ $name }}</h1>


                <!-- Meta grid -->
                <div class="gd-meta-grid">
                    <div class="gd-meta-item">
                        <label>Released</label>
                        <span>{{ $releaseDate }}</span>
                    </div>
                    @if($developer)
                    <div class="gd-meta-item">
                        <label>Developer</label>
                        <span>{{ e($developer) }}</span>
                    </div>
                    @endif
                    @if($publisher && $publisher !== $developer)
                    <div class="gd-meta-item">
                        <label>Publisher</label>
                        <span>{{ e($publisher) }}</span>
                    </div>
                    @endif
                    @if($genres)
                    <div class="gd-meta-item">
                        <label>Genre</label>
                        <span>{{ implode(', ', array_column($genres, 'name')) }}</span>
                    </div>
                    @endif
                    @if($modes)
                    <div class="gd-meta-item">
                        <label>Mode</label>
                        <span>{{ e(implode(', ', array_column($modes, 'name'))) }}</span>
                    </div>
                    @endif
                </div>

                {{-- Wishlist + Get Cash actions --}}
                {{-- Pre-compute platform price rows so both auth and guest sections can use them --}}
                @if($pricing && !$pricing['is_free'])
                @php
                    $__pd   = [];
                    $__allP = config('igdb.all_platforms');
                    foreach ($platforms as $__p) {
                        $__pid = $__p['id'] ?? null;
                        if (! $__pid || ! isset($__allP[$__pid]) || ! $gamePrice) continue;
                        $__pp = $gamePrice->getComputedPriceForPlatform((int) $__pid, $franchiseNames, $game['name'] ?? null);
                        if ($__pp && ! $__pp['is_free']) {
                            $__pd[] = ['id' => $__pid, 'name' => $__allP[$__pid], 'price' => number_format($__pp['price_numeric'], 2)];
                        }
                    }
                @endphp
                @endif
                <div class="gd-action-buttons">
                    @auth
                    {{-- Wishlist button --}}
                    @if($inWishlist)
                    <form method="POST" action="{{ route('wishlist.destroy', $game['id']) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn gd-wishlist-btn gd-wishlist-btn--active">
                            ♥ In Wishlist
                        </button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('wishlist.store') }}">
                        @csrf
                        <input type="hidden" name="igdb_game_id" value="{{ $game['id'] }}">
                        <input type="hidden" name="game_title" value="{{ $name }}">
                        <input type="hidden" name="cover_url" value="{{ $coverUrl }}">
                        @if($steamAppId)
                        <input type="hidden" name="steam_app_id" value="{{ $steamAppId }}">
                        @endif
                        @if(isset($game['first_release_date']))
                        <input type="hidden" name="release_date" value="{{ $game['first_release_date'] }}">
                        @endif
                        <button type="submit" class="btn gd-wishlist-btn">
                            ♡ Add to Wishlist
                        </button>
                    </form>
                    @endif

                    {{-- Get Cash button — only if a non-free price is available --}}
                    @if($pricing && !$pricing['is_free'])
                        @if(!empty($__pd))
                        {{-- Dropdown trigger: pre-built rows in <template>, JS clones to portal --}}
                        <button type="button"
                            class="btn gd-cash-btn js-cash-btn"
                            data-tpl="ctpl-gd-{{ $game['id'] }}">
                            💰 Get Cash
                        </button>
                        <template id="ctpl-gd-{{ $game['id'] }}" data-title="{{ $name }}">
                            @foreach($__pd as $__item)
                            <div class="cash-dropdown__item">
                                <div class="cash-dropdown__item-info">
                                    <span class="cash-dropdown__item-name">{{ $__item['name'] }}</span>
                                    <span class="cash-dropdown__item-price">£{{ $__item['price'] }}</span>
                                </div>
                                <form method="POST" action="{{ route('cash-basket.store') }}">
                                    @csrf
                                    <input type="hidden" name="igdb_game_id" value="{{ $game['id'] }}">
                                    <input type="hidden" name="platform_id"  value="{{ $__item['id'] }}">
                                    <input type="hidden" name="game_title"   value="{{ $name }}">
                                    <input type="hidden" name="cover_url"    value="{{ $coverUrl }}">
                                    @if($steamAppId)
                                    <input type="hidden" name="steam_app_id" value="{{ $steamAppId }}">
                                    @endif
                                    @if(isset($game['first_release_date']))
                                    <input type="hidden" name="release_date" value="{{ $game['first_release_date'] }}">
                                    @endif
                                    <button type="submit" class="btn btn--primary btn--xs">Add</button>
                                </form>
                            </div>
                            @endforeach
                        </template>
                        @else
                        {{-- Fallback: no recognised platforms — add without platform --}}
                        <form method="POST" action="{{ route('cash-basket.store') }}">
                            @csrf
                            <input type="hidden" name="igdb_game_id" value="{{ $game['id'] }}">
                            <input type="hidden" name="game_title" value="{{ $name }}">
                            <input type="hidden" name="cover_url" value="{{ $coverUrl }}">
                            @if($steamAppId)
                            <input type="hidden" name="steam_app_id" value="{{ $steamAppId }}">
                            @endif
                            @if(isset($game['first_release_date']))
                            <input type="hidden" name="release_date" value="{{ $game['first_release_date'] }}">
                            @endif
                            <button type="submit" class="btn gd-cash-btn">
                                💰 Get Cash ({{ $pricing['display_price'] }})
                            </button>
                        </form>
                        @endif
                    @endif

                    @else
                    {{-- Guest: link to login --}}
                    <a href="{{ route('login') }}" class="btn gd-wishlist-btn">♡ Add to Wishlist</a>
                    @if($pricing && !$pricing['is_free'])
                        @if(!empty($__pd))
                        <button type="button"
                            class="btn gd-cash-btn js-cash-btn"
                            data-tpl="ctpl-gd-{{ $game['id'] }}">
                            💰 Get Cash
                        </button>
                        <template id="ctpl-gd-{{ $game['id'] }}" data-title="{{ $name }}">
                            @foreach($__pd as $__item)
                            <div class="cash-dropdown__item">
                                <div class="cash-dropdown__item-info">
                                    <span class="cash-dropdown__item-name">{{ $__item['name'] }}</span>
                                    <span class="cash-dropdown__item-price">£{{ $__item['price'] }}</span>
                                </div>
                                <a href="{{ route('login') }}" class="btn btn--primary btn--xs">Sign In</a>
                            </div>
                            @endforeach
                        </template>
                        @else
                        <a href="{{ route('login') }}" class="btn gd-cash-btn">💰 Get Cash ({{ $pricing['display_price'] }})</a>
                        @endif
                    @endif
                    @endauth

                    @if(auth()->check() && auth()->user()->isAdmin())
                    <button type="button" id="edit-price-toggle" class="btn gd-wishlist-btn" style="border-color:var(--accent-2); color:var(--accent-2); font-size:0.82rem;">
                        ✏️ Edit Price
                    </button>
                    @endif
                </div>

                @if(auth()->check() && auth()->user()->isAdmin())
                <div id="edit-price-panel" style="display:none; margin-top:1.25rem; background:rgba(255,255,255,0.04); border:1px solid var(--border); border-radius:8px; padding:1.25rem;">
                    <p style="font-size:0.8rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--accent-2); margin-bottom:1rem;">Admin — Set Price Override</p>
                    <form method="POST" action="{{ route('admin.no-price-review.set-price', $game['id']) }}"
                          style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
                        @csrf
                        @php
                            $__allP     = config('igdb.all_platforms');
                            $__gamePids = array_map(fn($p) => $p['id'] ?? null, $platforms ?? []);
                            $__opts     = array_filter($__allP, fn($pid) => in_array($pid, $__gamePids), ARRAY_FILTER_USE_KEY);
                            if (empty($__opts)) $__opts = $__allP;
                        @endphp
                        <select name="platform_id" class="form-input" style="min-width:150px; font-size:0.85rem;">
                            @foreach($__opts as $pid => $pName)
                            <option value="{{ $pid }}">{{ $pName }}</option>
                            @endforeach
                        </select>
                        <div style="display:flex; align-items:center; gap:0.35rem;">
                            <span style="color:var(--text-muted);">£</span>
                            <input type="number" name="price" min="0.01" max="9999.99" step="0.01"
                                   class="form-input" style="width:90px; font-size:0.85rem;"
                                   placeholder="0.00">
                        </div>
                        <button type="submit" class="btn btn--primary btn--sm">Save</button>
                    </form>
                    @if($gamePrice && !empty($gamePrice->price_overrides))
                    <div style="margin-top:1rem; border-top:1px solid var(--border); padding-top:0.85rem;">
                        <p style="font-size:0.78rem; color:var(--text-muted); margin-bottom:0.5rem; font-weight:600;">Current overrides</p>
                        <div style="display:flex; flex-wrap:wrap; gap:0.4rem;">
                            @foreach($gamePrice->price_overrides as $pid => $pPrice)
                            <span style="font-size:0.8rem; background:rgba(255,255,255,0.06); border:1px solid var(--border); border-radius:4px; padding:0.2rem 0.55rem;">
                                {{ config('igdb.all_platforms')[$pid] ?? 'Platform '.$pid }}: £{{ number_format($pPrice, 2) }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                <script>
                    document.getElementById('edit-price-toggle').addEventListener('click', function () {
                        var panel = document.getElementById('edit-price-panel');
                        var open  = panel.style.display !== 'none';
                        panel.style.display = open ? 'none' : 'block';
                        this.textContent   = open ? '✏️ Edit Price' : '✕ Close';
                    });
                </script>
                @endif

                @if($summary)
                <p class="gd-summary" style="margin-top:1.5rem;">{!! nl2br(e($summary)) !!}</p>
                @endif

            </div>
        </div>
    </div>
</div>



@endif
@endsection
