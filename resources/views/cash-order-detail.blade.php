@extends('layouts.app')
@section('title', 'Quote ' . $order->order_ref)

@section('content')
<div class="container" style="padding: 3rem 0 5rem; max-width: 720px;">

    <div style="margin-bottom:1.5rem;">
        <a href="{{ route('cash-orders.index') }}" style="color:var(--accent); font-size:0.9rem;">← My Submissions</a>
    </div>

    @if(session('flash_success'))
    <div class="alert alert--success" style="margin-bottom:1.5rem;">{{ session('flash_success') }}</div>
    @endif
    @if(session('flash_error'))
    <div class="alert alert--error" style="margin-bottom:1.5rem;">{{ session('flash_error') }}</div>
    @endif

    <div class="account-card" style="margin-bottom:1.5rem;">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
            <div>
                <h1 style="font-family:'Bebas Neue',sans-serif; font-size:1.8rem; letter-spacing:0.05em;">
                    {{ $order->order_ref }}
                </h1>
                <p style="color:var(--text-muted); font-size:0.85rem; margin-top:0.2rem;">
                    Submitted {{ $order->created_at->format('d M Y \a\t H:i') }}
                </p>
            </div>
            <span class="status-badge {{ $order->statusClass() }}" style="font-size:0.9rem; padding:0.35rem 0.9rem;">
                {{ $order->statusLabel() }}
            </span>
        </div>
    </div>

    <div class="account-card" style="margin-bottom:1.5rem;">
        <h2 style="font-size:1rem; font-weight:600; margin-bottom:1rem; color:var(--text-muted);">SUBMITTED GAMES</h2>
        @foreach($order->items as $item)
        <div class="co-detail-item">
            @if(!empty($item['cover_url']))
            <img src="{{ e($item['cover_url']) }}" alt="{{ e($item['game_title']) }}" class="co-detail-item__cover">
            @else
            <div class="co-detail-item__cover co-detail-item__cover--placeholder">🎮</div>
            @endif
            <div class="co-detail-item__body">
                <span class="co-detail-item__title">{{ e($item['game_title']) }}</span>
                @if(!empty($item['platform_name']))
                <span class="co-detail-item__platform">{{ $item['platform_name'] }}</span>
                @endif
            </div>
            <span class="co-detail-item__price">{{ $item['display_price'] ?? '—' }}</span>
        </div>
        @endforeach

        <div style="border-top:1px solid var(--border); margin-top:1rem; padding-top:1rem; display:flex; justify-content:space-between; align-items:center;">
            <span style="font-weight:600;">Estimated Total</span>
            <span style="font-size:1.3rem; font-weight:700; color:var(--accent-2);">
                £{{ number_format((float) $order->total_gbp, 2) }}
            </span>
        </div>
    </div>

    @if($order->address_line1)
    <div class="account-card" style="margin-bottom:1.5rem;">
        <h2 style="font-size:1rem; font-weight:600; margin-bottom:0.75rem; color:var(--text-muted);">PICKUP ADDRESS</h2>
        <address style="font-style:normal; line-height:1.7; font-size:0.92rem;">
            {{ e($order->house_name_number) }}, {{ e($order->address_line1) }}<br>
            @if($order->address_line2)
            {{ e($order->address_line2) }}<br>
            @endif
            @if($order->address_line3)
            {{ e($order->address_line3) }}<br>
            @endif
            {{ e($order->city) }}{{ $order->county ? ', ' . e($order->county) : '' }}<br>
            {{ e($order->postcode) }}
        </address>
    </div>
    @endif

    @if($order->status === 'pending')
    <div class="account-card" style="background: rgba(249,168,38,0.06); border-color: rgba(249,168,38,0.3);">
        <p style="color:var(--text-muted); font-size:0.9rem; margin:0 0 0.75rem;">
            <strong style="color:var(--rating-mid);">Pending review</strong> —
            We'll contact you shortly by phone or email with payment details and next steps.
            Quote your reference <strong>{{ $order->order_ref }}</strong> if you need to get in touch.
        </p>
        @if($order->canCancel())
        @php $minsLeft = $order->cancelMinutesRemaining(); $hrsLeft = floor($minsLeft / 60); $minsRem = $minsLeft % 60; @endphp
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem; padding-top:0.75rem; border-top:1px solid rgba(249,168,38,0.2);">
            <p style="font-size:0.82rem; color:var(--text-muted); margin:0;">
                You can cancel this order for the next
                <strong style="color:var(--text);">{{ $hrsLeft > 0 ? $hrsLeft . 'h ' : '' }}{{ $minsRem }}m</strong>.
                After that it cannot be cancelled.
            </p>
            <form method="POST" action="{{ route('cash-orders.cancel', $order->order_ref) }}">
                @csrf
                <button type="button"
                    class="btn btn--sm"
                    style="background:rgba(230,57,70,0.1); color:var(--accent); border:1px solid rgba(230,57,70,0.3);"
                    data-confirm="Cancel order {{ $order->order_ref }}? This cannot be undone.">
                    Cancel Order
                </button>
            </form>
        </div>
        @else
        <p style="font-size:0.82rem; color:var(--text-muted); margin:0.5rem 0 0; padding-top:0.75rem; border-top:1px solid rgba(249,168,38,0.2);">
            The 2-hour cancellation window has passed. To discuss this order please <a href="{{ route('contact') }}" style="color:var(--accent);">contact us</a>.
        </p>
        @endif
    </div>
    @endif

</div>
@endsection
