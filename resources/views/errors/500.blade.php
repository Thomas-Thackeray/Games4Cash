@extends('layouts.app')
@section('title', 'Server Error')
@section('content')
<div class="container" style="padding: 6rem 1rem; text-align:center; max-width:560px;">
    <div style="font-size:4rem; margin-bottom:1rem;">⚠️</div>
    <h1 style="font-size:2.5rem; margin-bottom:0.75rem;">500 — Server Error</h1>
    <p style="color:var(--text-muted); line-height:1.8; margin-bottom:2rem;">
        Something went wrong on our end. Please try again in a moment.
        If the problem persists, feel free to <a href="{{ route('contact') }}" style="color:var(--accent);">contact us</a>.
    </p>
    <a href="{{ route('home') }}" class="btn btn--primary">Go Home</a>
</div>
@endsection
