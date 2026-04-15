@extends('layouts.app')

@php $activePage = 'search'; @endphp

@section('title', $query ? 'Search: ' . $query : 'Browse Games')
@if($query)
@section('meta_description', 'Search results for "' . $query . '" — browse games, check cash values, and get a free collection quote.')
@else
@section('meta_description', 'Browse thousands of games across every platform and genre. Check what your games are worth and get cash today.')
@endif
@section('canonical', route('search', array_filter(['q' => $query, 'genre' => $genre, 'franchise' => $franchise, 'min_price' => $minPrice, 'max_price' => $maxPrice])))

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
    <form class="filter-bar" method="GET" action="{{ route('search') }}" id="filter-form">
        <select name="genre" class="filter-select" onchange="this.form.submit()">
            <option value="">All Genres</option>
            @foreach(config('igdb.genres') as $name => $id)
            <option value="{{ $id }}" {{ $genre == $id ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>

        <select name="franchise" class="filter-select" onchange="this.form.submit()">
            <option value="">All Franchises</option>
            @foreach(config('igdb.franchises') as $name)
            <option value="{{ $name }}" {{ $franchise === $name ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>

        <div class="filter-price">
            <span class="filter-price__label">
                Price: <strong id="price-display">
                    £{{ $minPrice ?: '0' }} – £{{ ($maxPrice && $maxPrice < 60) ? $maxPrice : '60+' }}
                </strong>
            </span>
            <div class="dual-range">
                <input type="range" name="min_price" id="range-min" min="0" max="60" step="1" value="{{ $minPrice ?: 0 }}">
                <input type="range" name="max_price" id="range-max" min="0" max="60" step="1" value="{{ $maxPrice ?: 60 }}">
            </div>
            <button type="submit" class="btn btn--sm btn--primary">Go</button>
        </div>

        @if($genre || $franchise || $minPrice || $maxPrice)
        <a href="{{ route('search') }}" class="chip chip--clear">✕ Clear</a>
        @endif
    </form>
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
        @php
            $pageParams = array_filter(['genre' => $genre, 'franchise' => $franchise, 'min_price' => $minPrice, 'max_price' => $maxPrice]);
        @endphp
        <div class="pagination">
            @if($page > 1)
            <a href="{{ route('search', array_merge($pageParams, ['page' => $page - 1])) }}" class="page-btn">← Prev</a>
            @endif
            @for($i = max(1, $page - 2); $i <= min(8, $page + 4); $i++)
            <a href="{{ route('search', array_merge($pageParams, ['page' => $i])) }}"
               class="page-btn {{ $i === $page ? 'active' : '' }}">
                {{ $i }}
            </a>
            @endfor
            <a href="{{ route('search', array_merge($pageParams, ['page' => $page + 1])) }}" class="page-btn">Next →</a>
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
