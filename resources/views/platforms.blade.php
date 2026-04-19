@extends('layouts.app')

@section('title', 'Sell Games by Platform — Get Cash for Your Collection')
@section('meta_description', 'Browse every platform we buy — PlayStation 5, PlayStation 4, Xbox Series X|S, Xbox One, Nintendo Switch, and PC. Get instant cash prices and free UK collection.')
@section('canonical', route('platforms.index'))

@section('content')

<!-- ===== HERO ===== -->
<div class="platform-hero">
    <div class="container">
        <div style="max-width:680px;">
            <p style="font-size:0.75rem; letter-spacing:0.15em; text-transform:uppercase; color:var(--accent); font-weight:700; margin-bottom:0.5rem;">All Platforms</p>
            <h1 class="section-title">Sell Games on Any Platform for Cash</h1>
            <p style="color:var(--text-muted); font-size:0.97rem; line-height:1.75; margin-top:0.85rem; max-width:580px;">
                We buy physical games across every major console and PC. Browse by platform below to see what your collection is worth — instant prices, free door-to-door collection anywhere in the UK.
            </p>
        </div>

        <!-- Quick-jump chips -->
        <div style="display:flex; gap:0.65rem; margin-top:2rem; flex-wrap:wrap;">
            @foreach($platformsConfig as $pName => $pData)
            <a href="#platform-{{ $pData['id'] }}" class="chip">
                {{ $pData['icon'] }} {{ $pName }}
            </a>
            @endforeach
        </div>
    </div>
</div>

<!-- ===== PLATFORM SECTIONS ===== -->
@foreach($platformsConfig as $pName => $pData)
@php
    $games    = $platformGames[$pName] ?? [];
    $slug     = $pData['slug'] ?? $pName;
    $browseUrl = route('platform.show', ['id' => $pData['id'], 'name' => $slug]);
@endphp
<section class="section" id="platform-{{ $pData['id'] }}" style="{{ !$loop->first ? 'border-top:1px solid var(--border);' : '' }}">
    <div class="container">

        <!-- Platform header -->
        <div style="display:grid; grid-template-columns:1fr auto; gap:2rem; align-items:start; margin-bottom:2rem;">
            <div style="display:flex; align-items:center; gap:1.25rem;">
                <div class="platform-logo" style="width:60px; height:60px; font-size:1.5rem; flex-shrink:0;">{{ $pData['icon'] }}</div>
                <div>
                    <h2 style="font-size:1.4rem; font-weight:800; color:var(--text); margin:0 0 0.35rem;">{{ $pName }}</h2>
                    <p style="color:var(--text-muted); font-size:0.88rem; line-height:1.6; max-width:560px; margin:0;">
                        {{ $pData['desc'] }}
                    </p>
                </div>
            </div>
            <div style="flex-shrink:0;">
                <a href="{{ $browseUrl }}" class="btn btn--primary btn--sm" style="white-space:nowrap;">
                    Browse All {{ $pData['short'] }} Games →
                </a>
            </div>
        </div>

        <!-- Highlights bullets -->
        @if(!empty($pData['highlights']))
        <div style="display:flex; flex-wrap:wrap; gap:0.5rem; margin-bottom:2rem;">
            @foreach($pData['highlights'] as $point)
            <span style="display:inline-flex; align-items:center; gap:0.4rem; font-size:0.8rem; color:var(--text-muted); background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:50px; padding:0.3rem 0.75rem;">
                <span style="color:var(--accent); font-size:0.7rem;">✓</span> {{ $point }}
            </span>
            @endforeach
        </div>
        @endif

        <!-- Game cards -->
        @if(!empty($games))
        <div class="games-grid games-grid--large fade-up">
            @foreach($games as $game)
            <x-game-card :game="$game" />
            @endforeach
        </div>
        <div style="margin-top:1.5rem; text-align:center;">
            <a href="{{ $browseUrl }}" class="btn btn--outline btn--sm">
                See all {{ $pName }} games →
            </a>
        </div>
        @else
        <p style="color:var(--text-muted); font-size:0.9rem;">No priced games found for this platform yet.</p>
        @endif

    </div>
</section>
@endforeach

<!-- ===== BOTTOM CTA ===== -->
<section class="section" style="background:rgba(255,255,255,0.02); border-top:1px solid var(--border);">
    <div class="container" style="text-align:center;">
        <h2 style="font-size:1.25rem; font-weight:700; margin-bottom:0.75rem;">Not Sure Where to Start?</h2>
        <p style="color:var(--text-muted); font-size:0.95rem; max-width:480px; margin:0 auto 1.5rem; line-height:1.7;">
            Search by title across all platforms to see what your games are worth and build your cash basket in minutes.
        </p>
        <a href="{{ route('search') }}" class="btn btn--primary">💰 Search All Games</a>
    </div>
</section>

@endsection
