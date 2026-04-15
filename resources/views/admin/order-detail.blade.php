@extends('layouts.app')
@section('title', 'Order ' . $order->order_ref)

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">{{ $order->order_ref }}</h1>
            <p class="admin-subtitle">
                <a href="{{ route('admin.orders') }}" style="color:var(--accent);">← Cash Orders</a>
            </p>
        </div>
        <span class="status-badge {{ $order->statusClass() }}" style="font-size:1rem; padding:0.4rem 1rem;">
            {{ $order->statusLabel() }}
        </span>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 340px; gap:1.5rem; align-items:start;">

        {{-- Left: order items --}}
        <div>
            <div class="admin-section">
                <h2 class="admin-section__title">Submitted Games</h2>
                <div class="account-card" style="padding:0; overflow:hidden;">
                    @foreach($order->items as $item)
                    <div class="co-detail-item" style="padding:0.9rem 1.25rem; border-bottom:1px solid var(--border);">
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
                    <div style="padding:1rem 1.25rem; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-weight:600;">Total</span>
                        <span style="font-size:1.4rem; font-weight:700; color:var(--accent-2);">
                            £{{ number_format((float) $order->total_gbp, 2) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Customer details --}}
            <div class="admin-section" style="margin-top:1.5rem;">
                <h2 class="admin-section__title">Customer</h2>
                <div class="account-card">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                        <div>
                            <label style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.06em;">Name</label>
                            <p style="margin:0.2rem 0 0; font-weight:600;">
                                {{ e(trim(($order->user->first_name ?? '') . ' ' . ($order->user->surname ?? ''))) ?: e($order->user->username ?? '—') }}
                            </p>
                        </div>
                        <div>
                            <label style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.06em;">Username</label>
                            <p style="margin:0.2rem 0 0;">{{ e($order->user->username ?? '—') }}</p>
                        </div>
                        <div>
                            <label style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.06em;">Email</label>
                            <p style="margin:0.2rem 0 0;">
                                <a href="mailto:{{ e($order->user->email ?? '') }}" style="color:var(--accent-2);">
                                    {{ e($order->user->email ?? '—') }}
                                </a>
                            </p>
                        </div>
                        <div>
                            <label style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.06em;">Phone</label>
                            <p style="margin:0.2rem 0 0;">
                                @if(!empty($order->user->contact_number))
                                <a href="tel:{{ e($order->user->contact_number) }}" style="color:var(--accent-2);">
                                    {{ e($order->user->contact_number) }}
                                </a>
                                @else
                                <span style="color:var(--text-dim);">Not provided</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    @if($order->address_line1)
                    <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid var(--border);">
                        <label style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.06em;">Pickup Address</label>
                        <address style="font-style:normal; line-height:1.7; font-size:0.88rem; margin-top:0.25rem;">
                            {{ e($order->house_name_number) }}, {{ e($order->address_line1) }}<br>
                            @if($order->address_line2)
                            {{ e($order->address_line2) }}<br>
                            @endif
                            @if($order->address_line3)
                            {{ e($order->address_line3) }}<br>
                            @endif
                            {{ e($order->city) }}{{ $order->county ? ', ' . e($order->county) : '' }}<br>
                            <strong>{{ e($order->postcode) }}</strong>
                        </address>
                    </div>
                    @endif
                    <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid var(--border);">
                        <label style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:0.4rem;">Customer Confirmations</label>
                        <div style="display:flex; flex-direction:column; gap:0.3rem; font-size:0.82rem;">
                            <span style="color:{{ $order->agreed_terms ? 'var(--rating-high)' : 'var(--accent)' }};">
                                {{ $order->agreed_terms ? '✓' : '✗' }} Agreed to Terms &amp; Conditions
                            </span>
                            <span style="color:{{ $order->confirmed_contents ? 'var(--rating-high)' : 'var(--accent)' }};">
                                {{ $order->confirmed_contents ? '✓' : '✗' }} Confirmed all items present
                            </span>
                        </div>
                    </div>
                    <div style="margin-top:1rem; padding-top:1rem; border-top:1px solid var(--border); font-size:0.82rem; color:var(--text-muted);">
                        Submitted {{ $order->created_at->format('d M Y \a\t H:i') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: status update --}}
        <div>
            <div class="admin-section">
                <h2 class="admin-section__title">Update Status</h2>
                <div class="account-card">
                    <form method="POST" action="{{ route('admin.orders.update-status', $order->id) }}">
                        @csrf
                        @method('PATCH')

                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-input" style="width:100%;">
                                @foreach(['pending' => 'Pending', 'contacted' => 'Contacted', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label)
                                <option value="{{ $val }}" {{ $order->status === $val ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group" style="margin-top:1rem;">
                            <label class="form-label">Internal Notes</label>
                            <textarea name="admin_notes"
                                class="form-input"
                                rows="5"
                                style="width:100%; resize:vertical;"
                                placeholder="Notes visible only to admins…">{{ old('admin_notes', $order->admin_notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn--primary btn--sm" style="width:100%; margin-top:0.75rem;">
                            Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
