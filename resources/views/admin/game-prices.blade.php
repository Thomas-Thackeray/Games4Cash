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

    @if(session('flash_success'))
    <div class="alert alert--success" style="margin-bottom:1.5rem;">{{ session('flash_success') }}</div>
    @endif

    {{-- Source filter tabs --}}
    @php
    $filters = [
        ''           => 'All',
        'cheapshark' => 'CheapShark',
        'steam'      => 'Steam',
        'none'       => 'No Price',
        'override'   => 'Overridden',
        'over10'     => 'Over £10',
        'hidden'     => 'Hidden',
    ];
    @endphp
    <div style="display:flex; gap:0.4rem; flex-wrap:wrap; margin-bottom:1.25rem;">
        @foreach($filters as $val => $label)
        @php
            $active  = $source === $val;
            $href    = route('admin.game-prices', array_filter(['source' => $val ?: null, 'search' => $search ?: null, 'price_min' => $priceMin ?? null, 'price_max' => $priceMax ?? null]));
            $colours = [
                'cheapshark' => ['bg'=>'rgba(59,130,246,0.15)', 'color'=>'#2563eb', 'border'=>'rgba(59,130,246,0.4)' ],
                'steam'      => ['bg'=>'rgba(249,115,22,0.15)', 'color'=>'#ea580c', 'border'=>'rgba(249,115,22,0.4)' ],
                'base'       => ['bg'=>'rgba(100,116,139,0.15)','color'=>'#64748b', 'border'=>'rgba(100,116,139,0.4)'],
                'none'       => ['bg'=>'rgba(230,57,70,0.12)',  'color'=>'#e63946', 'border'=>'rgba(230,57,70,0.3)'  ],
                'override'   => ['bg'=>'rgba(168,85,247,0.15)', 'color'=>'#9333ea', 'border'=>'rgba(168,85,247,0.4)' ],
                'over10'     => ['bg'=>'rgba(234,179,8,0.15)',  'color'=>'#ca8a04', 'border'=>'rgba(234,179,8,0.4)'  ],
                'hidden'     => ['bg'=>'rgba(30,30,30,0.6)',    'color'=>'#94a3b8', 'border'=>'rgba(100,116,139,0.4)'],
                ''           => ['bg'=>'var(--bg-card)',        'color'=>'var(--text)','border'=>'var(--border)'      ],
            ];
            $c = $colours[$val];
        @endphp
        <a href="{{ $href }}"
           style="display:inline-block; padding:0.3rem 0.85rem; border-radius:999px; font-size:0.8rem; font-weight:600; text-decoration:none;
                  background:{{ $active ? ($val ? $c['bg'] : 'var(--accent)') : 'var(--bg-card)' }};
                  color:{{ $active ? ($val ? $c['color'] : '#fff') : 'var(--text-muted)' }};
                  border:1px solid {{ $active ? $c['border'] : 'var(--border)' }};
                  transition:0.15s;">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Search (server-side) --}}
    <form method="GET" action="{{ route('admin.game-prices') }}" class="admin-search-form">
        @if($source)
        <input type="hidden" name="source" value="{{ $source }}">
        @endif
        <input type="search" name="search" value="{{ $search }}"
            class="form-input admin-search-input"
            placeholder="Search by game name or slug…">
        <button type="submit" class="btn btn--outline btn--sm">Search</button>
        @if($search)
        <a href="{{ route('admin.game-prices', array_filter(['source' => $source ?: null])) }}" class="btn btn--outline btn--sm">Clear</a>
        @endif
    </form>

    {{-- Calculated price filter (client-side — filters rows on the current page instantly) --}}
    <div style="display:flex; align-items:center; gap:0.5rem; margin-top:0.6rem; flex-wrap:wrap;">
        <span style="font-size:0.8rem; color:var(--text-muted); white-space:nowrap;">Filter by calc. price:</span>
        <span style="font-size:0.82rem; color:var(--text-muted);">£</span>
        <input type="number" id="price-min-filter" min="0" step="0.01" placeholder="Min"
               class="form-input"
               style="width:90px; padding:0.4rem 0.6rem; font-size:0.88rem; text-align:center;">
        <span style="font-size:0.82rem; color:var(--text-muted);">–&nbsp;£</span>
        <input type="number" id="price-max-filter" min="0" step="0.01" placeholder="Max"
               class="form-input"
               style="width:90px; padding:0.4rem 0.6rem; font-size:0.88rem; text-align:center;">
        <button type="button" id="price-filter-clear" class="btn btn--outline btn--sm" style="display:none;">Clear</button>
        <span id="price-filter-count" style="font-size:0.8rem; color:var(--text-muted);"></span>
    </div>

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
                    <th style="width:36px; text-align:center;">
                        <input type="checkbox" id="select-all" title="Select all rows"
                               style="width:16px; height:16px; cursor:pointer; accent-color:var(--accent);">
                    </th>
                    <th>Game</th>
                    <th>Platform</th>
                    <th>Base Price</th>
                    <th>Calculated Price</th>
                    <th>Calculated With</th>
                    <th>Override Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gamePrices as $gamePrice)
                    @php
                        $platformIds = json_decode($gamePrice->platform_ids, true) ?? [];
                        $gameHiddenPlatforms = $hiddenMap[$gamePrice->igdb_game_id] ?? [];
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
                            $isRowHidden = in_array((int) $platformId, $gameHiddenPlatforms, true);
                        @endphp
                        <tr id="{{ $rowId }}" data-game="{{ $gamePrice->igdb_game_id }}"
                            style="{{ $isRowHidden ? 'opacity:0.45;' : '' }}">
                            <td style="text-align:center; vertical-align:middle;">
                                <input type="checkbox"
                                       class="js-row-check"
                                       style="width:16px; height:16px; cursor:pointer; accent-color:var(--accent);"
                                       data-igdb="{{ $gamePrice->igdb_game_id }}"
                                       data-platform="{{ $platformId }}"
                                       data-row="{{ $rowId }}">
                            </td>
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
                            <td class="admin-td-muted">
                                @if($gamePrice->base_price_gbp !== null)
                                    £{{ number_format($gamePrice->base_price_gbp, 2) }}
                                @else
                                    <span style="color:var(--text-dim);">—</span>
                                @endif
                            </td>
                            <td class="js-display-price js-breakdown-trigger"
                                data-row="{{ $rowId }}"
                                data-igdb="{{ $gamePrice->igdb_game_id }}"
                                data-platform="{{ $platformId }}"
                                title="Click to see price calculation"
                                style="cursor:pointer; text-decoration:underline dotted; text-underline-offset:3px;">
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
                            <td style="text-align:center;">
                                <button type="button"
                                    class="btn btn--sm js-toggle-hide"
                                    data-igdb="{{ $gamePrice->igdb_game_id }}"
                                    data-platform="{{ $platformId }}"
                                    data-row="{{ $rowId }}"
                                    data-hidden="{{ $isRowHidden ? '1' : '0' }}"
                                    style="{{ $isRowHidden
                                        ? 'background:rgba(100,116,139,0.15); color:#94a3b8; border:1px solid rgba(100,116,139,0.4);'
                                        : 'background:rgba(30,30,30,0.5); color:#94a3b8; border:1px solid rgba(100,116,139,0.3);' }}
                                        white-space:nowrap; min-width:72px;">
                                    {{ $isRowHidden ? 'Unhide' : 'Hide' }}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div style="margin-top:0.5rem; margin-bottom:5rem;">
        {{ $gamePrices->links() }}
    </div>

    {{-- Bulk action bar (appears when rows are selected) --}}
    <div id="bulk-bar" style="
        display:none;
        position:fixed; bottom:0; left:0; right:0; z-index:200;
        background:var(--bg-card); border-top:1px solid var(--border);
        padding:0.85rem 1.5rem;
        box-shadow:0 -4px 20px rgba(0,0,0,0.35);">
        <div style="max-width:1200px; margin:0 auto; display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
            <span id="bulk-count" style="font-weight:600; color:var(--accent); white-space:nowrap; min-width:90px;"></span>
            <div style="display:flex; align-items:center; gap:0.5rem; flex:1; flex-wrap:wrap;">
                <label style="font-size:0.875rem; color:var(--text-muted); white-space:nowrap;">Set price (£):</label>
                <input type="number" id="bulk-price"
                       class="form-input"
                       style="width:110px; padding:0.35rem 0.6rem; font-size:0.875rem;"
                       placeholder="0.00" step="0.01" min="0" max="999.99">
                <button type="button" id="bulk-apply"
                        class="btn btn--sm"
                        style="background:var(--accent); color:#fff; border:none;">
                    Apply to Selected
                </button>
                <button type="button" id="bulk-clear"
                        class="btn btn--sm"
                        style="background:rgba(230,57,70,0.12); color:var(--accent); border:1px solid rgba(230,57,70,0.3);">
                    Clear Overrides
                </button>
            </div>
            <button type="button" id="bulk-deselect"
                    class="btn btn--outline btn--sm" style="white-space:nowrap;">
                Deselect All
            </button>
        </div>
        <div id="bulk-progress" style="display:none; margin-top:0.5rem; max-width:1200px; margin-left:auto; margin-right:auto;">
            <div style="height:4px; background:var(--border); border-radius:2px; overflow:hidden;">
                <div id="bulk-progress-bar" style="height:100%; width:0%; background:var(--accent); transition:width 0.15s;"></div>
            </div>
            <p id="bulk-progress-text" style="font-size:0.78rem; color:var(--text-muted); margin-top:0.3rem;"></p>
        </div>
    </div>

    @endif

</div>

{{-- Price breakdown modal --}}
<div id="breakdown-overlay" style="
    display:none; position:fixed; inset:0; z-index:500;
    background:rgba(0,0,0,0.6); backdrop-filter:blur(2px);
    align-items:center; justify-content:center;">
    <div style="
        background:var(--bg-card); border:1px solid var(--border); border-radius:10px;
        padding:1.5rem 1.75rem; max-width:460px; width:90%; max-height:85vh; overflow-y:auto;
        box-shadow:0 20px 60px rgba(0,0,0,0.5);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
            <h3 id="breakdown-title" style="margin:0; font-size:1rem; color:var(--text);">Price Breakdown</h3>
            <button id="breakdown-close" style="
                background:none; border:none; cursor:pointer; color:var(--text-muted);
                font-size:1.3rem; line-height:1; padding:0 0.25rem;">×</button>
        </div>
        <div id="breakdown-body">
            <p style="color:var(--text-muted); text-align:center; padding:1rem 0;">Loading…</p>
        </div>
    </div>
</div>

<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── Feedback ──────────────────────────────────────────────────────────────
    function showFeedback(msg, isError) {
        const el = document.getElementById('price-feedback');
        el.textContent = msg;
        el.className = 'alert ' + (isError ? 'alert--error' : 'alert--success');
        el.style.display = 'block';
        clearTimeout(el._timer);
        el._timer = setTimeout(() => { el.style.display = 'none'; }, 4000);
    }

    // ── Row update helpers ────────────────────────────────────────────────────
    function updateRow(rowId, data) {
        document.querySelectorAll(`.js-display-price[data-row="${rowId}"]`).forEach(el => {
            el.textContent = data.display_price;
        });
        document.querySelectorAll(`.js-source-cell[data-row="${rowId}"]`).forEach(el => {
            el.innerHTML = sourceBadgeHtml(data.source);
        });
        const clearBtn = document.querySelector(`.js-clear-override[data-row="${rowId}"]`);
        if (clearBtn) clearBtn.disabled = !data.override_set;
        const input = document.querySelector(`.js-override-input[data-row="${rowId}"]`);
        if (input && data.override_set && data.display_price !== '—') {
            input.value = parseFloat(data.display_price.replace('£', '')).toFixed(2);
        }
    }

    function sourceBadgeHtml(source) {
        if (!source) return '<span style="color:var(--text-muted);">—</span>';
        const map = {
            'CeX':        { bg: 'rgba(34,197,94,0.15)',   color: '#16a34a', border: 'rgba(34,197,94,0.3)'   },
            'CheapShark': { bg: 'rgba(59,130,246,0.15)',  color: '#2563eb', border: 'rgba(59,130,246,0.3)'  },
            'Steam':      { bg: 'rgba(249,115,22,0.15)',  color: '#ea580c', border: 'rgba(249,115,22,0.3)'  },
            'Override':   { bg: 'rgba(168,85,247,0.15)',  color: '#9333ea', border: 'rgba(168,85,247,0.3)'  },
        };
        const s = map[source] ?? map['Base Price'];
        return `<span style="display:inline-block; padding:0.2rem 0.55rem; border-radius:999px; font-size:0.78rem; font-weight:600; background:${s.bg}; color:${s.color}; border:1px solid ${s.border};">${source}</span>`;
    }

    // ── PATCH override ────────────────────────────────────────────────────────
    async function patchOverride(igdbId, platformId, price) {
        const url  = '{{ rtrim(url('/'), '/') }}/admin/game-prices/' + igdbId + '/' + platformId + '/override';
        const body = new FormData();
        body.append('_method', 'PATCH');
        if (price !== null && price !== '') body.append('price', price);

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

    // ── Bulk selection ────────────────────────────────────────────────────────
    const bulkBar      = document.getElementById('bulk-bar');
    const bulkCount    = document.getElementById('bulk-count');
    const bulkPrice    = document.getElementById('bulk-price');
    const bulkApply    = document.getElementById('bulk-apply');
    const bulkClear    = document.getElementById('bulk-clear');
    const bulkDeselect = document.getElementById('bulk-deselect');
    const bulkProgress = document.getElementById('bulk-progress');
    const bulkBar$     = document.getElementById('bulk-progress-bar');
    const bulkText     = document.getElementById('bulk-progress-text');
    const selectAll    = document.getElementById('select-all');

    function getChecked() {
        return [...document.querySelectorAll('.js-row-check:checked')];
    }

    function syncBulkBar() {
        const checked = getChecked();
        const n = checked.length;
        if (n === 0) {
            bulkBar.style.display = 'none';
            if (selectAll) selectAll.indeterminate = false, selectAll.checked = false;
        } else {
            bulkBar.style.display = 'block';
            bulkCount.textContent = n === 1 ? '1 row selected' : n + ' rows selected';
            const all = document.querySelectorAll('.js-row-check').length;
            if (selectAll) {
                selectAll.checked       = n === all;
                selectAll.indeterminate = n > 0 && n < all;
            }
        }
    }

    // Select-all toggle
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.js-row-check').forEach(cb => {
                cb.checked = selectAll.checked;
            });
            syncBulkBar();
        });
    }

    // Individual checkbox change
    document.getElementById('game-prices-table')?.addEventListener('change', function (e) {
        if (e.target.classList.contains('js-row-check')) {
            syncBulkBar();
        }
    });

    // Deselect all
    bulkDeselect?.addEventListener('click', function () {
        document.querySelectorAll('.js-row-check').forEach(cb => cb.checked = false);
        if (selectAll) selectAll.checked = false, selectAll.indeterminate = false;
        syncBulkBar();
    });

    // Bulk apply / clear
    async function runBulk(clearMode) {
        const checked = getChecked();
        if (checked.length === 0) return;

        const price = clearMode ? '' : (bulkPrice?.value?.trim() ?? '');
        if (!clearMode && price === '') {
            showFeedback('Enter a price before applying.', true);
            bulkPrice?.focus();
            return;
        }

        bulkApply.disabled  = true;
        bulkClear.disabled  = true;
        bulkProgress.style.display = 'block';

        let done = 0;
        const total = checked.length;
        const errors = [];

        for (const cb of checked) {
            const { igdb, platform, row } = cb.dataset;
            bulkText.textContent = `Saving ${done + 1} of ${total}…`;
            bulkBar$.style.width = (done / total * 100) + '%';

            try {
                const data = await patchOverride(igdb, platform, price);
                updateRow(row, data);
                if (clearMode) {
                    const input = document.querySelector(`.js-override-input[data-row="${row}"]`);
                    if (input) input.value = '';
                }
            } catch (err) {
                errors.push(row);
            }
            done++;
        }

        bulkBar$.style.width = '100%';
        bulkText.textContent = '';
        setTimeout(() => { bulkProgress.style.display = 'none'; bulkBar$.style.width = '0%'; }, 600);

        if (errors.length === 0) {
            showFeedback(clearMode
                ? `Cleared overrides for ${total} row${total !== 1 ? 's' : ''}.`
                : `Applied £${parseFloat(price).toFixed(2)} to ${total} row${total !== 1 ? 's' : ''}.`,
                false);
        } else {
            showFeedback(`${done - errors.length}/${total} updated. ${errors.length} failed.`, true);
        }

        bulkApply.disabled = false;
        bulkClear.disabled = false;
    }

    bulkApply?.addEventListener('click', () => runBulk(false));
    bulkClear?.addEventListener('click', () => runBulk(true));

    // ── Per-row click handlers ────────────────────────────────────────────────
    document.addEventListener('click', async function (e) {

        // Save single override
        if (e.target.closest('.js-save-override')) {
            const btn = e.target.closest('.js-save-override');
            const { igdb, platform, row } = btn.dataset;
            const input = document.querySelector(`.js-override-input[data-row="${row}"]`);
            const price = input?.value?.trim() ?? '';

            btn.disabled = true;
            try {
                const data = await patchOverride(igdb, platform, price);
                updateRow(row, data);
                showFeedback('Price override saved.', false);
            } catch (err) {
                showFeedback('Failed to save: ' + err.message, true);
            } finally {
                btn.disabled = false;
            }
        }

        // Clear single override
        if (e.target.closest('.js-clear-override')) {
            const btn = e.target.closest('.js-clear-override');
            const { igdb, platform, row } = btn.dataset;
            const input = document.querySelector(`.js-override-input[data-row="${row}"]`);

            btn.disabled = true;
            try {
                const data = await patchOverride(igdb, platform, '');
                if (input) input.value = '';
                updateRow(row, data);
                showFeedback('Override cleared.', false);
            } catch (err) {
                showFeedback('Failed to clear: ' + err.message, true);
                btn.disabled = false;
            }
        }

        // Toggle hide
        if (e.target.closest('.js-toggle-hide')) {
            const btn = e.target.closest('.js-toggle-hide');
            const { igdb, platform, row } = btn.dataset;
            const url = '{{ rtrim(url('/'), '/') }}/admin/game-prices/' + igdb + '/' + platform + '/hide';

            btn.disabled = true;
            try {
                const resp = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                });
                if (!resp.ok) throw new Error('Request failed');
                const data = await resp.json();

                const tr = document.getElementById(row);
                if (tr) tr.style.opacity = data.hidden ? '0.45' : '';

                btn.dataset.hidden = data.hidden ? '1' : '0';
                btn.textContent    = data.hidden ? 'Unhide' : 'Hide';
                btn.style.cssText  = data.hidden
                    ? 'background:rgba(100,116,139,0.15); color:#94a3b8; border:1px solid rgba(100,116,139,0.4); white-space:nowrap; min-width:72px;'
                    : 'background:rgba(30,30,30,0.5); color:#94a3b8; border:1px solid rgba(100,116,139,0.3); white-space:nowrap; min-width:72px;';

                showFeedback(data.hidden ? 'Row hidden from application.' : 'Row is now visible.', false);
            } catch (err) {
                showFeedback('Failed to update visibility.', true);
            } finally {
                btn.disabled = false;
            }
        }
    });

    // Enter key in single override input
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && e.target.closest('.js-override-input')) {
            e.preventDefault();
            const row = e.target.closest('.js-override-input').dataset.row;
            document.querySelector(`.js-save-override[data-row="${row}"]`)?.click();
        }
        if (e.key === 'Escape') closeBreakdown();
    });

    // ── Price breakdown modal ─────────────────────────────────────────────────
    const overlay       = document.getElementById('breakdown-overlay');
    const breakdownBody = document.getElementById('breakdown-body');
    const breakdownTitle = document.getElementById('breakdown-title');

    function closeBreakdown() {
        overlay.style.display = 'none';
    }

    function fmtGbp(v) {
        return '£' + parseFloat(v).toFixed(2);
    }

    function renderBreakdown(data, gameTitle, platformName) {
        breakdownTitle.textContent = gameTitle + (platformName ? ' — ' + platformName : '');

        if (data.is_free) {
            breakdownBody.innerHTML = '<p style="color:var(--text-muted);">This game is free to play — no cash offer applies.</p>';
            return;
        }

        if (data.error) {
            breakdownBody.innerHTML = `<p style="color:var(--accent-2);">${data.error}</p>`;
            return;
        }

        let html = '<table style="width:100%; border-collapse:collapse; font-size:0.875rem;">';
        html += '<thead><tr>'
             + '<th style="text-align:left; padding:0.4rem 0.5rem; color:var(--text-muted); font-weight:600; border-bottom:1px solid var(--border);">Step</th>'
             + '<th style="text-align:right; padding:0.4rem 0.5rem; color:var(--text-muted); font-weight:600; border-bottom:1px solid var(--border);">Running Total</th>'
             + '</tr></thead><tbody>';

        data.steps.forEach((step, i) => {
            const isLast  = i === data.steps.length - 1;
            const rowStyle = isLast
                ? 'background:rgba(255,255,255,0.04); font-weight:700;'
                : '';
            html += `<tr style="${rowStyle}">`;
            html += `<td style="padding:0.5rem 0.5rem; border-bottom:1px solid rgba(255,255,255,0.05);">`;
            html += `<div style="color:var(--text);">${step.label}</div>`;
            if (step.note) html += `<div style="color:var(--text-muted); font-size:0.78rem; margin-top:0.1rem;">${step.note}</div>`;
            html += `</td>`;
            html += `<td style="padding:0.5rem 0.5rem; text-align:right; border-bottom:1px solid rgba(255,255,255,0.05); color:${isLast ? 'var(--accent)' : 'var(--text)'}; white-space:nowrap;">`;
            html += fmtGbp(step.running);
            html += `</td></tr>`;
        });

        html += '</tbody></table>';

        if (data.override) {
            html = `<div style="background:rgba(168,85,247,0.1); border:1px solid rgba(168,85,247,0.3); border-radius:6px; padding:0.75rem 1rem; margin-bottom:1rem; font-size:0.875rem; color:#c084fc;">
                        Manual override — admin-set price, no formula applied.
                    </div>` + html;
        }

        html += `<div style="margin-top:1rem; padding-top:0.75rem; border-top:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                    <span style="color:var(--text-muted); font-size:0.85rem;">Final cash offer</span>
                    <span style="font-size:1.2rem; font-weight:700; color:var(--accent);">${fmtGbp(data.final)}</span>
                 </div>`;

        breakdownBody.innerHTML = html;
    }

    document.addEventListener('click', async function (e) {
        const cell = e.target.closest('.js-breakdown-trigger');
        if (! cell) return;

        const igdb     = cell.dataset.igdb;
        const platform = cell.dataset.platform;
        const tr       = cell.closest('tr');
        const gameTitle  = tr?.querySelector('td:nth-child(2)')?.textContent?.trim() ?? 'Game #' + igdb;
        const platformName = tr?.querySelector('.admin-td-muted')?.textContent?.trim() ?? '';

        overlay.style.display = 'flex';
        breakdownTitle.textContent = 'Price Breakdown';
        breakdownBody.innerHTML = '<p style="color:var(--text-muted); text-align:center; padding:1.5rem 0;">Loading…</p>';

        try {
            const url  = '{{ rtrim(url('/'), '/') }}/admin/game-prices/' + igdb + '/' + platform + '/breakdown';
            const resp = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await resp.json();
            renderBreakdown(data, gameTitle, platformName);
        } catch {
            breakdownBody.innerHTML = '<p style="color:var(--accent-2);">Failed to load breakdown.</p>';
        }
    });

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeBreakdown();
    });

    document.getElementById('breakdown-close')?.addEventListener('click', closeBreakdown);

    // ── Client-side price range filter ───────────────────────────────────────
    (function () {
        const minInput  = document.getElementById('price-min-filter');
        const maxInput  = document.getElementById('price-max-filter');
        const clearBtn  = document.getElementById('price-filter-clear');
        const countSpan = document.getElementById('price-filter-count');
        const tbody     = document.querySelector('#game-prices-table tbody');

        if (!minInput || !maxInput || !tbody) return;

        function applyFilter() {
            const minVal = minInput.value !== '' ? parseFloat(minInput.value) : null;
            const maxVal = maxInput.value !== '' ? parseFloat(maxInput.value) : null;
            const active = minVal !== null || maxVal !== null;

            let hidden = 0;
            tbody.querySelectorAll('tr').forEach(function (tr) {
                const cell = tr.querySelector('.js-display-price');
                if (!cell) { tr.style.display = ''; return; }

                const text = cell.textContent.trim();
                const num  = parseFloat(text.replace('£', ''));
                const valid = !isNaN(num);

                let show = true;
                if (active && valid) {
                    if (minVal !== null && num < minVal) show = false;
                    if (maxVal !== null && num > maxVal) show = false;
                } else if (active && !valid) {
                    // Row has no price (—); hide when filter is active
                    show = false;
                }

                tr.style.display = show ? '' : 'none';
                if (!show) hidden++;
            });

            if (clearBtn) clearBtn.style.display = active ? 'inline-flex' : 'none';
            if (countSpan) {
                countSpan.textContent = active && hidden > 0
                    ? hidden + ' row' + (hidden !== 1 ? 's' : '') + ' hidden'
                    : (active ? '' : '');
            }
        }

        minInput.addEventListener('input', applyFilter);
        maxInput.addEventListener('input', applyFilter);

        clearBtn?.addEventListener('click', function () {
            minInput.value = '';
            maxInput.value = '';
            applyFilter();
        });

        // Restore filter state from URL params on load
        const params = new URLSearchParams(window.location.search);
        const urlMin = params.get('price_min');
        const urlMax = params.get('price_max');
        if (urlMin !== null && urlMin !== '') { minInput.value = urlMin; }
        if (urlMax !== null && urlMax !== '') { maxInput.value = urlMax; }
        if (urlMin || urlMax) applyFilter();
    })();
})();
</script>
@endsection
