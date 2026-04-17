@extends('layouts.app')
@section('title', 'Site Settings')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Site Settings</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.dashboard') }}" style="color:var(--accent);">← Dashboard</a></p>
        </div>
        <button type="submit" form="settings-form" class="btn btn--primary">Save Settings</button>
    </div>

    {{-- Pricing Formula Explainer --}}
    <div class="settings-card settings-card--wide" style="margin-bottom:1.5rem; border-left:3px solid var(--accent);">
        <h2 class="settings-card__title">How Pricing Works</h2>
        <p class="settings-hint" style="margin-bottom:1.25rem;">
            This explains how the cash offer shown to customers is calculated, and what happens when you change each setting.
        </p>

        <div style="display:flex; flex-direction:column; gap:1rem; font-size:0.92rem; color:var(--text);">

            <div>
                <strong>Step 1 — Find the base price</strong>
                <p class="settings-hint" style="margin-top:0.25rem;">
                    The system first checks <strong>CeX</strong> for a real-world cash buy price for this game and platform.
                    CeX prices are fetched live and cached for 24 hours; see the <em>CeX Pricing</em> setting to control the margin applied.
                    If CeX has no data, it falls back to the current <strong>Steam GBP price</strong>, then to
                    <strong>CheapShark</strong> (all-time historical lowest, in USD — converted using the
                    <strong>USD → GBP Exchange Rate</strong>), and finally to the <strong>Base Price (GBP)</strong> setting as a last resort.
                </p>
            </div>

            <div>
                <strong>Step 2 — Apply margin or discount</strong>
                <p class="settings-hint" style="margin-top:0.25rem;">
                    <strong>CeX path:</strong> the CeX cash price is multiplied by the <strong>CeX Margin %</strong> (e.g. 90% means
                    we offer slightly below what CeX pays). Platform modifiers are skipped because CeX prices are already platform-specific.<br>
                    <strong>Fallback path:</strong> the franchise adjustment is added to the base, then the <strong>Discount %</strong>
                    is applied, then the platform modifier.
                    Manage franchise adjustments at the bottom of this page.
                </p>
            </div>

            <div>
                <strong>Step 3 — Apply the discount (fallback only)</strong>
                <p class="settings-hint" style="margin-top:0.25rem;">
                    When not using CeX data, the (franchise-adjusted) base price is multiplied by <code>(100% − Discount%)</code>.
                    At 85% discount, only <strong>15%</strong> of the base price remains.
                    This setting has no effect on games priced via CeX.
                </p>
            </div>

            <div>
                <strong>Step 4 — Apply the platform modifier</strong>
                <p class="settings-hint" style="margin-top:0.25rem;">
                    An adjustment is applied based on the console — either a flat <strong>£ amount</strong> added
                    or subtracted, or a <strong>% multiplier</strong> applied to the discounted price.
                    A positive value increases the offer (e.g. PS5 games may be worth more);
                    a negative value reduces it. Set to 0 to leave a platform's price unchanged.
                    Each console is listed individually in the Get Cash dropdown with its own adjusted price —
                    so Xbox, Xbox 360, and Xbox One will each show a different offer if their modifiers differ.
                </p>
            </div>

            <div>
                <strong>Step 5 — Apply the age-based reduction</strong>
                <p class="settings-hint" style="margin-top:0.25rem;">
                    For each full year since the game's release date, a flat <strong>£ amount is deducted</strong>
                    from the price. At £0.50/year, a game released 10 years ago loses £5.00 from its computed price.
                    The price is always floored at £0.01 so it never goes negative.
                    Set this to 0 to disable age-based reductions entirely.
                </p>
            </div>

            <div>
                <strong>Step 6 — Low-price boost</strong>
                <p class="settings-hint" style="margin-top:0.25rem;">
                    If the price after all the above steps is still <strong>less than £0.10</strong>,
                    the <strong>Low-Price Boost (£)</strong> amount is added to keep the offer meaningful.
                    This most often affects very old games whose Steam/CheapShark price is extremely low.
                    Set to 0 to disable this boost entirely.
                </p>
            </div>

            <div>
                <strong>Step 7 — Bundle bonus</strong>
                <p class="settings-hint" style="margin-top:0.25rem;">
                    If the game is flagged as a <strong>bundle</strong> in the IGDB database (i.e. it contains
                    multiple games), a flat <strong>£ amount is added</strong> to the computed price.
                    This reflects the extra value of a multi-game package.
                    Set to 0 to leave bundle prices unchanged.
                </p>
            </div>

            <div>
                <strong>Step 8 — High-price reduction</strong>
                <p class="settings-hint" style="margin-top:0.25rem;">
                    If the price after step 6 is <strong>greater than £10.00</strong>, it is reduced by the
                    <strong>High-Price Reduction %</strong>. This keeps offers on premium or recent titles
                    from being too generous. Set to 0 to disable.
                </p>
            </div>

            <div>
                <strong>Step 9 — Condition modifier (applied at quote time)</strong>
                <p class="settings-hint" style="margin-top:0.25rem;">
                    When a customer selects the physical condition of their game in the cash basket,
                    a final percentage adjustment is applied on top of the computed price.
                    <em>Brand New</em> increases the offer; <em>Just Disk</em> reduces it.
                    This is the last step and is shown live in the basket before the customer submits their quote.
                </p>
            </div>

            <div style="background:rgba(255,255,255,0.04); border-radius:6px; padding:0.75rem 1rem; font-family:monospace; font-size:0.85rem; color:var(--text-muted);">
                <em style="color:var(--accent);">When CeX data is available for this platform:</em>
                <br>offer = cex_cash_price × cex_margin%
                <br><br><em style="color:var(--text-dim);">Fallback (no CeX data):</em>
                <br>base = steam_gbp &nbsp;OR&nbsp; (cheapshark_usd ÷ rate) &nbsp;OR&nbsp; base_price_gbp
                <br>base += franchise_adj
                <br>offer = base × (1 − discount%) [× (1 + platform%) &nbsp;OR&nbsp; + platform_£]
                <br><br><em style="color:var(--text-dim);">Both paths then apply:</em>
                <br>offer −= age_years × £age_reduction
                <br>if offer &lt; £0.10 → offer += £low_boost
                <br>if is_bundle → offer += £bundle_gbp
                <br>if offer &gt; £10.00 → offer × (1 − high_price%)
                <br>final = offer × (1 + condition%)
            </div>

        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" id="settings-form">
        @csrf

        {{-- Top row: Pricing + Condition Modifiers --}}
        <div class="settings-grid">

            {{-- Pricing --}}
            <div class="settings-card">
                <h2 class="settings-card__title">Pricing</h2>

                <div class="form-group">
                    <label class="form-label">CeX Margin (%)</label>
                    <p class="settings-hint">Percentage of the CeX cash buy price to offer. 90% means we offer slightly below what CeX pays. Only applies when CeX data is available for the game.</p>
                    <div class="settings-input-row">
                        <input type="number" name="cex_margin_pct"
                            value="{{ old('cex_margin_pct', $settings['cex_margin_pct']) }}"
                            min="1" max="150" step="1" class="form-input settings-input--sm">
                        <span class="settings-unit">%</span>
                    </div>
                    @error('cex_margin_pct')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">USD → GBP Exchange Rate</label>
                    <p class="settings-hint">How many US dollars equal one British pound (e.g. 1.36 means $1.36 = £1).</p>
                    <div class="settings-input-row">
                        <span class="settings-unit">$1 USD =</span>
                        <input type="number" name="usd_to_gbp_rate"
                            value="{{ old('usd_to_gbp_rate', $settings['usd_to_gbp_rate']) }}"
                            min="0.01" max="99.99" step="0.01" class="form-input settings-input--sm">
                        <span class="settings-unit">GBP</span>
                    </div>
                    @error('usd_to_gbp_rate')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Discount Applied to Prices (%)</label>
                    <p class="settings-hint">The Steam price (or CheapShark historical low if no Steam data) is reduced by this percentage to produce the cash offer.</p>
                    <div class="settings-input-row">
                        <input type="number" name="pricing_discount_percent"
                            value="{{ old('pricing_discount_percent', $settings['pricing_discount_percent']) }}"
                            min="0" max="99" step="1" class="form-input settings-input--sm">
                        <span class="settings-unit">%</span>
                    </div>
                    @error('pricing_discount_percent')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Age-Based Reduction (£ per year)</label>
                    <p class="settings-hint">For each full year since release, deduct this flat amount from the price. Set to 0 to disable.</p>
                    <div class="settings-input-row">
                        <span class="settings-unit">£</span>
                        <input type="number" name="age_reduction_per_year"
                            value="{{ old('age_reduction_per_year', $settings['age_reduction_per_year']) }}"
                            min="0" max="9.99" step="0.01" class="form-input settings-input--sm">
                        <span class="settings-unit">/ year</span>
                    </div>
                    @error('age_reduction_per_year')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Base Price (GBP)</label>
                    <p class="settings-hint">Fallback price for games with no Steam or historical low. Set to <strong>0</strong> to disable.</p>
                    <div class="settings-input-row">
                        <span class="settings-unit">£</span>
                        <input type="number" name="base_price_gbp"
                            value="{{ old('base_price_gbp', $settings['base_price_gbp']) }}"
                            min="0" max="999.99" step="0.01" class="form-input settings-input--sm">
                    </div>
                    @error('base_price_gbp')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Bundle Price Increase (£)</label>
                    <p class="settings-hint">If the game is a bundle (multiple games in one), add this flat amount to the computed price. Set to <strong>0</strong> to disable.</p>
                    <div class="settings-input-row">
                        <span class="settings-unit">£</span>
                        <input type="number" name="bundle_price_increase_gbp"
                            value="{{ old('bundle_price_increase_gbp', $settings['bundle_price_increase_gbp']) }}"
                            min="0" max="999.99" step="0.01" class="form-input settings-input--sm">
                    </div>
                    @error('bundle_price_increase_gbp')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Low-Price Boost (£)</label>
                    <p class="settings-hint">If the computed price is <strong>less than £0.10</strong>, add this amount to keep the offer meaningful. Set to <strong>0</strong> to disable.</p>
                    <div class="settings-input-row">
                        <span class="settings-unit">£</span>
                        <input type="number" name="low_price_boost_gbp"
                            value="{{ old('low_price_boost_gbp', $settings['low_price_boost_gbp']) }}"
                            min="0" max="99.99" step="0.01" class="form-input settings-input--sm">
                    </div>
                    @error('low_price_boost_gbp')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">High-Price Reduction (%)</label>
                    <p class="settings-hint">If the computed price exceeds <strong>£10.00</strong>, reduce it by this percentage. Set to <strong>0</strong> to disable.</p>
                    <div class="settings-input-row">
                        <input type="number" name="high_price_reduction_pct"
                            value="{{ old('high_price_reduction_pct', $settings['high_price_reduction_pct']) }}"
                            min="0" max="99" step="1" class="form-input settings-input--sm">
                        <span class="settings-unit">%</span>
                    </div>
                    @error('high_price_reduction_pct')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Minimum Order Value (GBP)</label>
                    <p class="settings-hint">Users must reach this total before submitting a quote. Set to <strong>0</strong> to remove the minimum.</p>
                    <div class="settings-input-row">
                        <span class="settings-unit">£</span>
                        <input type="number" name="min_order_gbp"
                            value="{{ old('min_order_gbp', $settings['min_order_gbp']) }}"
                            min="0" max="999.99" step="0.01" class="form-input settings-input--sm">
                    </div>
                    @error('min_order_gbp')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Game Condition Modifiers --}}
            <div class="settings-card">
                <h2 class="settings-card__title">Condition Modifiers</h2>
                <p class="settings-hint" style="margin-bottom:1.5rem;">
                    Adjust the cash value based on the condition the customer reports.
                    Positive % increases the price; negative % reduces it.
                </p>

                @foreach([
                    ['key' => 'condition_new_pct',      'label' => '💎 Brand New',          'hint' => 'Case sealed, never opened'],
                    ['key' => 'condition_complete_pct', 'label' => '✅ Complete (In Case)',   'hint' => 'Case, manual & disc present'],
                    ['key' => 'condition_disk_pct',     'label' => '💿 Just Disk',            'hint' => 'Disc only, no case or manual'],
                ] as $cond)
                <div class="form-group settings-condition-row">
                    <div>
                        <label class="form-label">{{ $cond['label'] }}</label>
                        <p class="settings-hint">{{ $cond['hint'] }}</p>
                    </div>
                    <div class="settings-input-row">
                        <input type="number" name="{{ $cond['key'] }}"
                            value="{{ old($cond['key'], $settings[$cond['key']]) }}"
                            min="-100" max="100" step="1" class="form-input settings-input--sm">
                        <span class="settings-unit">%</span>
                    </div>
                    @error($cond['key'])<p class="form-error">{{ $message }}</p>@enderror
                </div>
                @endforeach
            </div>

        </div>

        {{-- Console / Platform Modifiers --}}
        <div class="settings-card settings-card--wide" style="margin-top:1.5rem;">
            <h2 class="settings-card__title">Console Price Modifiers</h2>
            <p class="settings-hint" style="margin-bottom:1.5rem;">
                Each console has its own modifier. Positive % increases the cash offer; negative % reduces it.
                In the Get Cash dropdown, every console is listed separately with its own adjusted price —
                Xbox, Xbox 360, and Xbox One will each show a different offer if their modifiers differ.
            </p>
            <div class="settings-platforms-grid">
                @foreach($platforms as $platform)
                @php $isGbp = old('platform_modifier_type.' . $platform['id'], $platform['modifier_type']) === 'gbp'; @endphp
                <div class="settings-platform-row">
                    <label class="form-label">{{ $platform['name'] }}</label>
                    <div class="platform-modifier-control">
                        <select name="platform_modifier_type[{{ $platform['id'] }}]"
                                onchange="
                                    var inp = this.nextElementSibling;
                                    if (this.value === 'gbp') {
                                        inp.min = -999.99; inp.max = 999.99; inp.step = 0.01;
                                    } else {
                                        inp.min = -99; inp.max = 99; inp.step = 1;
                                    }
                                ">
                            <option value="percent" @selected(!$isGbp)>%</option>
                            <option value="gbp"     @selected($isGbp)>£</option>
                        </select>
                        <input type="number"
                            name="platform_modifier[{{ $platform['id'] }}]"
                            value="{{ old('platform_modifier.' . $platform['id'], $platform['modifier']) }}"
                            min="{{ $isGbp ? -999.99 : -99 }}"
                            max="{{ $isGbp ? 999.99 : 99 }}"
                            step="{{ $isGbp ? '0.01' : '1' }}"
                            class="form-input">
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </form>

    {{-- Franchise Price Adjustments --}}
    <div class="settings-card settings-card--wide" style="margin-top:1.5rem;">
        <h2 class="settings-card__title">Franchise Price Adjustments</h2>
        <p class="settings-hint" style="margin-bottom:1.5rem;">
            Add or deduct a flat £ amount from games belonging to a specific franchise.
            Positive values increase the cash offer; negative values reduce it.
        </p>

        {{-- Existing adjustments --}}
        @if($franchiseAdjustments->isNotEmpty())
        <div style="display:flex; flex-direction:column; gap:0.5rem; margin-bottom:1.5rem;">
            @foreach($franchiseAdjustments as $fa)
            <form method="POST" action="{{ route('admin.franchise-adjustments.update', $fa->id) }}"
                  style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
                @csrf
                @method('PATCH')
                <span style="flex:1; min-width:160px; font-weight:500; color:var(--text);">{{ $fa->franchise_name }}</span>
                <div class="settings-input-row">
                    <span class="settings-unit">£</span>
                    <input type="number" name="adjustment_gbp"
                           value="{{ $fa->adjustment_gbp }}"
                           min="-999.99" max="999.99" step="0.01"
                           class="form-input settings-input--sm"
                           style="width:90px;">
                </div>
                <button type="submit" class="btn btn--outline btn--sm">Save</button>
                <button type="button"
                    class="btn btn--sm" style="background:rgba(230,57,70,0.12); color:var(--accent); border:1px solid rgba(230,57,70,0.3);"
                    data-confirm="Remove franchise adjustment for &quot;{{ e($fa->franchise_name) }}&quot;?"
                    onclick="this.closest('form').querySelector('[name=_method]').value='DELETE'; this.closest('form').action='{{ route('admin.franchise-adjustments.destroy', $fa->id) }}'; this.closest('form').submit();">
                    Remove
                </button>
            </form>
            @endforeach
        </div>
        @endif

        {{-- Add new --}}
        @php
            $usedFranchises = $franchiseAdjustments->pluck('franchise_name')->toArray();
            $availableFranchises = array_diff(config('igdb.franchises'), $usedFranchises);
        @endphp
        <form method="POST" action="{{ route('admin.franchise-adjustments.store') }}"
              style="display:flex; align-items:flex-end; gap:0.75rem; flex-wrap:wrap;">
            @csrf
            <div class="form-group" style="flex:1; min-width:180px; margin:0;">
                <label class="form-label">Franchise</label>
                <input type="text" name="franchise_name" value="{{ old('franchise_name') }}"
                       class="form-input" placeholder="e.g. Call of Duty"
                       list="franchise-suggestions" autocomplete="off">
                <datalist id="franchise-suggestions">
                    @foreach($availableFranchises as $fname)
                    <option value="{{ $fname }}">
                    @endforeach
                </datalist>
                @error('franchise_name')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group" style="margin:0;">
                <label class="form-label">Adjustment (£)</label>
                <div class="settings-input-row">
                    <span class="settings-unit">£</span>
                    <input type="number" name="adjustment_gbp" value="{{ old('adjustment_gbp', '0.00') }}"
                           min="-999.99" max="999.99" step="0.01"
                           class="form-input settings-input--sm" style="width:90px;">
                </div>
                @error('adjustment_gbp')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn btn--primary btn--sm" style="margin-bottom:1px;">Add Franchise</button>
        </form>
    </div>

    {{-- CeX Priced Games --}}
    <div class="settings-card settings-card--wide" style="margin-top:1.5rem;">
        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1.5rem;">
            <div>
                <h2 class="settings-card__title" style="margin-bottom:0.25rem;">Games with CeX Prices ({{ $cexGames->count() }})</h2>
                <p class="settings-hint">
                    Games listed here are priced using live CeX cash buy data. Click <strong>Sync Now</strong> to fetch prices
                    for all known games at once. Prices refresh automatically every 24 hours during normal browsing.
                </p>
            </div>
            <form method="POST" action="{{ route('admin.sync-cex-prices') }}" style="flex-shrink:0;">
                @csrf
                <button type="submit" class="btn btn--primary btn--sm"
                    data-confirm="This will fetch CeX prices for all known games. It may take a minute — proceed?">
                    Sync CeX Now
                </button>
            </form>
        </div>

        @if($cexGames->isEmpty())
        <p style="color:var(--text-dim); padding:0.5rem 0;">No CeX prices yet. Click <strong>Sync CeX Now</strong> above to fetch prices for all known games.</p>
        @else
        @php $allPlatforms = config('igdb.all_platforms'); @endphp
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Game</th>
                        <th>CeX Platforms &amp; Cash Prices</th>
                        <th>Fetched</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cexGames as $gp)
                    @php
                        $gameSlug  = $gp->slug ?? null;
                        $gameName  = $gameSlug
                            ? ucwords(str_replace('-', ' ', $gameSlug))
                            : 'Game #' . $gp->igdb_game_id;
                        $gameUrl   = $gameSlug
                            ? route('game.show', ['slug' => $gameSlug])
                            : url('/game/' . $gp->igdb_game_id);
                        $prices    = $gp->cex_prices ?? [];
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ $gameUrl }}" style="color:var(--accent); text-decoration:none;" target="_blank">
                                {{ $gameName }}
                            </a>
                        </td>
                        <td>
                            <div style="display:flex; flex-wrap:wrap; gap:0.4rem;">
                                @foreach($prices as $platformId => $priceData)
                                @php $platformName = $allPlatforms[$platformId] ?? 'Platform ' . $platformId; @endphp
                                <span style="display:inline-flex; align-items:center; gap:0.3rem; background:rgba(255,255,255,0.06); border:1px solid var(--border); border-radius:4px; padding:0.2rem 0.5rem; font-size:0.8rem; white-space:nowrap;">
                                    {{ $platformName }}
                                    <strong style="color:var(--accent-2);">£{{ number_format($priceData['cash'], 2) }}</strong>
                                </span>
                                @endforeach
                            </div>
                        </td>
                        <td style="color:var(--text-muted); font-size:0.82rem; white-space:nowrap;">
                            {{ $gp->cex_fetched_at ? $gp->cex_fetched_at->diffForHumans() : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>
@endsection
