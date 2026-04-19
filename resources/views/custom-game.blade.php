@extends('layouts.app')

@section('title', $game->title)
@section('meta_description', $game->summary ? \Illuminate\Support\Str::limit(strip_tags($game->summary), 160) : $game->title . ' — browse game info and get a cash trade-in quote.')
@section('og_type', 'game')
@if($game->cover_image_path)
@section('og_image', Storage::url($game->cover_image_path))
@endif

@section('content')

<!-- ===== BREADCRUMB ===== -->
<nav class="breadcrumb-bar" aria-label="Breadcrumb">
    <div class="container">
        <ol class="breadcrumb">
            <li><a href="{{ route('home') }}">Home</a></li>
            <li><a href="{{ route('search') }}">Games</a></li>
            <li aria-current="page">{{ $game->title }}</li>
        </ol>
    </div>
</nav>

<!-- ===== HERO ===== -->
<div class="game-detail-hero">
    <div class="container">
        <div class="gd-inner">
            <div class="gd-cover">
                @if($game->cover_image_path)
                <img src="{{ Storage::url($game->cover_image_path) }}" alt="{{ $game->title }} cover">
                @else
                <img src="{{ asset('img/placeholder.jpg') }}" alt="No cover available">
                @endif
            </div>

            <div class="gd-info">
                @if(!empty($game->genres))
                <div class="gd-platforms">
                    @foreach($game->genres as $genre)
                    <span class="gd-platform-tag">{{ $genre }}</span>
                    @endforeach
                </div>
                @endif

                <h1 class="gd-title">{{ $game->title }}</h1>

                <div class="gd-meta-grid">
                    @if($game->release_year)
                    <div class="gd-meta-item">
                        <label>Released</label>
                        <span>{{ $game->release_year }}</span>
                    </div>
                    @endif
                    @if($game->developer)
                    <div class="gd-meta-item">
                        <label>Developer</label>
                        <span>{{ $game->developer }}</span>
                    </div>
                    @endif
                    @if($game->publisher && $game->publisher !== $game->developer)
                    <div class="gd-meta-item">
                        <label>Publisher</label>
                        <span>{{ $game->publisher }}</span>
                    </div>
                    @endif
                    @if(!empty($pricingRows))
                    <div class="gd-meta-item">
                        <label>Best Cash Price</label>
                        <span>Up to £{{ number_format(max(array_column($pricingRows, 'price_numeric')), 2) }}</span>
                    </div>
                    @endif
                </div>

                @if(!empty($pricingRows))
                <div class="gd-action-buttons">
                    @auth
                    <button type="button"
                        class="btn gd-cash-btn js-cash-btn"
                        data-tpl="ctpl-custom-{{ $game->id }}">
                        💰 Get Cash
                    </button>
                    <template id="ctpl-custom-{{ $game->id }}" data-title="{{ $game->title }}">
                        @foreach($pricingRows as $row)
                        <div class="cash-dropdown__item">
                            <div class="cash-dropdown__item-info">
                                <span class="cash-dropdown__item-name">{{ $row['platform_name'] }}</span>
                                <span class="cash-dropdown__item-price">{{ $row['display_price'] }}</span>
                            </div>
                            @php
                                $platformId = array_search($row['platform_name'], $platforms);
                            @endphp
                            <form method="POST" action="{{ route('cash-basket.store') }}">
                                @csrf
                                <input type="hidden" name="custom_game_id" value="{{ $game->id }}">
                                <input type="hidden" name="platform_id"    value="{{ $platformId }}">
                                <input type="hidden" name="game_title"     value="{{ $game->title }}">
                                @if($game->cover_image_path)
                                <input type="hidden" name="cover_url"      value="{{ Storage::url($game->cover_image_path) }}">
                                @endif
                                <button type="submit" class="btn btn--primary btn--xs">Add</button>
                            </form>
                        </div>
                        @endforeach
                    </template>
                    @else
                    <a href="{{ route('login') }}" class="btn gd-cash-btn">💰 Get Cash</a>
                    @endauth
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- ===== BODY ===== -->
<div class="container" style="padding-top:2.5rem; padding-bottom:3rem;">
    @if($game->summary)
    <section style="max-width:800px; margin-bottom:2.5rem;">
        <h2 style="font-size:1.2rem; font-weight:700; margin-bottom:0.75rem;">About This Game</h2>
        <p style="color:var(--text-muted); line-height:1.8; white-space:pre-wrap;">{{ $game->summary }}</p>
    </section>
    @endif

    @if(!empty($pricingRows))
    <section>
        <h2 style="font-size:1.2rem; font-weight:700; margin-bottom:1rem;">Cash Trade-in Prices</h2>
        <div style="display:flex; flex-direction:column; gap:0.5rem; max-width:480px;">
            @foreach($pricingRows as $row)
            <div style="display:flex; justify-content:space-between; align-items:center; padding:0.75rem 1rem; background:var(--card-bg); border:1px solid var(--border); border-radius:8px;">
                <span style="font-size:0.9rem;">{{ $row['platform_name'] }}</span>
                <span style="font-weight:700; color:var(--accent);">{{ $row['display_price'] }}</span>
            </div>
            @endforeach
        </div>
        <p style="margin-top:1rem; font-size:0.82rem; color:var(--text-dim);">
            Prices shown are for games in complete condition. Final offer may vary based on condition.
            @auth
            <a href="{{ route('cash-basket.index') }}" style="color:var(--accent);">View your cash basket →</a>
            @else
            <a href="{{ route('login') }}" style="color:var(--accent);">Log in to get a quote →</a>
            @endauth
        </p>
    </section>
    @endif
</div>

@endsection
