@extends('layouts.app')

@php
    if ($game) {
        $name        = $game['name'] ?? 'Unknown';
        $summary     = $game['summary'] ?? '';
        $storyline   = $game['storyline'] ?? '';
        $rating      = isset($game['rating']) ? round($game['rating']) : null;
        $ratingCount = $game['rating_count'] ?? 0;
        $rClass      = $rating ? rating_class($rating) : '';
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
@section('canonical', route('game.show', $game['id']))
@section('og_type', 'game')
@section('og_image', $coverUrl)
@endif

@push('head_meta')
@if($game)
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "VideoGame",
    "name": "{{ e($name) }}"
    {!! $seoDesc      ? ', "description": "' . e($seoDesc) . '"'      : '' !!}
    {!! $coverUrl     ? ', "image": "' . $coverUrl . '"'               : '' !!}
    {!! ($releaseDate !== 'TBA' && isset($game['first_release_date'])) ? ', "datePublished": "' . date('Y-m-d', $game['first_release_date']) . '"' : '' !!}
    {!! $seoGenres    ? ', "genre": "' . e($seoGenres) . '"'           : '' !!}
    {!! $seoPlatforms ? ', "gamePlatform": "' . e($seoPlatforms) . '"' : '' !!}
    {!! $developer    ? ', "author": {"@type":"Organization","name":"' . e($developer) . '"}' : '' !!}
    {!! ($rating && $ratingCount > 0) ? ', "aggregateRating": {"@type":"AggregateRating","ratingValue":"' . $rating . '","ratingCount":"' . $ratingCount . '","bestRating":"100","worstRating":"0"}' : '' !!}
}
</script>
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
                <img src="{{ $coverUrl }}" alt="{{ e($name) }} cover">
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

                @if($rating)
                <div class="gd-rating-block">
                    <span class="gd-score {{ $rClass }}">{{ $rating }}</span>
                    <div>
                        {!! star_rating($rating) !!}
                        <div style="font-size:0.8rem; color:var(--text-muted); margin-top:4px">
                            Based on {{ number_format($ratingCount) }} ratings
                        </div>
                    </div>
                </div>
                @endif

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
                    $__base = $pricing['price_numeric'];
                    foreach ($platforms as $__p) {
                        $__pid = $__p['id'] ?? null;
                        if (! $__pid || ! isset($__allP[$__pid])) continue;
                        $__mod  = (float) \App\Models\Setting::get("platform_modifier_{$__pid}", 0);
                        $__adj  = max(0.01, round($__base * (1 + $__mod / 100), 2));
                        $__pd[] = ['id' => $__pid, 'name' => $__allP[$__pid], 'price' => number_format($__adj, 2)];
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
                        <input type="hidden" name="game_title" value="{{ e($name) }}">
                        <input type="hidden" name="cover_url" value="{{ e($coverUrl) }}">
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
                        <template id="ctpl-gd-{{ $game['id'] }}" data-title="{{ e($name) }}">
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
                                    <input type="hidden" name="game_title"   value="{{ e($name) }}">
                                    <input type="hidden" name="cover_url"    value="{{ e($coverUrl) }}">
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
                            <input type="hidden" name="game_title" value="{{ e($name) }}">
                            <input type="hidden" name="cover_url" value="{{ e($coverUrl) }}">
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
                        <template id="ctpl-gd-{{ $game['id'] }}" data-title="{{ e($name) }}">
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
                </div>

                @if($summary)
                <p class="gd-summary">{!! nl2br(e($summary)) !!}</p>
                @endif

            </div>
        </div>
    </div>
</div>



@endif
@endsection
