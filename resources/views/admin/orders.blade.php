@extends('layouts.app')
@section('title', 'Cash Orders')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Cash Orders</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.dashboard') }}" style="color:var(--accent);">← Dashboard</a></p>
        </div>
    </div>

    {{-- Export CSV --}}
    <details class="admin-section" style="margin-bottom:1.25rem; padding:1rem 1.25rem;">
        <summary style="cursor:pointer; font-weight:600; font-size:0.9rem; list-style:none; display:flex; align-items:center; gap:0.5rem;">
            ⬇ Export Orders to CSV
        </summary>
        <form method="GET" action="{{ route('admin.orders.export') }}" style="margin-top:1rem; display:flex; gap:0.75rem; flex-wrap:wrap; align-items:flex-end;">
            <div>
                <label style="font-size:0.8rem; color:var(--text-muted); display:block; margin-bottom:0.3rem;">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-input" style="padding:0.45rem 0.7rem; font-size:0.88rem;">
            </div>
            <div>
                <label style="font-size:0.8rem; color:var(--text-muted); display:block; margin-bottom:0.3rem;">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-input" style="padding:0.45rem 0.7rem; font-size:0.88rem;">
            </div>
            <div>
                <label style="font-size:0.8rem; color:var(--text-muted); display:block; margin-bottom:0.3rem;">Status</label>
                <select name="status" class="form-input" style="padding:0.45rem 0.7rem; font-size:0.88rem;">
                    @foreach(['all' => 'All', 'pending' => 'Pending', 'contacted' => 'Contacted', 'completed' => 'Paid', 'cancelled' => 'Cancelled'] as $val => $label)
                    <option value="{{ $val }}" {{ request('status', 'all') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn--primary btn--sm">Download CSV</button>
        </form>
    </details>

    {{-- Status filter tabs --}}
    <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:1.5rem;">
        @foreach(['all' => 'All', 'pending' => 'Pending', 'contacted' => 'Contacted', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label)
        <a href="{{ route('admin.orders', ['status' => $val]) }}"
           class="btn btn--sm {{ $status === $val ? 'btn--primary' : 'btn--outline' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    @if($orders->isEmpty())
    <div class="empty-state" style="padding:4rem 0;">
        <div class="icon">📋</div>
        <h3>No orders found</h3>
        <p>No Get Cash submissions match the current filter.</p>
    </div>
    @else
    <div class="admin-section">
        <div class="account-card" style="padding:0; overflow:hidden;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>User</th>
                        <th>Games</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td>
                            <span style="font-family:'Bebas Neue',sans-serif; font-size:1rem; letter-spacing:0.05em;">
                                {{ $order->order_ref }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight:600; font-size:0.9rem;">{{ e($order->user->username ?? '—') }}</div>
                            <div style="font-size:0.78rem; color:var(--text-muted);">{{ e($order->user->email ?? '') }}</div>
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
                            <a href="{{ route('admin.orders.detail', $order->id) }}"
                               class="btn btn--outline btn--xs">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top:1.5rem;">
            {{ $orders->links() }}
        </div>
    </div>
    @endif

</div>
@endsection
