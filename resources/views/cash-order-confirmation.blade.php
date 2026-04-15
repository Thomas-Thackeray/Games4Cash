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
                <img src="{{ e($item['cover_url']) }}" alt="{{ e($item['game_title']) }}" class="order-confirmation__item-cover">
                @else
                <div class="order-confirmation__item-cover order-confirmation__item-cover--placeholder">🎮</div>
                @endif
                <div class="order-confirmation__item-info">
                    <span class="order-confirmation__item-title">{{ e($item['game_title']) }}</span>
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

        <div class="order-confirmation__actions">
            <a href="{{ route('cash-orders.index') }}" class="btn btn--outline">View My Submissions</a>
            <a href="{{ route('search') }}" class="btn btn--primary">Continue Browsing</a>
        </div>

    </div>

</div>
@endsection
