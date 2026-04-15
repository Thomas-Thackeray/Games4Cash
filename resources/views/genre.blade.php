@extends('layouts.app')

@section('title', $genreName . ' Games')
@section('meta_description', 'Browse ' . $genreName . ' games across all platforms, check what they\'re worth, and sell your collection for cash.')
@section('canonical', route('genre.show', ['id' => $id, 'name' => $genreName]))

@section('content')

<!-- ===== GENRE HERO ===== -->
<div class="platform-hero">
    <div class="container">
        <div class="platform-hero__inner">
            <div class="platform-logo" style="font-size:1.8rem;">🎯</div>
            <div>
                <p style="font-size:0.8rem; letter-spacing:0.15em; text-transform:uppercase; color:var(--accent); font-weight:700; margin-bottom:0.5rem;">
                    Genre
                </p>
                <h1 class="section-title">{{ e($genreName) }}</h1>
            </div>
        </div>

        <!-- Genre nav chips -->
        <div style="display:flex; gap:0.75rem; margin-top:2rem; flex-wrap:wrap;">
            @foreach(config('igdb.genres') as $gName => $gId)
            <a href="{{ route('genre.show', ['id' => $gId, 'name' => $gName]) }}"
               class="chip {{ $gId === $id ? 'active' : '' }}">
                {{ $gName }}
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
        </div>
        @else
        <div class="section-header fade-up">
            <h2 class="section-title">{{ e($genreName) }} Games</h2>
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
            <a href="{{ route('genre.show', ['id' => $id, 'name' => $genreName, 'page' => $page - 1]) }}" class="page-btn">← Prev</a>
            @endif
            @for($i = max(1, $page - 2); $i <= min(20, $page + 4); $i++)
            <a href="{{ route('genre.show', ['id' => $id, 'name' => $genreName, 'page' => $i]) }}"
               class="page-btn {{ $i === $page ? 'active' : '' }}">
                {{ $i }}
            </a>
            @endfor
            @if(count($games) === $limit)
            <a href="{{ route('genre.show', ['id' => $id, 'name' => $genreName, 'page' => $page + 1]) }}" class="page-btn">Next →</a>
            @endif
        </div>
        @endif
    </div>
</section>

@endsection
