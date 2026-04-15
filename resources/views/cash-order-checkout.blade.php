@extends('layouts.app')
@section('title', 'Confirm Pickup Address')

@section('content')
<div class="container" style="padding: 3rem 0 5rem;">

    <div style="margin-bottom:1.5rem;">
        <a href="{{ route('cash-basket.index') }}" style="color:var(--accent); font-size:0.9rem;">← Back to Basket</a>
    </div>

    <div class="page-header" style="margin-bottom:2rem;">
        <h1 class="section-title" style="font-size:2rem;">Confirm Pickup Address</h1>
        <p style="color:var(--text-muted); margin-top:0.4rem;">
            Let us know where to collect your games from.
        </p>
    </div>

    <div class="checkout-layout">

        {{-- Address form --}}
        <div class="checkout-layout__form">
            <form method="POST" action="{{ route('cash-orders.store') }}">
                @csrf

                <div class="account-card">
                    <h2 style="font-size:1rem; font-weight:600; margin-bottom:1.25rem; color:var(--text-muted);">PICKUP ADDRESS</h2>

                    <div class="form-group">
                        <label class="form-label" for="house_name_number">House Name or Number <span style="color:var(--accent);">*</span></label>
                        <input type="text"
                            id="house_name_number"
                            name="house_name_number"
                            value="{{ old('house_name_number') }}"
                            class="form-input{{ $errors->has('house_name_number') ? ' form-input--error' : '' }}"
                            placeholder="e.g. 12 or Elm Cottage"
                            maxlength="100"
                            required>
                        @error('house_name_number')
                        <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="address_line1">Address Line 1 <span style="color:var(--accent);">*</span></label>
                        <input type="text"
                            id="address_line1"
                            name="address_line1"
                            value="{{ old('address_line1') }}"
                            class="form-input{{ $errors->has('address_line1') ? ' form-input--error' : '' }}"
                            placeholder="Street name"
                            maxlength="150"
                            required>
                        @error('address_line1')
                        <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="address_line2">Address Line 2 <span style="color:var(--text-dim);">(optional)</span></label>
                        <input type="text"
                            id="address_line2"
                            name="address_line2"
                            value="{{ old('address_line2') }}"
                            class="form-input{{ $errors->has('address_line2') ? ' form-input--error' : '' }}"
                            placeholder=""
                            maxlength="150">
                        @error('address_line2')
                        <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="address_line3">Address Line 3 <span style="color:var(--text-dim);">(optional)</span></label>
                        <input type="text"
                            id="address_line3"
                            name="address_line3"
                            value="{{ old('address_line3') }}"
                            class="form-input{{ $errors->has('address_line3') ? ' form-input--error' : '' }}"
                            placeholder=""
                            maxlength="150">
                        @error('address_line3')
                        <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                        <div class="form-group">
                            <label class="form-label" for="city">City / Town <span style="color:var(--accent);">*</span></label>
                            <input type="text"
                                id="city"
                                name="city"
                                value="{{ old('city') }}"
                                class="form-input{{ $errors->has('city') ? ' form-input--error' : '' }}"
                                placeholder="e.g. Manchester"
                                maxlength="100"
                                required>
                            @error('city')
                            <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="county">County <span style="color:var(--text-dim);">(optional)</span></label>
                            <input type="text"
                                id="county"
                                name="county"
                                value="{{ old('county') }}"
                                class="form-input{{ $errors->has('county') ? ' form-input--error' : '' }}"
                                placeholder="e.g. Greater Manchester"
                                maxlength="100">
                            @error('county')
                            <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group" style="max-width:200px;">
                        <label class="form-label" for="postcode">Postcode <span style="color:var(--accent);">*</span></label>
                        <input type="text"
                            id="postcode"
                            name="postcode"
                            value="{{ old('postcode') }}"
                            class="form-input{{ $errors->has('postcode') ? ' form-input--error' : '' }}"
                            placeholder="e.g. M1 1AE"
                            maxlength="20"
                            style="text-transform:uppercase;"
                            required>
                        @error('postcode')
                        <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <p style="font-size:0.8rem; color:var(--text-dim); margin-top:0.75rem;">
                        Fields marked <span style="color:var(--accent);">*</span> are required.
                    </p>
                </div>

                <div class="form-group checkout-agreements">
                    <label class="checkout-agreement{{ $errors->has('agreed_terms') ? ' checkout-agreement--error' : '' }}">
                        <input type="checkbox" name="agreed_terms" value="1" {{ old('agreed_terms') ? 'checked' : '' }}>
                        <span>
                            I have read and agree to the
                            <a href="{{ route('terms') }}" target="_blank" style="color:var(--accent-2);">Terms &amp; Conditions</a>.
                        </span>
                    </label>
                    @error('agreed_terms')
                    <p class="form-error" style="margin-top:0.25rem;">{{ $message }}</p>
                    @enderror

                    <label class="checkout-agreement{{ $errors->has('confirmed_contents') ? ' checkout-agreement--error' : '' }}" style="margin-top:0.65rem;">
                        <input type="checkbox" name="confirmed_contents" value="1" {{ old('confirmed_contents') ? 'checked' : '' }}>
                        <span>I confirm that all items listed above are in my possession and ready for collection.</span>
                    </label>
                    @error('confirmed_contents')
                    <p class="form-error" style="margin-top:0.25rem;">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn--primary" style="width:100%; margin-top:1.25rem; padding:0.9rem;">
                    Submit Quote — {{ $totalFormatted }}
                </button>
            </form>
        </div>

        {{-- Order summary sidebar --}}
        <div class="checkout-layout__summary">
            <div class="cb-summary__card">
                <h2 class="cb-summary__title">Order Summary</h2>
                <div class="cb-summary__total">{{ $totalFormatted }}</div>
                <p class="cb-summary__note">{{ count($orderItems) }} {{ count($orderItems) === 1 ? 'game' : 'games' }}</p>

                <div style="margin-top:1rem; border-top:1px solid var(--border); padding-top:1rem;">
                    @foreach($orderItems as $item)
                    <div class="checkout-summary-item">
                        @if(!empty($item['cover_url']))
                        <img src="{{ e($item['cover_url']) }}" alt="{{ e($item['game_title']) }}" class="checkout-summary-item__cover">
                        @else
                        <div class="checkout-summary-item__cover checkout-summary-item__cover--placeholder">🎮</div>
                        @endif
                        <div class="checkout-summary-item__body">
                            <span class="checkout-summary-item__title">{{ e($item['game_title']) }}</span>
                            @if(!empty($item['platform_name']))
                            <span class="checkout-summary-item__platform">{{ $item['platform_name'] }}</span>
                            @endif
                        </div>
                        <span class="checkout-summary-item__price">{{ $item['display_price'] ?? '—' }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
