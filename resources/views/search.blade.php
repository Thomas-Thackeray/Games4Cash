@extends('layouts.app')

@php $activePage = 'search'; @endphp

@section('title', $query ? 'Search: ' . $query : 'Browse Games')
@if($query)
@section('meta_description', 'Search results for "' . $query . '" — browse games, check cash values, and get a free collection quote.')
@else
@section('meta_description', 'Browse thousands of games across every platform and genre. Check what your games are worth and get cash today.')
@endif
@section('canonical', route('search', array_filter(['q' => $query, 'sort' => ($sort !== 'trending' ? $sort : null)])))

@section('content')

<!-- ===== SEARCH HERO ===== -->
<div class="search-hero">
    <div class="container">
        <h1 class="section-title">
            @if($query)
                Results for "{{ e($query) }}"
            @else
                Browse All Games
            @endif
        </h1>
        <form class="search-bar" action="{{ route('search') }}" method="GET">
            <input
                type="search"
                name="q"
                value="{{ e($query) }}"
                placeholder="Search for any game…"
                autocomplete="off"
                autofocus>
            <button type="submit">⌕ Search</button>
        </form>
    </div>
</div>

<!-- ===== FILTER BAR ===== -->
@if(!$query)
<div class="container">
    <div class="filter-bar">
        <span class="filter-label">Sort by:</span>
        <div class="filter-chips">
            @php
                $filters = [
                    'trending'  => '🔥 Trending',
                    'top_rated' => '⭐ Top Rated',
                    'recent'    => '🆕 New Releases',
                    'upcoming'  => '⏳ Upcoming',
                ];
            @endphp
            @foreach($filters as $key => $label)
            <a href="{{ route('search', ['sort' => $key]) }}"
               class="chip {{ $sort === $key ? 'active' : '' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        <span class="filter-label" style="margin-left:auto">Platform:</span>
        <div class="filter-chips">
            @foreach(config('igdb.platforms') as $pName => $pData)
            <a href="{{ route('platform.show', ['id' => $pData['id'], 'name' => $pName]) }}" class="chip">
                {{ $pData['short'] }}
            </a>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- ===== RESULTS ===== -->
<section class="section--tight">
    <div class="container">
        @if($error)
        <div class="error-banner">⚠️ {{ e($error) }}</div>
        @elseif(empty($games))
        <div class="empty-state">
            <div class="icon">🔍</div>
            <h3>No games found</h3>
            <p>Try a different search term or browse by platform.</p>
            <a href="{{ route('search') }}" class="btn btn--primary" style="margin-top:1.5rem">Browse All</a>
        </div>
        @else
        <div class="games-grid games-grid--large" style="margin-bottom: 2rem;">
            @foreach($games as $game)
            <x-game-card :game="$game" />
            @endforeach
        </div>

        <!-- Pagination -->
        @if(!$query)
        <div class="pagination">
            @if($page > 1)
            <a href="{{ route('search', ['sort' => $sort, 'page' => $page - 1]) }}" class="page-btn">← Prev</a>
            @endif
            @for($i = max(1, $page - 2); $i <= min(8, $page + 4); $i++)
            <a href="{{ route('search', ['sort' => $sort, 'page' => $i]) }}"
               class="page-btn {{ $i === $page ? 'active' : '' }}">
                {{ $i }}
            </a>
            @endfor
            <a href="{{ route('search', ['sort' => $sort, 'page' => $page + 1]) }}" class="page-btn">Next →</a>
        </div>
        @elseif(count($games) === $limit)
        <div class="pagination">
            @if($page > 1)
            <a href="{{ route('search', ['q' => $query, 'page' => $page - 1]) }}" class="page-btn">← Prev</a>
            @endif
            <span class="page-btn active">{{ $page }}</span>
            <a href="{{ route('search', ['q' => $query, 'page' => $page + 1]) }}" class="page-btn">Next →</a>
        </div>
        @endif
        @endif
    </div>
</section>

@endsection
