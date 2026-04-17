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
            Set a manual price override to make the game visible, or dismiss to skip it.
        </p>
    </div>

    @if($rows->isEmpty())
    <div class="settings-card settings-card--wide" style="text-align:center; padding:2rem;">
        <p style="color:var(--text-muted);">No games awaiting review — everything is priced.</p>
    </div>
    @else

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Game</th>
                    <th>Known Platforms</th>
                    <th style="width:260px;">Set Price Override</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $gp)
                @php
                    $title      = $gp->game_title ?? ($gp->slug ? ucwords(str_replace('-', ' ', $gp->slug)) : 'Game #' . $gp->igdb_game_id);
                    $gameUrl    = $gp->slug ? route('game.show', ['slug' => $gp->slug]) : null;
                    $platformIds = json_decode($gp->platform_ids ?? '[]', true);
                    // Prefer no_price_reviews platforms if available, fall back to game_prices platform_ids
                    $displayPids = $nprPlatforms[$gp->igdb_game_id] ?? $platformIds;
                    $displayPids = array_values(array_unique(array_filter($displayPids)));
                @endphp
                <tr>
                    <td>
                        @if($gameUrl)
                        <a href="{{ $gameUrl }}" style="color:var(--accent); text-decoration:none;" target="_blank">{{ $title }}</a>
                        @else
                        <span style="color:var(--text);">{{ $title }}</span>
                        @endif
                        @if($gp->steam_app_id)
                        <br><a href="https://store.steampowered.com/app/{{ $gp->steam_app_id }}" target="_blank" style="font-size:0.78rem; color:var(--text-muted);">Steam ↗</a>
                        @endif
                    </td>
                    <td>
                        @if(!empty($displayPids))
                        <div style="display:flex; flex-wrap:wrap; gap:0.35rem;">
                            @foreach($displayPids as $pid)
                            <span style="background:rgba(255,255,255,0.06); border:1px solid var(--border); border-radius:4px; padding:0.15rem 0.45rem; font-size:0.8rem;">
                                {{ $platforms[$pid] ?? 'Platform ' . $pid }}
                            </span>
                            @endforeach
                        </div>
                        @else
                        <span style="color:var(--text-muted); font-size:0.82rem;">Unknown</span>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.no-price-review.set-price', $gp->igdb_game_id) }}"
                              style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
                            @csrf
                            <select name="platform_id" class="form-input" style="flex:1; min-width:120px; font-size:0.82rem; padding:0.3rem 0.5rem;">
                                @foreach($platforms as $pid => $pName)
                                <option value="{{ $pid }}" {{ !empty($displayPids) && $displayPids[0] == $pid ? 'selected' : '' }}>
                                    {{ $pName }}
                                </option>
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

                        <form method="POST" action="{{ route('admin.no-price-review.dismiss', $gp->igdb_game_id) }}"
                              style="margin-top:0.4rem;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn--outline btn--sm" style="font-size:0.78rem; opacity:0.6;"
                                    data-confirm="Dismiss this game? It will remain hidden from listings.">
                                Dismiss
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top:1rem;">
        {{ $rows->links() }}
    </div>

    @endif

</div>
@endsection
