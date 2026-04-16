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

    <form method="POST" action="{{ route('admin.settings.update') }}" id="settings-form">
        @csrf

        {{-- Top row: Pricing + Condition Modifiers --}}
        <div class="settings-grid">

            {{-- Pricing --}}
            <div class="settings-card">
                <h2 class="settings-card__title">Pricing</h2>

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
                    <p class="settings-hint">The historical low (converted to GBP) is reduced by this percentage before display. Falls back to Steam price if unavailable.</p>
                    <div class="settings-input-row">
                        <input type="number" name="pricing_discount_percent"
                            value="{{ old('pricing_discount_percent', $settings['pricing_discount_percent']) }}"
                            min="0" max="99" step="1" class="form-input settings-input--sm">
                        <span class="settings-unit">%</span>
                    </div>
                    @error('pricing_discount_percent')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Age-Based Reduction (% per year)</label>
                    <p class="settings-hint">For each full year since release, reduce the price by this additional percentage. Max 20%.</p>
                    <div class="settings-input-row">
                        <input type="number" name="age_reduction_per_year"
                            value="{{ old('age_reduction_per_year', $settings['age_reduction_per_year']) }}"
                            min="0" max="20" step="0.5" class="form-input settings-input--sm">
                        <span class="settings-unit">% / year</span>
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
                Adjust the final price up or down based on the console. Positive % increases the price; negative % reduces it.
                For multi-platform games the most favourable modifier is applied.
            </p>
            <div class="settings-platforms-grid">
                @foreach($platforms as $platform)
                <div class="settings-platform-row">
                    <label class="form-label" style="margin:0;">{{ $platform['name'] }}</label>
                    <div class="settings-input-row">
                        <input type="number"
                            name="platform_modifier[{{ $platform['id'] }}]"
                            value="{{ old('platform_modifier.' . $platform['id'], $platform['modifier']) }}"
                            min="-99" max="99" step="1" class="form-input settings-input--sm">
                        <span class="settings-unit">%</span>
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
        @if(!empty($availableFranchises))
        <form method="POST" action="{{ route('admin.franchise-adjustments.store') }}"
              style="display:flex; align-items:flex-end; gap:0.75rem; flex-wrap:wrap;">
            @csrf
            <div class="form-group" style="flex:1; min-width:180px; margin:0;">
                <label class="form-label">Franchise</label>
                <select name="franchise_name" class="form-input">
                    <option value="" disabled selected>— Select franchise —</option>
                    @foreach($availableFranchises as $fname)
                    <option value="{{ $fname }}" {{ old('franchise_name') === $fname ? 'selected' : '' }}>{{ $fname }}</option>
                    @endforeach
                </select>
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
        @else
        <p style="color:var(--text-muted); font-size:0.875rem;">All configured franchises already have an adjustment.</p>
        @endif
    </div>

</div>
@endsection
