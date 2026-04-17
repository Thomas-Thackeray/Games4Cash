@extends('layouts.app')
@section('title', 'Game Prices')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Game Prices</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.settings') }}" style="color:var(--accent);">← Settings</a></p>
        </div>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('admin.game-prices') }}" class="admin-search-form">
        <input type="search" name="search" value="{{ $search }}"
            class="form-input admin-search-input"
            placeholder="Search by game name or slug…">
        <button type="submit" class="btn btn--outline btn--sm">Search</button>
        @if($search)
        <a href="{{ route('admin.game-prices') }}" class="btn btn--outline btn--sm">Clear</a>
        @endif
    </form>

    @if($gamePrices->isEmpty())
    <div class="empty-state" style="padding:3rem 0;">
        <div class="icon">🎮</div>
        <h3>No games found</h3>
        <p>{{ $search ? 'No games match your search.' : 'No game price records exist yet.' }}</p>
    </div>
    @else

    <p class="admin-td-muted" style="margin-bottom:1rem; font-size:0.875rem;">
        Showing {{ $gamePrices->firstItem() }}–{{ $gamePrices->lastItem() }} of {{ $gamePrices->total() }} platform entries
        ({{ $gamePrices->total() }} rows across {{ $gamePrices->lastPage() }} page{{ $gamePrices->lastPage() === 1 ? '' : 's' }})
    </p>

    <div id="price-feedback" style="display:none; margin-bottom:1rem;" class="alert alert--success"></div>

    <div class="admin-table-wrap">
        <table class="admin-table" id="game-prices-table">
            <thead>
                <tr>
                    <th>Game</th>
                    <th>Platform</th>
                    <th>Calculated Price</th>
                    <th>Calculated With</th>
                    <th>Override Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gamePrices as $gamePrice)
                    @php
                        $platformIds = json_decode($gamePrice->platform_ids, true) ?? [];
                    @endphp
                    @foreach($platformIds as $platformId)
                        @php
                            $platformName = $allPlatforms[$platformId] ?? null;
                            if (!$platformName) continue;
                            $result = $gamePrice->adminPriceForPlatform((int) $platformId);
                            $displayPrice = $result['display_price'] ?? '—';
                            $source = $result['source'] ?? null;
                            $overrides = $gamePrice->price_overrides ?? [];
                            $hasOverride = isset($overrides[$platformId]);
                            $rowId = 'row-' . $gamePrice->igdb_game_id . '-' . $platformId;
                        @endphp
                        <tr id="{{ $rowId }}">
                            <td>
                                @if($gamePrice->slug)
                                    <a href="{{ route('game.show', $gamePrice->slug) }}" target="_blank"
                                       style="color:var(--accent); text-decoration:none; font-weight:500;">
                                        {{ $gamePrice->displayName() }}
                                    </a>
                                @else
                                    <span style="font-weight:500;">{{ $gamePrice->displayName() }}</span>
                                @endif
                            </td>
                            <td class="admin-td-muted">{{ $platformName }}</td>
                            <td class="js-display-price" data-row="{{ $rowId }}">
                                {{ $displayPrice }}
                            </td>
                            <td class="js-source-cell" data-row="{{ $rowId }}">
                                @include('admin._price-source-badge', ['source' => $source])
                            </td>
                            <td>
                                <div style="display:flex; align-items:center; gap:0.5rem;">
                                    <input type="number"
                                        class="form-input js-override-input"
                                        style="width:90px; padding:0.3rem 0.5rem; font-size:0.875rem;"
                                        placeholder="£0.00"
                                        step="0.01" min="0" max="999.99"
                                        value="{{ $hasOverride ? number_format((float)$overrides[$platformId], 2) : '' }}"
                                        data-igdb="{{ $gamePrice->igdb_game_id }}"
                                        data-platform="{{ $platformId }}"
                                        data-row="{{ $rowId }}">
                                    <button type="button"
                                        class="btn btn--outline btn--sm js-save-override"
                                        data-igdb="{{ $gamePrice->igdb_game_id }}"
                                        data-platform="{{ $platformId }}"
                                        data-row="{{ $rowId }}">
                                        Save
                                    </button>
                                    <button type="button"
                                        class="btn btn--sm js-clear-override"
                                        style="background:rgba(230,57,70,0.12); color:var(--accent); border:1px solid rgba(230,57,70,0.3);"
                                        data-igdb="{{ $gamePrice->igdb_game_id }}"
                                        data-platform="{{ $platformId }}"
                                        data-row="{{ $rowId }}"
                                        {{ !$hasOverride ? 'disabled' : '' }}>
                                        Clear
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($gamePrices->hasPages())
    <div style="margin-top:1.5rem;">
        {{ $gamePrices->links() }}
    </div>
    @endif

    @endif

</div>

<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function showFeedback(msg, isError) {
        const el = document.getElementById('price-feedback');
        el.textContent = msg;
        el.className = 'alert ' + (isError ? 'alert--error' : 'alert--success');
        el.style.display = 'block';
        clearTimeout(el._timer);
        el._timer = setTimeout(() => { el.style.display = 'none'; }, 3000);
    }

    function updateRow(rowId, data) {
        // Update displayed price
        document.querySelectorAll(`.js-display-price[data-row="${rowId}"]`).forEach(el => {
            el.textContent = data.display_price;
        });
        // Update source badge
        document.querySelectorAll(`.js-source-cell[data-row="${rowId}"]`).forEach(el => {
            el.innerHTML = sourceBadgeHtml(data.source);
        });
        // Update clear button state
        const clearBtn = document.querySelector(`.js-clear-override[data-row="${rowId}"]`);
        if (clearBtn) {
            clearBtn.disabled = !data.override_set;
        }
    }

    function sourceBadgeHtml(source) {
        if (!source) return '<span style="color:var(--text-muted);">—</span>';
        const map = {
            'CeX':        { bg: 'rgba(34,197,94,0.15)',  color: '#16a34a', border: 'rgba(34,197,94,0.3)'  },
            'CheapShark': { bg: 'rgba(59,130,246,0.15)', color: '#2563eb', border: 'rgba(59,130,246,0.3)' },
            'Steam':      { bg: 'rgba(249,115,22,0.15)', color: '#ea580c', border: 'rgba(249,115,22,0.3)' },
            'Override':   { bg: 'rgba(168,85,247,0.15)', color: '#9333ea', border: 'rgba(168,85,247,0.3)' },
            'Base Price': { bg: 'rgba(100,116,139,0.15)', color: '#64748b', border: 'rgba(100,116,139,0.3)' },
        };
        const s = map[source] ?? map['Base Price'];
        return `<span style="display:inline-block; padding:0.2rem 0.55rem; border-radius:999px; font-size:0.78rem; font-weight:600; background:${s.bg}; color:${s.color}; border:1px solid ${s.border};">${source}</span>`;
    }

    async function patchOverride(igdbId, platformId, price, rowId) {
        const url = '{{ rtrim(url('/'), '/') }}/admin/game-prices/' + igdbId + '/' + platformId + '/override';
        const body = new FormData();
        body.append('_method', 'PATCH');
        if (price !== null && price !== '') {
            body.append('price', price);
        }

        const resp = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body,
        });

        if (!resp.ok) {
            const err = await resp.json().catch(() => ({}));
            throw new Error(err.message ?? 'Request failed');
        }
        return resp.json();
    }

    document.addEventListener('click', async function (e) {
        // Save override
        if (e.target.closest('.js-save-override')) {
            const btn = e.target.closest('.js-save-override');
            const { igdb, platform, row } = btn.dataset;
            const input = document.querySelector(`.js-override-input[data-row="${row}"]`);
            const price = input?.value?.trim() ?? '';

            btn.disabled = true;
            try {
                const data = await patchOverride(igdb, platform, price, row);
                updateRow(row, data);
                showFeedback('Price override saved.', false);
            } catch (err) {
                showFeedback('Failed to save: ' + err.message, true);
            } finally {
                btn.disabled = false;
            }
        }

        // Clear override
        if (e.target.closest('.js-clear-override')) {
            const btn = e.target.closest('.js-clear-override');
            const { igdb, platform, row } = btn.dataset;
            const input = document.querySelector(`.js-override-input[data-row="${row}"]`);

            btn.disabled = true;
            try {
                const data = await patchOverride(igdb, platform, '', row);
                if (input) input.value = '';
                updateRow(row, data);
                showFeedback('Override cleared.', false);
            } catch (err) {
                showFeedback('Failed to clear: ' + err.message, true);
                btn.disabled = false;
            }
        }
    });

    // Allow Enter key to submit from the input
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && e.target.closest('.js-override-input')) {
            e.preventDefault();
            const input = e.target.closest('.js-override-input');
            const row = input.dataset.row;
            document.querySelector(`.js-save-override[data-row="${row}"]`)?.click();
        }
    });
})();
</script>
@endsection
