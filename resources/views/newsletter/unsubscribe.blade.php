@extends('layouts.app')
@section('title', 'Unsubscribe from Newsletter')

@section('content')
<div class="container" style="padding: 5rem 0; max-width: 500px; text-align: center;">

    <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
    <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.75rem;">Unsubscribe from Newsletter</h1>
    <p style="color: var(--text-muted); margin-bottom: 2rem; line-height: 1.6;">
        Are you sure you want to unsubscribe <strong>{{ $subscriber->email }}</strong> from
        {{ config('app.name') }} newsletters? You won't receive any more emails from us.
    </p>

    <form method="POST" action="{{ route('newsletter.unsubscribe.confirm', $token) }}" style="display:inline-block;">
        @csrf
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('home') }}" class="btn btn--outline">Keep Me Subscribed</a>
            <button type="submit" class="btn btn--danger">Unsubscribe</button>
        </div>
    </form>

</div>
@endsection
