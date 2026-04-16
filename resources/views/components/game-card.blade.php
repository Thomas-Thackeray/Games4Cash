@props(['game'])

@php
    $cardId  = $game['id'];
    $name    = $game['name'] ?? 'Unknown';
    $imgId   = $game['cover']['image_id'] ?? null;
    $imgUrl  = $imgId ? igdb_img($imgId, 'cover_big') : asset('img/placeholder.jpg');
    $genre   = $game['genres'][0]['name'] ?? '';
    $year    = isset($game['first_release_date']) ? date('Y', $game['first_release_date']) : '';

    $franchiseNames = array_column($game['franchises'] ?? [], 'name');

    $gamePrice = \App\Models\GamePrice::where('igdb_game_id', $cardId)->first();
    $pricing   = $gamePrice?->getComputedPrice($franchiseNames);

    // Don't show free-to-play games in the actions bar
    if ($pricing && $pricing['is_free']) {
        $pricing = null;
    }

    // Per-platform rows for the Get Cash dropdown (card uses DB-stored prices)
    $platformsData = [];
    if ($gamePrice && $pricing) {
        $allPlatforms = config('igdb.all_platforms');
        $platformIds  = json_decode($gamePrice->platform_ids ?? '[]', true);
        foreach ($platformIds as $pid) {
            if (! isset($allPlatforms[$pid])) {
                continue;
            }
            $p = $gamePrice->getComputedPriceForPlatform((int) $pid, $franchiseNames);
            if ($p && ! $p['is_free']) {
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
@endphp

<div class="game-card">
    <a href="{{ route('game.show', $cardId) }}" class="game-card__link">
        <div class="game-card__img-wrap">
            <img src="{{ $imgUrl }}" alt="{{ e($name) }}" loading="lazy" class="game-card__img">
        </div>
        <div class="game-card__info">
            <h3 class="game-card__title">{{ $name }}</h3>
            <div class="game-card__meta">
                @if($genre)<span class="tag">{{ e($genre) }}</span>@endif
                @if($year)<span class="year">{{ $year }}</span>@endif
            </div>
        </div>
    </a>
    @if($pricing)
    <div class="game-card__actions">
        @auth
            @if(!empty($platformsData))
            {{-- Dropdown trigger — template holds pre-built rows, JS shows the portal --}}
            <button type="button"
                class="btn btn--primary btn--xs js-cash-btn"
                data-tpl="ctpl-{{ $cardId }}">
                Get Cash
            </button>
            <template id="ctpl-{{ $cardId }}" data-title="{{ e($name) }}">
                @foreach($platformsData as $pd)
                <div class="cash-dropdown__item">
                    <div class="cash-dropdown__item-info">
                        <span class="cash-dropdown__item-name">{{ $pd['name'] }}</span>
                        <span class="cash-dropdown__item-price">{{ $pd['price'] }}</span>
                    </div>
                    <form method="POST" action="{{ route('cash-basket.store') }}">
                        @csrf
                        <input type="hidden" name="igdb_game_id"  value="{{ $cardId }}">
                        <input type="hidden" name="platform_id"   value="{{ $pd['id'] }}">
                        <input type="hidden" name="game_title"    value="{{ e($name) }}">
                        <input type="hidden" name="cover_url"     value="{{ $imgUrl }}">
                        <input type="hidden" name="steam_app_id"  value="{{ $pd['steam_app_id'] }}">
                        <input type="hidden" name="release_date"  value="{{ $pd['release_date'] }}">
                        <button type="submit" class="btn btn--primary btn--xs">Add</button>
                    </form>
                </div>
                @endforeach
            </template>
            @else
            {{-- No recognised platforms in DB yet — add without platform --}}
            <form method="POST" action="{{ route('cash-basket.store') }}">
                @csrf
                <input type="hidden" name="igdb_game_id" value="{{ $cardId }}">
                <input type="hidden" name="game_title"   value="{{ e($name) }}">
                <input type="hidden" name="cover_url"    value="{{ $imgUrl }}">
                <input type="hidden" name="steam_app_id" value="{{ $gamePrice->steam_app_id }}">
                <input type="hidden" name="release_date" value="{{ $gamePrice->release_date }}">
                <button type="submit" class="btn btn--primary btn--xs">Get Cash</button>
            </form>
            @endif
        @else
            @if(!empty($platformsData))
            <button type="button"
                class="btn btn--primary btn--xs js-cash-btn"
                data-tpl="ctpl-{{ $cardId }}">
                Get Cash
            </button>
            <template id="ctpl-{{ $cardId }}" data-title="{{ e($name) }}">
                @foreach($platformsData as $pd)
                <div class="cash-dropdown__item">
                    <div class="cash-dropdown__item-info">
                        <span class="cash-dropdown__item-name">{{ $pd['name'] }}</span>
                        <span class="cash-dropdown__item-price">{{ $pd['price'] }}</span>
                    </div>
                    <a href="{{ route('login') }}" class="btn btn--primary btn--xs">Sign In</a>
                </div>
                @endforeach
            </template>
            @else
            <a href="{{ route('login') }}" class="btn btn--primary btn--xs">Get Cash</a>
            @endif
        @endauth
    </div>
    @endif
</div>
