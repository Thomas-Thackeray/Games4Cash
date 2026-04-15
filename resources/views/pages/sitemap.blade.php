@extends('layouts.app')
@section('title', 'Site Map')
@section('content')
<div class="container" style="max-width:800px; padding:4rem 1rem;">
    <h1 style="font-size:2.5rem; margin-bottom:2rem;">Site Map</h1>

    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:2rem;">

        <div>
            <h3 style="font-size:1.1rem; margin-bottom:1rem; color:var(--accent);">Main</h3>
            <ul style="list-style:none; padding:0; display:flex; flex-direction:column; gap:0.5rem;">
                <li><a href="{{ route('home') }}" style="color:var(--text-muted);">Home</a></li>
                <li><a href="{{ route('search') }}" style="color:var(--text-muted);">Browse All Games</a></li>
            </ul>
        </div>

        <div>
            <h3 style="font-size:1.1rem; margin-bottom:1rem; color:var(--accent);">Platforms</h3>
            <ul style="list-style:none; padding:0; display:flex; flex-direction:column; gap:0.5rem;">
                @foreach(config('igdb.platforms') as $pName => $pData)
                <li><a href="{{ route('platform.show', ['id' => $pData['id'], 'name' => $pName]) }}" style="color:var(--text-muted);">{{ $pName }}</a></li>
                @endforeach
            </ul>
        </div>

        <div>
            <h3 style="font-size:1.1rem; margin-bottom:1rem; color:var(--accent);">Genres</h3>
            <ul style="list-style:none; padding:0; display:flex; flex-direction:column; gap:0.5rem;">
                @foreach(config('igdb.genres') as $gName => $gId)
                <li><a href="{{ route('genre.show', ['id' => $gId, 'name' => $gName]) }}" style="color:var(--text-muted);">{{ $gName }}</a></li>
                @endforeach
            </ul>
        </div>

        <div>
            <h3 style="font-size:1.1rem; margin-bottom:1rem; color:var(--accent);">Company</h3>
            <ul style="list-style:none; padding:0; display:flex; flex-direction:column; gap:0.5rem;">
                <li><a href="{{ route('about') }}" style="color:var(--text-muted);">About Us</a></li>
                <li><a href="{{ route('contact') }}" style="color:var(--text-muted);">Contact Us</a></li>
                <li><a href="{{ route('faq') }}" style="color:var(--text-muted);">FAQ</a></li>
                <li><a href="{{ route('terms') }}" style="color:var(--text-muted);">Terms &amp; Conditions</a></li>
                <li><a href="{{ route('privacy') }}" style="color:var(--text-muted);">Privacy Policy</a></li>
            </ul>
        </div>

    </div>
</div>
@endsection
