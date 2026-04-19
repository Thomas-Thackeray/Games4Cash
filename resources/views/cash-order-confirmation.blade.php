@extends('layouts.app')
@section('title', 'Quote Submitted — ' . $order->order_ref)

@section('content')
<div class="container" style="padding: 4rem 0 6rem; max-width: 680px;">

    <div class="order-confirmation">

        <div class="order-confirmation__icon">✓</div>

        <h1 class="order-confirmation__heading">Quote Submitted!</h1>
        <p class="order-confirmation__sub">
            Thank you for your Get Cash submission. We'll be in touch shortly
            by <strong>phone or email</strong> with payment details and next steps.
        </p>

        <div class="order-confirmation__ref-card">
            <span class="order-confirmation__ref-label">Quote Reference</span>
            <span class="order-confirmation__ref">{{ $order->order_ref }}</span>
            <span class="order-confirmation__ref-note">Keep this reference handy — quote it if you contact us.</span>
        </div>

        <div class="order-confirmation__summary">
            <div class="order-confirmation__total-row">
                <span>Estimated Total Value</span>
                <span class="order-confirmation__total-value">£{{ number_format((float) $order->total_gbp, 2) }}</span>
            </div>
            <div class="order-confirmation__total-row order-confirmation__total-row--sub">
                <span>Games submitted</span>
                <span>{{ count($order->items) }}</span>
            </div>
            @if($order->address_line1)
            <div style="border-top:1px solid var(--border); margin-top:0.75rem; padding-top:0.75rem; font-size:0.82rem; color:var(--text-muted); text-align:left;">
                <strong style="color:var(--text);">Pickup address:</strong><br>
                {{ e($order->house_name_number) }}, {{ e($order->address_line1) }}{{ $order->address_line2 ? ', ' . e($order->address_line2) : '' }}{{ $order->address_line3 ? ', ' . e($order->address_line3) : '' }}<br>
                {{ e($order->city) }}{{ $order->county ? ', ' . e($order->county) : '' }}<br>
                {{ e($order->postcode) }}
            </div>
            @endif
        </div>

        <div class="order-confirmation__items">
            @foreach($order->items as $item)
            <div class="order-confirmation__item">
                @if(!empty($item['cover_url']))
                <img src="{{ $item['cover_url'] }}" alt="{{ $item['game_title'] }}" class="order-confirmation__item-cover">
                @else
                <div class="order-confirmation__item-cover order-confirmation__item-cover--placeholder">🎮</div>
                @endif
                <div class="order-confirmation__item-info">
                    <span class="order-confirmation__item-title">{{ $item['game_title'] }}</span>
                    @if(!empty($item['platform_name']))
                    <span class="order-confirmation__item-platform">{{ $item['platform_name'] }}</span>
                    @endif
                </div>
                <span class="order-confirmation__item-price">
                    {{ $item['display_price'] ?? '—' }}
                </span>
            </div>
            @endforeach
        </div>

        {{-- Newsletter opt-in --}}
        <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); padding:1.5rem; margin-bottom:1.5rem; text-align:center;">
            <div style="font-size:1.5rem; margin-bottom:0.5rem;">📬</div>
            <h3 style="font-size:1rem; font-weight:700; margin:0 0 0.4rem;">Want to know when new games arrive?</h3>
            <p style="font-size:0.85rem; color:var(--text-muted); margin:0 0 1rem;">Subscribe to our newsletter and be first to hear about new trade-in titles and price updates.</p>
            <form method="POST" action="{{ route('newsletter.subscribe') }}" style="display:flex; gap:0.5rem; justify-content:center; flex-wrap:wrap;">
                @csrf
                <input type="hidden" name="source" value="order">
                <input type="hidden" name="name" value="{{ auth()->user()->first_name . ' ' . auth()->user()->surname }}">
                <input type="email" name="email" value="{{ auth()->user()->email }}" required
                       style="flex:1; min-width:200px; max-width:280px; padding:0.6rem 0.9rem; border:1px solid var(--border); border-radius:var(--radius); background:var(--bg); color:var(--text); font-family:inherit; font-size:0.9rem;">
                <button type="submit" class="btn btn--primary btn--sm">Subscribe</button>
            </form>
        </div>

        <div class="order-confirmation__actions">
            <a href="{{ route('cash-orders.index') }}" class="btn btn--outline">View My Submissions</a>
            <a href="{{ route('search') }}" class="btn btn--primary">Continue Browsing</a>
        </div>

    </div>

</div>
@endsection
