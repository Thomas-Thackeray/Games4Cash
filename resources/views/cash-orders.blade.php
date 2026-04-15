@extends('layouts.app')
@section('title', 'Get Cash Submissions')

@section('content')
<div class="container" style="padding: 3rem 0 5rem;">

    <div class="page-header" style="margin-bottom:2rem;">
        <h1 class="section-title" style="font-size:2rem;">Get Cash Submissions</h1>
        <p style="color:var(--text-muted); margin-top:0.4rem;">Your quote history</p>
    </div>

    @if($orders->isEmpty())
    <div class="empty-state">
        <div class="icon">📋</div>
        <h3>No submissions yet</h3>
        <p>Add games to your Cash Basket and submit a quote to get started.</p>
        <a href="{{ route('cash-basket.index') }}" class="btn btn--primary" style="margin-top:1.5rem;">Go to Cash Basket</a>
    </div>
    @else
    <div class="co-list">
        @foreach($orders as $order)
        <a href="{{ route('cash-orders.show', $order->order_ref) }}" class="co-card">
            <div class="co-card__ref">
                <span class="co-card__ref-id">{{ $order->order_ref }}</span>
                <span class="status-badge {{ $order->statusClass() }}">{{ $order->statusLabel() }}</span>
            </div>
            <div class="co-card__meta">
                <span>{{ count($order->items) }} {{ count($order->items) === 1 ? 'game' : 'games' }}</span>
                <span class="co-card__total">£{{ number_format((float) $order->total_gbp, 2) }}</span>
                <span class="co-card__date">{{ $order->created_at->format('d M Y') }}</span>
            </div>
        </a>
        @endforeach
    </div>

    <div style="margin-top:2rem;">
        {{ $orders->links() }}
    </div>
    @endif

</div>
@endsection
