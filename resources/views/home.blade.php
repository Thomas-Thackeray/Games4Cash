@extends('layouts.app')

@php $activePage = 'home'; @endphp
@section('seo_title', config('app.name') . ' | Get Cash for Your Old Games')
@section('meta_description', 'Turn your old games into cash. Browse thousands of titles across every platform, see what they\'re worth, and get a free collection quote. Fast, easy, free pickup.')
@section('canonical', route('home'))

@section('content')

<!-- ===== HERO ===== -->
<section class="hero">
    <div class="hero__bg"></div>
    <div class="hero__grid-lines"></div>
    <div class="container">
        <div class="hero__content">
            <p class="hero__eyebrow">Turn Your Games Into Cash</p>
            <h1 class="hero__title">
                Get <span>Cash</span><br>For Your<br>Old Games
            </h1>
            <p class="hero__desc">
                Browse thousands of titles, check what your games are worth, and sell them fast.
                Free collection from your door — we'll be in touch within 24 hours.
            </p>
            <div class="hero__actions">
                <a href="{{ route('search') }}" class="btn btn--primary">💰 See What Your Games Are Worth</a>
                <a href="{{ route('search', ['sort' => 'top_rated']) }}" class="btn btn--outline">⭐ Top Rated</a>
            </div>
        </div>
    </div>
</section>

<!-- ===== PLATFORM STRIP ===== -->
<div class="container">
    <div class="platform-strip">
        @foreach(config('igdb.platforms') as $pName => $pData)
        <a href="{{ route('platform.show', ['id' => $pData['id'], 'name' => $pName]) }}" class="platform-pill">
            <span class="icon">{{ $pData['icon'] }}</span>
            {{ $pName }}
        </a>
        @endforeach
        @foreach(config('igdb.genres') as $gName => $gId)
        <a href="{{ route('genre.show', ['id' => $gId, 'name' => $gName]) }}" class="platform-pill">
            {{ $gName }}
        </a>
        @endforeach
    </div>
</div>





@endsection
