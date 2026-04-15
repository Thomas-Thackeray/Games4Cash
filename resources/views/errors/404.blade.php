@extends('layouts.app')
@section('title', 'Page Not Found')
@section('content')
<div class="container" style="padding: 6rem 1rem; text-align:center; max-width:560px;">
    <div style="font-size:4rem; margin-bottom:1rem;">🕹️</div>
    <h1 style="font-size:2.5rem; margin-bottom:0.75rem;">404 — Page Not Found</h1>
    <p style="color:var(--text-muted); line-height:1.8; margin-bottom:2rem;">
        The page you're looking for doesn't exist or has been moved.
    </p>
    <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
        <a href="{{ route('home') }}" class="btn btn--primary">Go Home</a>
        <a href="{{ route('search') }}" class="btn btn--outline">Browse Games</a>
    </div>
</div>
@endsection
