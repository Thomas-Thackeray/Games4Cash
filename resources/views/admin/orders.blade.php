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
