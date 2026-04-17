@extends('layouts.app')
@section('title', 'Recently Viewed')

@section('content')
<div class="container" style="padding: 3rem 0 5rem;">

    <div class="page-header" style="margin-bottom:2rem;">
        <h1 class="section-title" style="font-size:2rem;">Recently Viewed</h1>
        <p style="color:var(--text-muted); margin-top:0.4rem;">{{ count($games) }} {{ count($games) === 1 ? 'game' : 'games' }}</p>
    </div>

    @if(empty($games))
    <div class="empty-state">
        <div class="icon">🕹️</div>
        <h3>No recently viewed games</h3>
        <p>Games you view will appear here.</p>
        <a href="{{ route('search') }}" class="btn btn--primary" style="margin-top:1.5rem;">Browse Games</a>
    </div>
    @else

    <div class="rv-list">
        @foreach($games as $game)
        @php
            $gameId      = $game['id'];
            $name        = $game['name'] ?? 'Unknown';
            $imgId       = $game['cover']['image_id'] ?? null;
            $imgUrl      = $imgId ? igdb_img($imgId, 'cover_big') : asset('img/placeholder.jpg');
            $allPlatforms   = config('igdb.all_platforms');
            $franchiseNames = array_column($game['franchises'] ?? [], 'name');

            // Platform names from IGDB data
            $platformNames = collect($game['platforms'] ?? [])
                ->pluck('id')
                ->map(fn($pid) => $allPlatforms[$pid] ?? null)
                ->filter()
                ->unique()
                ->values();

            // Pricing & cash dropdown data from DB
            $gamePrice   = \App\Models\GamePrice::where('igdb_game_id', $gameId)->first();
            $pricing     = $gamePrice?->getComputedPrice($franchiseNames, $name);
            if ($pricing && $pricing['is_free']) { $pricing = null; }

            $platformsData = [];
            if ($gamePrice && $pricing) {
                $platformIds = json_decode($gamePrice->platform_ids ?? '[]', true);
                foreach ($platformIds as $pid) {
                    if (!isset($allPlatforms[$pid])) continue;
                    $p = $gamePrice->getComputedPriceForPlatform((int) $pid, $franchiseNames, $name);
                    if ($p && !$p['is_free']) {
                        $platformsData[] = [
                            'id'           => (int) $pid,
                            'name'         => $allPlatforms[$pid],
                            'price'        => $p['display_price'],
                            'steam_app_id' => $gamePrice->steam_app_id,
                            'release_date' => $gamePrice->release_date,
                        ];
                    }
                }
            }

            $inWishlist = in_array($gameId, $wishlistIds);
        @endphp

        <div class="rv-row">
            {{-- Title & platforms --}}
            <div class="rv-row__main">
                <a href="{{ \App\Models\GamePrice::urlForId($gameId) }}" class="rv-row__title">{{ $name }}</a>
                @if($platformNames->isNotEmpty())
                <div class="rv-row__platforms">
                    @foreach($platformNames as $pname)
                    <span class="tag">{{ $pname }}</span>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="rv-row__actions">
                {{-- Wishlist --}}
                @if($inWishlist)
                <form method="POST" action="{{ route('wishlist.destroy', $gameId) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn--outline btn--sm rv-wishlist-btn rv-wishlist-btn--active" title="Remove from wishlist">
                        ♥ Wishlisted
                    </button>
                </form>
                @else
                <form method="POST" action="{{ route('wishlist.store') }}">
                    @csrf
                    <input type="hidden" name="igdb_game_id" value="{{ $gameId }}">
                    <input type="hidden" name="game_title"   value="{{ $name }}">
                    <input type="hidden" name="cover_url"    value="{{ $imgUrl }}">
                    <button type="submit" class="btn btn--outline btn--sm rv-wishlist-btn" title="Add to wishlist">
                        ♡ Wishlist
                    </button>
                </form>
                @endif

                {{-- Get Cash --}}
                @if($pricing)
                    @if(!empty($platformsData))
                    <button type="button"
                        class="btn btn--primary btn--sm js-cash-btn"
                        data-tpl="ctpl-{{ $gameId }}">
                        💰 Get Cash
                    </button>
                    <template id="ctpl-{{ $gameId }}" data-title="{{ $name }}">
                        @foreach($platformsData as $pd)
                        <div class="cash-dropdown__item">
                            <div class="cash-dropdown__item-info">
                                <span class="cash-dropdown__item-name">{{ $pd['name'] }}</span>
                                <span class="cash-dropdown__item-price">{{ $pd['price'] }}</span>
                            </div>
                            <form method="POST" action="{{ route('cash-basket.store') }}">
                                @csrf
                                <input type="hidden" name="igdb_game_id"  value="{{ $gameId }}">
                                <input type="hidden" name="platform_id"   value="{{ $pd['id'] }}">
                                <input type="hidden" name="game_title"    value="{{ $name }}">
                                <input type="hidden" name="cover_url"     value="{{ $imgUrl }}">
                                <input type="hidden" name="steam_app_id"  value="{{ $pd['steam_app_id'] }}">
                                <input type="hidden" name="release_date"  value="{{ $pd['release_date'] }}">
                                <button type="submit" class="btn btn--primary btn--xs">Add</button>
                            </form>
                        </div>
                        @endforeach
                    </template>
                    @else
                    <form method="POST" action="{{ route('cash-basket.store') }}">
                        @csrf
                        <input type="hidden" name="igdb_game_id" value="{{ $gameId }}">
                        <input type="hidden" name="game_title"   value="{{ $name }}">
                        <input type="hidden" name="cover_url"    value="{{ $imgUrl }}">
                        @if($gamePrice)
                        <input type="hidden" name="steam_app_id" value="{{ $gamePrice->steam_app_id }}">
                        <input type="hidden" name="release_date" value="{{ $gamePrice->release_date }}">
                        @endif
                        <button type="submit" class="btn btn--primary btn--sm">💰 Get Cash</button>
                    </form>
                    @endif
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @endif

</div>
@endsection
