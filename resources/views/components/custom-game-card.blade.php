@props(['game'])

@php
    $coverUrl    = $game->cover_image_path ? asset('storage/' . $game->cover_image_path) : asset('img/coming-soon.svg');
    $cardUrl     = route('game.show', $game->slug);
    $platforms   = config('igdb.all_platforms', []);
    $pricingRows = [];

    foreach ($platforms as $platformId => $platformName) {
        $price = $game->priceForPlatform($platformId);
        if ($price !== null) {
            $pricingRows[] = [
                'id'    => $platformId,
                'name'  => $platformName,
                'price' => '£' . number_format($price, 2),
            ];
        }
    }

    $bestPrice = !empty($pricingRows)
        ? '£' . number_format(max(array_map(fn($r) => $game->priceForPlatform($r['id']), $pricingRows)), 2)
        : null;
@endphp

@if(!empty($pricingRows))
<div class="game-card">
    <a href="{{ $cardUrl }}" class="game-card__link">
        <div class="game-card__img-wrap">
            <img src="{{ $coverUrl }}" alt="{{ $game->title }}" loading="lazy" class="game-card__img">
        </div>
        <div class="game-card__info">
            <h3 class="game-card__title">{{ $game->title }}</h3>
            <div class="game-card__meta">
                @if(!empty($game->genres[0]))<span class="tag">{{ $game->genres[0] }}</span>@endif
                @if($game->release_year)<span class="year">{{ $game->release_year }}</span>@endif
            </div>
        </div>
    </a>
    <div class="game-card__actions">
        @auth
        <button type="button"
            class="btn btn--primary btn--xs js-cash-btn"
            data-tpl="ctpl-cg-{{ $game->id }}">
            Get Cash
        </button>
        <template id="ctpl-cg-{{ $game->id }}" data-title="{{ $game->title }}">
            @foreach($pricingRows as $row)
            <div class="cash-dropdown__item">
                <div class="cash-dropdown__item-info">
                    <span class="cash-dropdown__item-name">{{ $row['name'] }}</span>
                    <span class="cash-dropdown__item-price">{{ $row['price'] }}</span>
                </div>
                <form method="POST" action="{{ route('cash-basket.store') }}">
                    @csrf
                    <input type="hidden" name="custom_game_id" value="{{ $game->id }}">
                    <input type="hidden" name="platform_id"    value="{{ $row['id'] }}">
                    <input type="hidden" name="game_title"     value="{{ $game->title }}">
                    <input type="hidden" name="cover_url"      value="{{ $coverUrl }}">
                    <button type="submit" class="btn btn--primary btn--xs">Add</button>
                </form>
            </div>
            @endforeach
        </template>
        @else
        <button type="button"
            class="btn btn--primary btn--xs js-cash-btn"
            data-tpl="ctpl-cg-{{ $game->id }}">
            Get Cash
        </button>
        <template id="ctpl-cg-{{ $game->id }}" data-title="{{ $game->title }}">
            @foreach($pricingRows as $row)
            <div class="cash-dropdown__item">
                <div class="cash-dropdown__item-info">
                    <span class="cash-dropdown__item-name">{{ $row['name'] }}</span>
                    <span class="cash-dropdown__item-price">{{ $row['price'] }}</span>
                </div>
                <a href="{{ route('login') }}" class="btn btn--primary btn--xs">Sign In</a>
            </div>
            @endforeach
        </template>
        @endauth
    </div>
</div>
@endif
