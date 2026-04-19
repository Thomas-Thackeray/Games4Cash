@extends('layouts.app')

@php
    $pageTitle   = 'Sell ' . $platformName . ' Games for Cash';
    $metaDesc    = $platformConfig['meta'] ?? 'Sell your ' . $platformName . ' games for cash. Get an instant trade-in price, free UK collection, and fast payment.';
    $icon        = $platformConfig['icon'] ?? '🎮';
    $seoHeading  = $platformConfig['seo']['heading'] ?? $pageTitle;
    $seoBody     = $platformConfig['seo']['body'] ?? '';
    $seoBody2    = $platformConfig['seo']['body2'] ?? '';
    $highlights  = $platformConfig['highlights'] ?? [];
@endphp

@section('title', $pageTitle)
@section('meta_description', $metaDesc)
@section('canonical', route('sell.platform', $slug))

@section('content')

<!-- ===== HERO ===== -->
<div class="platform-hero">
    <div class="container">

        <div style="display:grid; grid-template-columns:1fr auto; gap:3rem; align-items:start;">
            <div>
                <div class="platform-hero__inner" style="margin-bottom:1.25rem;">
                    <div class="platform-logo">{{ $icon }}</div>
                    <div>
                        <p style="font-size:0.75rem; letter-spacing:0.15em; text-transform:uppercase; color:var(--accent); font-weight:700; margin-bottom:0.4rem;">
                            Sell Your Games
                        </p>
                        <h1 class="section-title" style="margin:0;">{{ $platformName }}</h1>
                    </div>
                </div>

                @if(!empty($platformConfig['desc']))
                <p style="color:var(--text-muted); max-width:600px; font-size:0.95rem; line-height:1.75;">
                    {{ $platformConfig['desc'] }}
                </p>
                @else
                <p style="color:var(--text-muted); max-width:600px; font-size:0.95rem; line-height:1.75;">
                    Browse {{ $platformName }} games below, check your cash trade-in price, and add them to your basket. We offer free UK-wide collection and fast payment.
                </p>
                @endif

                <div style="display:flex; gap:0.75rem; margin-top:1.5rem; flex-wrap:wrap;">
                    <a href="{{ route('search') }}" class="btn btn--primary">Browse All Games</a>
                    <a href="{{ route('cash-basket.index') }}" class="btn btn--outline">View Basket</a>
                </div>
            </div>

            @if(!empty($highlights))
            <div style="min-width:240px; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:10px; padding:1.25rem 1.5rem;">
                <p style="font-size:0.72rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:var(--accent); margin-bottom:0.85rem;">Why sell with us</p>
                <ul style="list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:0.6rem;">
                    @foreach($highlights as $point)
                    <li style="display:flex; gap:0.6rem; align-items:flex-start; font-size:0.83rem; color:var(--text-muted); line-height:1.5;">
                        <span style="color:var(--accent); flex-shrink:0; margin-top:0.1rem;">✓</span>
                        {{ $point }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        {{-- Platform nav chips --}}
        <div style="display:flex; gap:0.65rem; margin-top:2rem; flex-wrap:wrap;">
            @foreach(config('igdb.platforms') as $pName => $pData)
            @php $pSlug = \Illuminate\Support\Str::slug($pName); @endphp
            <a href="{{ route('sell.platform', $pSlug) }}"
               class="chip {{ $pData['id'] === $platformId ? 'active' : '' }}">
                {{ $pData['icon'] }} {{ $pName }}
            </a>
            @endforeach
        </div>

    </div>
</div>

<!-- ===== GAMES GRID ===== -->
<section class="section">
    <div class="container">
        @if(empty($games) && $customGames->isEmpty())
        <div class="empty-state">
            <div class="icon">🎮</div>
            <h3>No games found</h3>
            <p>No priced {{ $platformName }} games found yet. <a href="{{ route('search') }}" style="color:var(--accent);">Browse all games →</a></p>
        </div>
        @else
        <div class="section-header fade-up">
            <h2 class="section-title">{{ $platformName }} Games</h2>
        </div>

        <div class="games-grid games-grid--large fade-up">
            @foreach($customGames as $cg)
            <x-custom-game-card :game="$cg" />
            @endforeach
            @foreach($games as $game)
            <x-game-card :game="$game" />
            @endforeach
        </div>
        @endif
    </div>
</section>

{{-- ===== SEO COPY ===== --}}
@if(!empty($seoBody))
<section class="section" style="padding-top:0; border-top:1px solid var(--border);">
    <div class="container">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:3.5rem; align-items:start; padding-top:2.5rem;">

            <div>
                <h2 style="font-size:1.2rem; font-weight:700; color:var(--text); margin-bottom:1rem; line-height:1.4;">
                    {{ $seoHeading }}
                </h2>
                <p style="color:var(--text-muted); font-size:0.95rem; line-height:1.8; margin-bottom:1rem;">
                    {{ $seoBody }}
                </p>
                @if($seoBody2)
                <p style="color:var(--text-muted); font-size:0.95rem; line-height:1.8;">
                    {{ $seoBody2 }}
                </p>
                @endif
            </div>

            <div>
                <h2 style="font-size:1.2rem; font-weight:700; color:var(--text); margin-bottom:1rem; line-height:1.4;">
                    How It Works
                </h2>
                <div style="display:flex; flex-direction:column; gap:1rem;">
                    @foreach([
                        ['1', 'Search for your games', 'Find your ' . $platformName . ' titles above and check the cash price.'],
                        ['2', 'Add to your basket', 'Select your games and the condition they\'re in. No hidden deductions.'],
                        ['3', 'Book a collection', 'Submit your basket and we\'ll arrange a free doorstep collection.'],
                        ['4', 'Get paid', 'Once we\'ve checked your games, we\'ll send payment directly to you.'],
                    ] as [$num, $title, $desc])
                    <div style="display:flex; gap:1rem; align-items:flex-start;">
                        <div style="width:28px; height:28px; border-radius:50%; background:var(--accent); color:#fff; display:flex; align-items:center; justify-content:center; font-size:0.78rem; font-weight:700; flex-shrink:0; margin-top:2px;">{{ $num }}</div>
                        <div>
                            <p style="font-weight:600; margin:0 0 0.2rem; font-size:0.9rem;">{{ $title }}</p>
                            <p style="color:var(--text-muted); font-size:0.83rem; margin:0; line-height:1.5;">{{ $desc }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
@endif

@endsection
