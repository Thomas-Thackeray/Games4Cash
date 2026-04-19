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

    {{-- ===== ORDER TRACKING TIMELINE ===== --}}
    @php $steps = $order->trackingSteps(); @endphp
    @if(!empty($steps))
    <div class="account-card" style="margin-bottom:1.5rem;">
        <h2 style="font-size:1rem; font-weight:600; margin-bottom:1.25rem; color:var(--text-muted);">ORDER PROGRESS</h2>
        <div style="display:flex; flex-direction:column; gap:0;">
            @foreach($steps as $i => $step)
            @php $isLast = $i === count($steps) - 1; @endphp
            <div style="display:flex; gap:1rem; align-items:flex-start;">
                {{-- Icon + line --}}
                <div style="display:flex; flex-direction:column; align-items:center; flex-shrink:0;">
                    <div style="
                        width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center;
                        font-size:0.85rem; font-weight:700; flex-shrink:0;
                        {{ $step['done'] ? 'background:var(--accent); color:#fff;' : ($step['active'] ? 'background:var(--accent-2); color:#fff; box-shadow:0 0 0 4px rgba(var(--accent-2-rgb,249,168,38),0.2);' : 'background:var(--card-bg); border:2px solid var(--border); color:var(--text-dim);') }}
                    ">
                        @if($step['done']) ✓
                        @elseif($step['active']) {{ $i + 1 }}
                        @else {{ $i + 1 }}
                        @endif
                    </div>
                    @if(!$isLast)
                    <div style="width:2px; height:32px; {{ $step['done'] ? 'background:var(--accent);' : 'background:var(--border);' }} margin:2px 0;"></div>
                    @endif
                </div>
                {{-- Label + desc --}}
                <div style="padding-top:6px; padding-bottom:{{ $isLast ? '0' : '0.75rem' }};">
                    <p style="font-weight:{{ $step['active'] ? '700' : '600' }}; font-size:0.9rem; margin:0; color:{{ $step['done'] ? 'var(--text-muted)' : ($step['active'] ? 'var(--text)' : 'var(--text-dim)') }};">
                        {{ $step['label'] }}
                        @if($step['active'])<span style="font-size:0.72rem; font-weight:500; background:rgba(249,168,38,0.12); color:var(--accent-2); border-radius:4px; padding:1px 6px; margin-left:6px; vertical-align:middle;">Current</span>@endif
                    </p>
                    <p style="font-size:0.8rem; color:var(--text-dim); margin:0.15rem 0 0;">{{ $step['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @elseif($order->status === 'cancelled')
    <div class="account-card" style="margin-bottom:1.5rem; border-color:rgba(230,57,70,0.3); background:rgba(230,57,70,0.04);">
        <p style="margin:0; font-size:0.9rem; color:var(--accent);">This order was cancelled.</p>
    </div>
    @endif

    <div class="account-card" style="margin-bottom:1.5rem;">
        <h2 style="font-size:1rem; font-weight:600; margin-bottom:1rem; color:var(--text-muted);">SUBMITTED GAMES</h2>
        @foreach($order->items as $item)
        <div class="co-detail-item">
            @if(!empty($item['cover_url']))
            <img src="{{ $item['cover_url'] }}" alt="{{ $item['game_title'] }}" class="co-detail-item__cover">
            @else
            <div class="co-detail-item__cover co-detail-item__cover--placeholder">🎮</div>
            @endif
            <div class="co-detail-item__body">
                <span class="co-detail-item__title">{{ $item['game_title'] }}</span>
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

    {{-- Re-add to basket (cancelled orders only) --}}
    @if($order->status === 'cancelled')
    <div class="account-card" style="border-color:var(--border); text-align:center; padding:1.75rem;">
        <div style="font-size:1.5rem; margin-bottom:0.5rem;">🔄</div>
        <h3 style="font-size:1rem; font-weight:700; margin:0 0 0.4rem;">Changed your mind?</h3>
        <p style="font-size:0.85rem; color:var(--text-muted); margin:0 0 1.25rem;">
            Re-add all games from this order back to your cash basket in one click.
        </p>
        <form method="POST" action="{{ route('cash-orders.resubmit', $order->order_ref) }}">
            @csrf
            <button type="submit" class="btn btn--primary">Re-add Games to Basket</button>
        </form>
    </div>
    @endif

</div>
@endsection
