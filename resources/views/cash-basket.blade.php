@extends('layouts.app')
@section('title', 'Cash Basket')

@push('head_meta')
<style>
    .cb-condition-guide {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
        margin-top: 0.55rem;
    }
    .cb-condition-guide__item {
        display: flex;
        align-items: flex-start;
        gap: 0.45rem;
        font-size: 0.78rem;
        color: var(--text-muted);
        line-height: 1.45;
        padding: 0.3rem 0.5rem;
        border-radius: 5px;
        transition: background 0.15s, color 0.15s;
    }
    .cb-condition-guide__item--active {
        background: rgba(230,57,70,0.07);
        color: var(--text);
    }
    .cb-condition-guide__item--active strong { color: var(--accent); }
    .cb-condition-guide__icon { flex-shrink: 0; font-size: 0.85rem; margin-top: 1px; }
</style>
@endpush

@section('content')
<div class="container" style="padding: 3rem 0 5rem;">

    <div class="page-header" style="margin-bottom:2rem;">
        <h1 class="section-title" style="font-size:2rem;">Cash Basket</h1>
        <p style="color:var(--text-muted); margin-top:0.4rem;">{{ $itemsWithPrices->count() }} {{ $itemsWithPrices->count() === 1 ? 'game' : 'games' }}</p>
    </div>

    @if($itemsWithPrices->isEmpty())
    <div class="empty-state">
        <div class="icon">💰</div>
        <h3>Your cash basket is empty</h3>
        <p>Browse games and click "Get Cash" to add them here.</p>
        <a href="{{ route('search') }}" class="btn btn--primary" style="margin-top:1.5rem;">Browse Games</a>
    </div>
    @else
    <div class="cb-layout">
        <div class="cb-items">
            @foreach($itemsWithPrices as $item)
            <div class="cb-item" id="cb-item-{{ $item['id'] }}">
                <a href="{{ \App\Models\GamePrice::urlForId($item['igdb_game_id']) }}" class="cb-item__cover-link">
                    @if($item['cover_url'])
                    <img src="{{ $item['cover_url'] }}" alt="{{ $item['game_title'] }}" class="cb-item__cover">
                    @else
                    <div class="cb-item__cover cb-item__cover--placeholder">🎮</div>
                    @endif
                </a>
                <div class="cb-item__body">
                    <a href="{{ \App\Models\GamePrice::urlForId($item['igdb_game_id']) }}" class="cb-item__title">
                        {{ $item['game_title'] }}
                    </a>
                    @if($item['platform_name'])
                    <span class="cb-item__platform">{{ $item['platform_name'] }}</span>
                    @endif

                    {{-- Condition dropdown --}}
                    <div class="cb-item__condition-wrap">
                        <select class="cb-condition-select form-input"
                                data-item-id="{{ $item['id'] }}"
                                data-url="{{ route('cash-basket.condition', $item['id']) }}">
                            <option value="" {{ $item['condition'] === null ? 'selected' : '' }} disabled>
                                — Select condition —
                            </option>
                            <option value="new"      {{ $item['condition'] === 'new'      ? 'selected' : '' }}>💎 Brand New (Case Unopened)</option>
                            <option value="complete" {{ $item['condition'] === 'complete' ? 'selected' : '' }}>✅ Complete Game (In Case)</option>
                            <option value="disk"     {{ $item['condition'] === 'disk'     ? 'selected' : '' }}>💿 Just Disk (Disc Only)</option>
                        </select>
                        <div class="cb-condition-guide">
                            <div class="cb-condition-guide__item {{ $item['condition'] === 'new' ? 'cb-condition-guide__item--active' : '' }}" data-cond="new">
                                <span class="cb-condition-guide__icon">💎</span>
                                <span><strong>Brand New</strong> — Factory sealed, shrink wrap fully intact, never opened.</span>
                            </div>
                            <div class="cb-condition-guide__item {{ $item['condition'] === 'complete' ? 'cb-condition-guide__item--active' : '' }}" data-cond="complete">
                                <span class="cb-condition-guide__icon">✅</span>
                                <span><strong>Complete (In Case)</strong> — Disc, case and any original manual all present. Minor wear is fine.</span>
                            </div>
                            <div class="cb-condition-guide__item {{ $item['condition'] === 'disk' ? 'cb-condition-guide__item--active' : '' }}" data-cond="disk">
                                <span class="cb-condition-guide__icon">💿</span>
                                <span><strong>Just Disk</strong> — Disc only, no case and no manual included.</span>
                            </div>
                        </div>
                    </div>

                    {{-- Price (condition-adjusted once selected) --}}
                    @if($item['base_price'] !== null)
                        @if($item['condition'] !== null)
                        <span class="cb-item__price" data-price-for="{{ $item['id'] }}">{{ $item['display_price'] }}</span>
                        @else
                        <span class="cb-item__price cb-item__price--pending" data-price-for="{{ $item['id'] }}">Select condition to see price</span>
                        @endif
                    @else
                    <span class="cb-item__price cb-item__price--unavailable">Price unavailable</span>
                    @endif

                    {{-- Adjustment explanation --}}
                    <span class="cb-item__adj-label{{ $item['adjustment_label'] ? '' : ' cb-item__adj-label--hidden' }}"
                          data-adj-for="{{ $item['id'] }}">{{ $item['adjustment_label'] ?? '' }}</span>
                </div>
                <form method="POST" action="{{ route('cash-basket.destroy', $item['id']) }}" class="cb-item__remove">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="wishlist-card__remove-btn" title="Remove from basket"
                        data-confirm="Remove &quot;{{ $item['game_title'] }}&quot; from your cash basket?">✕</button>
                </form>
            </div>
            @endforeach
        </div>

        <div class="cb-summary">
            <div class="cb-summary__card">
                <h2 class="cb-summary__title">Cash Value</h2>
                <div class="cb-summary__total" id="cb-total">{{ $totalFormatted }}</div>

                @php
                    $minOrder  = (float) \App\Models\Setting::get('min_order_gbp', 20);
                    $shortfall = $minOrder > 0 ? max(0, $minOrder - $total) : 0;
                    $canProceed = $allHaveCondition && $shortfall == 0 && $total > 0;
                @endphp

                @if(! $allHaveCondition)
                <p class="cb-summary__note cb-summary__note--warn" id="cb-condition-note">
                    Select a condition for every game above before proceeding.
                </p>
                @elseif($total == 0)
                <p class="cb-summary__note">Add priced games to see your estimated cash value.</p>
                @else
                <p class="cb-summary__note">Estimated cash you could receive for these games.</p>
                @endif

                {{-- Always in DOM so JS can show/hide it after AJAX condition changes --}}
                <p class="cb-summary__note cb-summary__note--warn" id="cb-min-note"
                   @if($shortfall == 0) style="display:none;" @endif>
                    Minimum order is <strong>£{{ number_format($minOrder, 2) }}</strong>.
                    Add £<span id="cb-min-shortfall">{{ number_format($shortfall, 2) }}</span> more to proceed.
                </p>

                <a href="{{ route('cash-orders.create') }}"
                   id="cb-checkout-btn"
                   class="btn btn--primary"
                   style="width:100%; display:block; text-align:center; margin-top:1.25rem;{{ ! $canProceed ? ' opacity:0.45; pointer-events:none;' : '' }}"
                   @if(! $canProceed) aria-disabled="true" tabindex="-1" @endif>
                    Get Cash — <span id="cb-total-btn">{{ $totalFormatted }}</span>
                </a>
            </div>
        </div>
    </div>
    @endif

</div>

@if(! $itemsWithPrices->isEmpty())
<script>
(function () {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    var minOrder  = {{ $minOrder ?? 0 }};

    // Highlight the active condition guide row on page load (for pre-selected conditions)
    document.querySelectorAll('.cb-condition-select').forEach(function (sel) {
        if (sel.value) updateConditionGuide(sel.closest('.cb-item__condition-wrap'), sel.value);
    });

    function updateConditionGuide(wrap, condition) {
        if (!wrap) return;
        wrap.querySelectorAll('.cb-condition-guide__item').forEach(function (row) {
            row.classList.toggle('cb-condition-guide__item--active', row.dataset.cond === condition);
        });
    }

    document.querySelectorAll('.cb-condition-select').forEach(function (select) {
        select.addEventListener('change', async function () {
            var itemId    = this.dataset.itemId;
            var url       = this.dataset.url;
            var condition = this.value;

            updateConditionGuide(this.closest('.cb-item__condition-wrap'), condition);
            var priceEl   = document.querySelector('[data-price-for="' + itemId + '"]');
            var adjEl     = document.querySelector('[data-adj-for="' + itemId + '"]');

            if (priceEl) priceEl.textContent = '…';

            try {
                var res = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ condition: condition }),
                });

                if (!res.ok) { location.reload(); return; }

                var data = await res.json();

                // Item price
                if (priceEl) {
                    priceEl.textContent = data.item_price || 'Price unavailable';
                    priceEl.classList.remove('cb-item__price--pending');
                }

                // Adjustment explanation
                if (adjEl) {
                    adjEl.textContent = data.adjustment_label || '';
                    adjEl.classList.toggle('cb-item__adj-label--hidden', !data.adjustment_label);
                }

                // Basket total
                var totalEl    = document.getElementById('cb-total');
                var totalBtnEl = document.getElementById('cb-total-btn');
                if (totalEl)    totalEl.textContent    = data.basket_total;
                if (totalBtnEl) totalBtnEl.textContent = data.basket_total;

                // Checkout button
                var raw        = data.basket_total_raw || 0;
                var canProceed = data.all_have_condition && raw > 0 && (minOrder <= 0 || raw >= minOrder);
                var btn = document.getElementById('cb-checkout-btn');
                if (btn) {
                    btn.style.opacity       = canProceed ? '1' : '0.45';
                    btn.style.pointerEvents = canProceed ? '' : 'none';
                    btn.setAttribute('aria-disabled', canProceed ? 'false' : 'true');
                    btn.tabIndex = canProceed ? 0 : -1;
                }

                // Remove "select condition" notice once all done
                if (data.all_have_condition) {
                    var note = document.getElementById('cb-condition-note');
                    if (note) note.remove();
                }

                // Update minimum order notice
                var minNote = document.getElementById('cb-min-note');
                var minShortfallEl = document.getElementById('cb-min-shortfall');
                if (minNote && minOrder > 0) {
                    var shortfall = minOrder - raw;
                    if (shortfall > 0 && data.all_have_condition) {
                        if (minShortfallEl) minShortfallEl.textContent = shortfall.toFixed(2);
                        minNote.style.display = '';
                    } else {
                        minNote.style.display = 'none';
                    }
                }

            } catch (e) {
                location.reload();
            }
        });
    });
})();
</script>
@endif
@endsection
