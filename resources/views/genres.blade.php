@extends('layouts.app')

@section('title', 'Sell Games by Genre — Get Cash for Your Collection')
@section('meta_description', 'Browse games by genre — action, RPG, sports, racing, shooter, and more. Check what your games are worth and get a free cash collection quote.')
@section('canonical', route('genres.index'))

@php
$genreIcons = [
    'Adventure'    => '🗺️',
    'Fighting'     => '🥊',
    'Hack & Slash' => '⚔️',
    'Indie'        => '🎨',
    'Platform'     => '🏃',
    'Puzzle'       => '🧩',
    'Racing'       => '🏎️',
    'RPG'          => '🐉',
    'Shooter'      => '🎯',
    'Simulation'   => '🏙️',
    'Sports'       => '⚽',
    'Strategy'     => '♟️',
];
@endphp

@section('content')

<!-- ===== HERO ===== -->
<div class="platform-hero">
    <div class="container">
        <div style="max-width:680px;">
            <p style="font-size:0.75rem; letter-spacing:0.15em; text-transform:uppercase; color:var(--accent); font-weight:700; margin-bottom:0.5rem;">All Genres</p>
            <h1 class="section-title">Sell Games Across Every Genre for Cash</h1>
            <p style="color:var(--text-muted); font-size:0.97rem; line-height:1.75; margin-top:0.85rem; max-width:580px;">
                From fast-paced shooters to deep RPGs and competitive sports titles — we buy physical games across every genre. Check what your collection is worth and get a free UK-wide collection.
            </p>
        </div>

        <!-- Quick-jump chips -->
        <div style="display:flex; gap:0.65rem; margin-top:2rem; flex-wrap:wrap;">
            @foreach($genresConfig as $gName => $gId)
            <a href="#genre-{{ $gId }}" class="chip">
                {{ $genreIcons[$gName] ?? '🎮' }} {{ $gName }}
            </a>
            @endforeach
        </div>
    </div>
</div>

<!-- ===== GENRE SECTIONS ===== -->
@foreach($genresConfig as $gName => $gId)
@php
    $games     = $genreGames[$gName] ?? [];
    $seo       = $genreDesc[$gId] ?? null;
    $browseUrl = route('genre.show', ['id' => $gId, 'name' => $gName]);
    $icon      = $genreIcons[$gName] ?? '🎮';
@endphp
<section class="section" id="genre-{{ $gId }}" style="{{ !$loop->first ? 'border-top:1px solid var(--border);' : '' }}">
    <div class="container">

        <!-- Genre header -->
        <div style="display:grid; grid-template-columns:1fr auto; gap:2rem; align-items:start; margin-bottom:2rem;">
            <div style="display:flex; align-items:center; gap:1.25rem;">
                <div class="platform-logo" style="width:60px; height:60px; font-size:1.6rem; flex-shrink:0;">{{ $icon }}</div>
                <div>
                    <h2 style="font-size:1.4rem; font-weight:800; color:var(--text); margin:0 0 0.35rem;">{{ $gName }}</h2>
                    @if($seo)
                    <p style="color:var(--text-muted); font-size:0.88rem; line-height:1.6; max-width:580px; margin:0;">
                        {{ $seo['body'] }}
                    </p>
                    @endif
                </div>
            </div>
            <div style="flex-shrink:0;">
                <a href="{{ $browseUrl }}" class="btn btn--primary btn--sm" style="white-space:nowrap;">
                    Browse All {{ $gName }} →
                </a>
            </div>
        </div>

        <!-- Game cards -->
        @if(!empty($games))
        <div class="games-grid games-grid--large fade-up">
            @foreach($games as $game)
            <x-game-card :game="$game" />
            @endforeach
        </div>
        <div style="margin-top:1.5rem; text-align:center;">
            <a href="{{ $browseUrl }}" class="btn btn--outline btn--sm">
                See all {{ $gName }} games →
            </a>
        </div>
        @else
        <p style="color:var(--text-muted); font-size:0.9rem;">No priced games found for this genre yet.</p>
        @endif

    </div>
</section>
@endforeach

<!-- ===== BOTTOM CTA ===== -->
<section class="section" style="background:rgba(255,255,255,0.02); border-top:1px solid var(--border);">
    <div class="container" style="text-align:center;">
        <h2 style="font-size:1.25rem; font-weight:700; margin-bottom:0.75rem;">Looking for a Specific Title?</h2>
        <p style="color:var(--text-muted); font-size:0.95rem; max-width:480px; margin:0 auto 1.5rem; line-height:1.7;">
            Search across all genres and platforms to find exactly what you want to sell and build your cash basket in minutes.
        </p>
        <a href="{{ route('search') }}" class="btn btn--primary">💰 Search All Games</a>
    </div>
</section>

@endsection
