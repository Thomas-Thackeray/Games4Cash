@extends('layouts.app')
@section('title', 'No Price Review')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">No Price Review</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.dashboard') }}" style="color:var(--accent);">← Dashboard</a></p>
        </div>
    </div>

    @if(session('flash_success'))
    <div class="flash flash--success" style="margin-bottom:1rem;">{{ session('flash_success') }}</div>
    @endif

    <div class="settings-card settings-card--wide" style="margin-bottom:1.5rem; border-left:3px solid var(--accent-2);">
        <p class="settings-hint">
            These games have no price from CheapShark or Steam and are currently <strong>hidden from public listings</strong>.
            Set a manual price override for each platform to make the game visible, or dismiss the entry to permanently skip it.
        </p>
    </div>

    @if($reviews->isEmpty())
    <div class="settings-card settings-card--wide" style="text-align:center; padding:2rem;">
        <p style="color:var(--text-muted);">No games awaiting review — everything is priced.</p>
    </div>
    @else

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Game</th>
                    <th>Platforms Needing Price</th>
                    <th>Queued</th>
                    <th style="width:200px;">Set Price Override</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reviews as $review)
                @php
                    $gp          = $gamePrices[$review->igdb_game_id] ?? null;
                    $title       = $gp?->game_title ?? ($gp?->slug ? ucwords(str_replace('-', ' ', $gp->slug)) : 'Game #' . $review->igdb_game_id);
                    $gameUrl     = $gp?->slug ? route('game.show', ['slug' => $gp->slug]) : null;
                    $platformIds = array_filter(array_map('intval', explode(',', $review->platform_ids_csv)));
                @endphp
                <tr>
                    <td>
                        @if($gameUrl)
                        <a href="{{ $gameUrl }}" style="color:var(--accent); text-decoration:none;" target="_blank">{{ $title }}</a>
                        @else
                        <span style="color:var(--text);">{{ $title }}</span>
                        @endif
                        @if($gp?->steam_app_id)
                        <br><a href="https://store.steampowered.com/app/{{ $gp->steam_app_id }}" target="_blank" style="font-size:0.78rem; color:var(--text-muted);">Steam ↗</a>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex; flex-wrap:wrap; gap:0.35rem;">
                            @foreach($platformIds as $pid)
                            <span style="background:rgba(255,255,255,0.06); border:1px solid var(--border); border-radius:4px; padding:0.15rem 0.45rem; font-size:0.8rem;">
                                {{ $platforms[$pid] ?? 'Platform ' . $pid }}
                            </span>
                            @endforeach
                        </div>
                    </td>
                    <td style="color:var(--text-muted); font-size:0.82rem; white-space:nowrap;">
                        {{ $review->created_at ? \Carbon\Carbon::parse($review->created_at)->diffForHumans() : '—' }}
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.no-price-review.set-price', $review->igdb_game_id) }}"
                              style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
                            @csrf
                            <select name="platform_id" class="form-input" style="flex:1; min-width:110px; font-size:0.82rem; padding:0.3rem 0.5rem;">
                                @foreach($platformIds as $pid)
                                <option value="{{ $pid }}">{{ $platforms[$pid] ?? 'Platform ' . $pid }}</option>
                                @endforeach
                            </select>
                            <div style="display:flex; align-items:center; gap:0.25rem;">
                                <span style="color:var(--text-muted); font-size:0.9rem;">£</span>
                                <input type="number" name="price" min="0.01" max="9999.99" step="0.01"
                                       class="form-input" style="width:72px; font-size:0.82rem; padding:0.3rem 0.4rem;"
                                       placeholder="0.00">
                            </div>
                            <button type="submit" class="btn btn--primary btn--sm">Save</button>
                        </form>

                        <form method="POST" action="{{ route('admin.no-price-review.dismiss', $review->igdb_game_id) }}"
                              style="margin-top:0.4rem;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn--outline btn--sm" style="font-size:0.78rem; opacity:0.6;"
                                    data-confirm="Dismiss all review entries for this game? It will remain hidden from listings.">
                                Dismiss all
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top:1rem;">
        {{ $reviews->links() }}
    </div>
    @endif

</div>
@endsection
