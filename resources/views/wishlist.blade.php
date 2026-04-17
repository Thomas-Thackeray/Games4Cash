@extends('layouts.app')
@section('title', 'My Wishlist')

@section('content')
<div class="container" style="padding: 3rem 0 5rem;">

    <div class="page-header" style="margin-bottom:2rem;">
        <h1 class="section-title" style="font-size:2rem;">My Wishlist</h1>
        <p style="color:var(--text-muted); margin-top:0.4rem;">{{ $items->count() }} {{ $items->count() === 1 ? 'game' : 'games' }}</p>
    </div>

    @if($items->isEmpty())
    <div class="empty-state">
        <div class="icon">♡</div>
        <h3>Your wishlist is empty</h3>
        <p>Browse games and click "Add to Wishlist" to save them here.</p>
        <a href="{{ route('search') }}" class="btn btn--primary" style="margin-top:1.5rem;">Browse Games</a>
    </div>
    @else
    <div class="wishlist-grid">
        @foreach($items as $item)
        <div class="wishlist-card">
            <a href="{{ \App\Models\GamePrice::urlForId($item->igdb_game_id) }}" class="wishlist-card__cover-link">
                @if($item->cover_url)
                <img src="{{ $item->cover_url }}" alt="{{ $item->game_title }}" class="wishlist-card__cover">
                @else
                <div class="wishlist-card__cover wishlist-card__cover--placeholder">🎮</div>
                @endif
            </a>
            <div class="wishlist-card__body">
                <a href="{{ \App\Models\GamePrice::urlForId($item->igdb_game_id) }}" class="wishlist-card__title">
                    {{ $item->game_title }}
                </a>
                <span class="wishlist-card__date">Added {{ $item->created_at->diffForHumans() }}</span>
            </div>
            <form method="POST" action="{{ route('wishlist.destroy', $item->igdb_game_id) }}" class="wishlist-card__remove">
                @csrf
                @method('DELETE')
                <button type="submit" class="wishlist-card__remove-btn" title="Remove from wishlist"
                    data-confirm="Remove &quot;{{ $item->game_title }}&quot; from your wishlist?">
                    ✕
                </button>
            </form>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection
