@extends('layouts.app')
@section('title', 'Price Requests')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Price Requests</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.dashboard') }}" style="color:var(--accent);">← Dashboard</a></p>
        </div>
    </div>

    @if(session('flash_success'))
    <div class="flash flash--success" style="margin-bottom:1rem;">{{ session('flash_success') }}</div>
    @endif

    @if(!empty($migrationPending))
    <div class="settings-card settings-card--wide" style="border-left:3px solid var(--accent-2);">
        <p style="color:var(--text);">The <code>price_requests</code> table does not exist yet.</p>
        <p class="settings-hint" style="margin-top:0.5rem;">Run <code>php artisan migrate</code> on the server to create it, then revisit this page.</p>
    </div>
    @else

    <div class="settings-card settings-card--wide" style="margin-bottom:1.5rem; border-left:3px solid var(--accent-2);">
        <p class="settings-hint">
            Users have requested prices for these games. Set a price override for the requested platform to fulfil the request
            — the price will automatically appear in their cash basket.
        </p>
    </div>

    @if(empty($groups))
    <div class="settings-card settings-card--wide" style="text-align:center; padding:2rem;">
        <p style="color:var(--text-muted);">No pending price requests.</p>
    </div>
    @else

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Game</th>
                    <th>Platforms Requested</th>
                    <th>Requests</th>
                    <th>Since</th>
                    <th style="width:260px;">Set Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groups as $group)
                @php
                    $gameUrl = $group['slug'] ? route('game.show', ['slug' => $group['slug']]) : null;
                @endphp
                <tr>
                    <td>
                        @if($gameUrl)
                        <a href="{{ $gameUrl }}" style="color:var(--accent); text-decoration:none;" target="_blank">{{ $group['game_title'] }}</a>
                        @else
                        <span style="color:var(--text);">{{ $group['game_title'] }}</span>
                        @endif
                        <br><span style="font-size:0.78rem; color:var(--text-muted);">IGDB #{{ $group['igdb_game_id'] }}</span>
                    </td>
                    <td>
                        @if(!empty($group['platforms']))
                        <div style="display:flex; flex-wrap:wrap; gap:0.35rem;">
                            @foreach($group['platforms'] as $pid)
                            <span style="background:rgba(255,255,255,0.06); border:1px solid var(--border); border-radius:4px; padding:0.15rem 0.45rem; font-size:0.8rem;">
                                {{ $allPlatforms[$pid] ?? 'Platform ' . $pid }}
                            </span>
                            @endforeach
                        </div>
                        @else
                        <span style="color:var(--text-muted); font-size:0.82rem;">Any / unspecified</span>
                        @endif
                    </td>
                    <td style="color:var(--text-muted); font-size:0.9rem;">
                        {{ $group['user_count'] }}
                    </td>
                    <td style="color:var(--text-muted); font-size:0.82rem; white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($group['created_at'])->diffForHumans() }}
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.price-requests.fulfill', $group['igdb_game_id']) }}"
                              style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
                            @csrf
                            <select name="platform_id" class="form-input" style="flex:1; min-width:120px; font-size:0.82rem; padding:0.3rem 0.5rem;">
                                @foreach($allPlatforms as $pid => $pName)
                                <option value="{{ $pid }}" {{ !empty($group['platforms']) && $group['platforms'][0] == $pid ? 'selected' : '' }}>
                                    {{ $pName }}
                                </option>
                                @endforeach
                            </select>
                            <div style="display:flex; align-items:center; gap:0.25rem;">
                                <span style="color:var(--text-muted); font-size:0.9rem;">£</span>
                                <input type="number" name="price" min="0.01" max="999.99" step="0.01"
                                       class="form-input" style="width:72px; font-size:0.82rem; padding:0.3rem 0.4rem;"
                                       placeholder="0.00">
                            </div>
                            <button type="submit" class="btn btn--primary btn--sm">Set Price</button>
                        </form>

                        <form method="POST" action="{{ route('admin.price-requests.dismiss', $group['igdb_game_id']) }}"
                              style="margin-top:0.4rem;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn--outline btn--sm" style="font-size:0.78rem; opacity:0.6;"
                                    data-confirm="Dismiss all price requests for this game?">
                                Dismiss
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @endif

    @endif {{-- migrationPending --}}

</div>
@endsection
