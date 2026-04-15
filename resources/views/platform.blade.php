@extends('layouts.app')

@section('title', $platformName . ' Games')
@section('meta_description', 'Browse ' . $platformName . ' games, check what they\'re worth, and get cash for your collection. Free pickup, fast quotes.')
@section('canonical', route('platform.show', ['id' => $id, 'name' => $platformName]))

@section('content')

<!-- ===== PLATFORM HERO ===== -->
<div class="platform-hero">
    <div class="container">
        <div class="platform-hero__inner">
            <div class="platform-logo">{{ $platformIcon }}</div>
            <div>
                <p style="font-size:0.8rem; letter-spacing:0.15em; text-transform:uppercase; color:var(--accent); font-weight:700; margin-bottom:0.5rem;">
                    Platform
                </p>
                <h1 class="section-title">{{ e($platformName) }}</h1>
                @if(!empty($platform['summary']))
                <p style="color:var(--text-muted); max-width:600px; margin-top:0.75rem; font-size:0.95rem; line-height:1.7;">
                    {{ e(truncate_text($platform['summary'], 200)) }}
                </p>
                @endif
            </div>
        </div>

        <!-- Platform nav chips -->
        <div style="display:flex; gap:0.75rem; margin-top:2rem; flex-wrap:wrap;">
            @foreach(config('igdb.platforms') as $pName => $pData)
            <a href="{{ route('platform.show', ['id' => $pData['id'], 'name' => $pName]) }}"
               class="chip {{ $pData['id'] === $id ? 'active' : '' }}">
                {{ $pData['icon'] }} {{ $pName }}
            </a>
            @endforeach
        </div>
    </div>
</div>

<!-- ===== GAMES GRID ===== -->
<section class="section">
    <div class="container">
        @if($error)
        <div class="error-banner">⚠️ {{ e($error) }}</div>
        @elseif(empty($games))
        <div class="empty-state">
            <div class="icon">🎮</div>
            <h3>No games found</h3>
            <p>No rated games found for this platform yet.</p>
        </div>
        @else
        <div class="section-header fade-up">
            <h2 class="section-title">{{ e($platformName) }} Games</h2>
            <span style="color:var(--text-muted); font-size:0.875rem;">Page {{ $page }}</span>
        </div>

        <div class="games-grid games-grid--large fade-up">
            @foreach($games as $game)
            <x-game-card :game="$game" />
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pagination">
            @if($page > 1)
            <a href="{{ route('platform.show', ['id' => $id, 'name' => $platformName, 'page' => $page - 1]) }}" class="page-btn">← Prev</a>
            @endif
            @for($i = max(1, $page - 2); $i <= min(20, $page + 4); $i++)
            <a href="{{ route('platform.show', ['id' => $id, 'name' => $platformName, 'page' => $i]) }}"
               class="page-btn {{ $i === $page ? 'active' : '' }}">
                {{ $i }}
            </a>
            @endfor
            @if(count($games) === $limit)
            <a href="{{ route('platform.show', ['id' => $id, 'name' => $platformName, 'page' => $page + 1]) }}" class="page-btn">Next →</a>
            @endif
        </div>
        @endif
    </div>
</section>

@endsection
