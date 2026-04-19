@extends('layouts.app')
@section('title', 'User: ' . $subject->username)

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">{{ $subject->first_name }} {{ $subject->surname }}</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.users') }}" style="color:var(--accent);">← Back to Users</a></p>
        </div>
        <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
            <form method="POST" action="{{ route('admin.users.send-setup-email', $subject->id) }}">
                @csrf
                <button type="submit" class="btn btn--outline btn--sm"
                    data-confirm="Send a setup / password-reset email to {{ $subject->email }}?">
                    Send Setup Email
                </button>
            </form>
            @unless($subject->force_password_reset)
            <form method="POST" action="{{ route('admin.users.force-reset', $subject->id) }}">
                @csrf
                <button type="submit" class="btn btn--outline btn--sm"
                    data-confirm="Force this user to reset their password on next login?">
                    Force Password Reset
                </button>
            </form>
            @else
            <span class="admin-badge admin-badge--warning" style="align-self:center;">Reset Pending</span>
            @endunless
            <form method="POST" action="{{ route('admin.users.delete', $subject->id) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn--danger btn--sm"
                    data-confirm="Permanently delete this account?">
                    Delete Account
                </button>
            </form>
        </div>
    </div>

    {{-- User details --}}
    <div class="admin-detail-grid">
        <div class="admin-section">
            <h2 class="admin-section__title">Account Info</h2>
            <div class="admin-info-list">
                <div class="admin-info-row"><span>Username</span><strong>{{ $subject->username }}</strong></div>
                <div class="admin-info-row"><span>Email</span><strong>{{ $subject->email }}</strong></div>
                <div class="admin-info-row"><span>Contact</span><strong>{{ $subject->contact_number }}</strong></div>
                <div class="admin-info-row"><span>Registered</span><strong>{{ $subject->created_at->format('d M Y, H:i') }}</strong></div>
                <div class="admin-info-row">
                    <span>Last Active</span>
                    <strong>{{ $subject->last_active_at ? $subject->last_active_at->format('d M Y, H:i') . ' (' . $subject->last_active_at->diffForHumans() . ')' : 'Never' }}</strong>
                </div>
                <div class="admin-info-row">
                    <span>Password Reset</span>
                    <strong>{{ $subject->force_password_reset ? 'Required on next login' : 'Not required' }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Wishlist --}}
    <div class="admin-section" style="margin-top:2rem;">
        <h2 class="admin-section__title">Wishlist ({{ $wishlistItems->count() }})</h2>

        @if($wishlistItems->isEmpty())
        <p style="color:var(--text-dim); padding:1rem 0;">No wishlist items.</p>
        @else
        <div class="admin-wishlist-grid">
            @foreach($wishlistItems as $item)
            <a href="{{ \App\Models\GamePrice::urlForId($item->igdb_game_id) }}" class="admin-wishlist-item">
                @if($item->cover_url)
                <img src="{{ $item->cover_url }}" alt="{{ $item->game_title }}" class="admin-wishlist-item__cover">
                @else
                <div class="admin-wishlist-item__cover admin-wishlist-item__cover--placeholder">🎮</div>
                @endif
                <span class="admin-wishlist-item__title">{{ $item->game_title }}</span>
            </a>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Current cash basket --}}
    <div class="admin-section" style="margin-top:2rem;">
        <h2 class="admin-section__title">Current Cash Basket ({{ $basketItems->count() }})</h2>

        @if($basketItems->isEmpty())
        <p style="color:var(--text-dim); padding:1rem 0;">Basket is empty.</p>
        @else
        <div class="account-card" style="padding:0; overflow:hidden; max-width:640px;">
            @foreach($basketItems as $item)
            <div class="co-detail-item" style="padding:0.75rem 1.25rem; border-bottom:1px solid var(--border);">
                @if(!empty($item['cover_url']))
                <img src="{{ $item['cover_url'] }}" alt="{{ $item['game_title'] }}" class="co-detail-item__cover">
                @else
                <div class="co-detail-item__cover co-detail-item__cover--placeholder">🎮</div>
                @endif
                <div class="co-detail-item__body">
                    <a href="{{ \App\Models\GamePrice::urlForId($item['igdb_game_id']) }}" class="co-detail-item__title" style="text-decoration:none; color:inherit;">
                        {{ $item['game_title'] }}
                    </a>
                    @if($item['platform_name'])
                    <span class="co-detail-item__platform">{{ $item['platform_name'] }}</span>
                    @endif
                </div>
                <span class="co-detail-item__price">{{ $item['display_price'] ?? '—' }}</span>
            </div>
            @endforeach
            <div style="padding:0.75rem 1.25rem; display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:0.85rem; color:var(--text-muted);">Estimated total</span>
                <span style="font-weight:700; color:var(--accent-2);">{{ $basketTotal }}</span>
            </div>
        </div>
        @endif
    </div>

    {{-- Submitted quotes --}}
    <div class="admin-section" style="margin-top:2rem;">
        <h2 class="admin-section__title">Submitted Quotes ({{ $cashOrders->count() }})</h2>

        @if($cashOrders->isEmpty())
        <p style="color:var(--text-dim); padding:1rem 0;">No quotes submitted.</p>
        @else
        <div class="account-card" style="padding:0; overflow:hidden; max-width:800px;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Games</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cashOrders as $order)
                    <tr>
                        <td style="font-family:'Bebas Neue',sans-serif; font-size:1rem; letter-spacing:0.05em;">
                            {{ $order->order_ref }}
                        </td>
                        <td>{{ count($order->items) }}</td>
                        <td style="font-weight:700; color:var(--accent-2);">
                            £{{ number_format((float) $order->total_gbp, 2) }}
                        </td>
                        <td>
                            <span class="status-badge {{ $order->statusClass() }}">{{ $order->statusLabel() }}</span>
                        </td>
                        <td style="color:var(--text-muted); font-size:0.82rem; white-space:nowrap;">
                            {{ $order->created_at->format('d M Y') }}
                        </td>
                        <td>
                            <a href="{{ route('admin.orders.detail', $order->id) }}" class="btn btn--outline btn--xs">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Login attempts --}}
    <div class="admin-section" style="margin-top:2rem;">
        <h2 class="admin-section__title">Login Attempts</h2>

        @if($attempts->isEmpty())
        <p style="color:var(--text-dim); padding:1rem 0;">No login attempts recorded.</p>
        @else
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Date &amp; Time</th>
                        <th>IP Address</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attempts as $attempt)
                    <tr>
                        <td>
                            <span class="attempt-badge attempt-badge--{{ $attempt->status }}">
                                {{ $attempt->status === 'success' ? '✓ Success' : '✕ Failed' }}
                            </span>
                        </td>
                        <td class="admin-td-muted">{{ $attempt->created_at->format('d M Y, H:i') }}</td>
                        <td class="attempt-ip">{{ $attempt->ip_address }}</td>
                        <td class="admin-td-muted">{{ $attempt->location }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($attempts->hasPages())
        <div class="history-pagination" style="margin-top:1.25rem;">
            <span class="history-pagination__info">Page {{ $attempts->currentPage() }} of {{ $attempts->lastPage() }}</span>
            <div class="history-pagination__btns">
                @if($attempts->onFirstPage())
                    <span class="btn btn--outline btn--sm history-pagination__btn--disabled">← Previous</span>
                @else
                    <a href="{{ $attempts->previousPageUrl() }}" class="btn btn--outline btn--sm">← Previous</a>
                @endif
                @if($attempts->hasMorePages())
                    <a href="{{ $attempts->nextPageUrl() }}" class="btn btn--outline btn--sm">Next →</a>
                @else
                    <span class="btn btn--outline btn--sm history-pagination__btn--disabled">Next →</span>
                @endif
            </div>
        </div>
        @endif
        @endif
    </div>

</div>
@endsection
