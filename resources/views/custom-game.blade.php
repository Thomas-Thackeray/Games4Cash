@extends('layouts.app')

@php
    $coverUrl    = $game->cover_image_path ? asset('storage/' . $game->cover_image_path) : asset('img/placeholder.jpg');
    $backdropUrl = $game->cover_image_path ? asset('storage/' . $game->cover_image_path) : '';
@endphp

@section('title', $game->title)
@section('meta_description', $game->summary ? \Illuminate\Support\Str::limit(strip_tags($game->summary), 160) : $game->title . ' — browse game info and get a cash trade-in quote.')
@section('og_type', 'game')
@section('og_image', $coverUrl)
@section('canonical', route('game.show', $game->slug))

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
    @if($backdropUrl)
    <div class="gd-backdrop" style="background-image:url('{{ $backdropUrl }}')"></div>
    @endif
    <div class="container">
        <div class="gd-inner">
            <div class="gd-cover">
                <img src="{{ $coverUrl }}" alt="{{ $game->title }} cover"
                     onerror="this.onerror=null;this.src='{{ asset('img/placeholder.jpg') }}'">
            </div>

            <div class="gd-info">
                {{-- Platform tags: show platforms that have prices set --}}
                @if(!empty($pricingRows))
                <div class="gd-platforms">
                    @foreach($pricingRows as $row)
                    <span class="gd-platform-tag">{{ $row['platform_name'] }}</span>
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
                    @if(!empty($game->genres))
                    <div class="gd-meta-item">
                        <label>Genre</label>
                        <span>{{ implode(', ', $game->genres) }}</span>
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
                            <form method="POST" action="{{ route('cash-basket.store') }}">
                                @csrf
                                <input type="hidden" name="custom_game_id" value="{{ $game->id }}">
                                <input type="hidden" name="platform_id"    value="{{ $row['platform_id'] }}">
                                <input type="hidden" name="game_title"     value="{{ $game->title }}">
                                <input type="hidden" name="cover_url"      value="{{ $coverUrl }}">
                                <button type="submit" class="btn btn--primary btn--xs">Add</button>
                            </form>
                        </div>
                        @endforeach
                    </template>
                    @else
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
                            <a href="{{ route('login') }}" class="btn btn--primary btn--xs">Sign In</a>
                        </div>
                        @endforeach
                    </template>
                    @endauth
                </div>
                @endif

                @if($game->summary)
                <p class="gd-summary" style="margin-top:1.5rem;">{{ $game->summary }}</p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
