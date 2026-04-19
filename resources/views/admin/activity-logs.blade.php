@extends('layouts.app')
@section('title', 'Activity Logs')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Activity Logs</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.dashboard') }}" style="color:var(--accent);">← Dashboard</a></p>
        </div>
        <div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
            <a href="{{ route('admin.activity-logs.export', array_filter(['type' => $type !== 'all' ? $type : null, 'search' => $search ?: null])) }}"
               class="btn btn--outline btn--sm">
                ↓ Export CSV
            </a>
            <form method="POST" action="{{ route('admin.activity-logs.clear') }}">
                @csrf
                @method('DELETE')
                @if($type !== 'all')
                    <input type="hidden" name="type" value="{{ $type }}">
                    <button type="button" class="btn btn--danger btn--sm"
                        data-confirm="Clear all {{ $type }} logs? This cannot be undone.">
                        Clear {{ ucfirst($type) }} Logs
                    </button>
                @else
                    <button type="button" class="btn btn--danger btn--sm"
                        data-confirm="Clear ALL activity logs? This cannot be undone.">
                        Clear All Logs
                    </button>
                @endif
            </form>
        </div>
    </div>

    {{-- Filter tabs --}}
    <div style="display:flex; gap:0.5rem; margin-bottom:1.25rem; flex-wrap:wrap; align-items:center;">
        @foreach(['all' => 'All', 'search' => '🔍 Search', 'login' => '🔑 Login', 'filter' => '🎮 Filter', 'quote' => '💰 Quote', 'security' => '🚨 Security'] as $key => $label)
        <a href="{{ route('admin.activity-logs', array_filter(['type' => $key === 'all' ? null : $key, 'search' => $search ?: null])) }}"
            class="btn btn--sm {{ $type === $key ? 'btn--primary' : 'btn--outline' }}">
            {{ $label }}
        </a>
        @endforeach

        <form method="GET" action="{{ route('admin.activity-logs') }}" style="display:flex; gap:0.5rem; margin-left:auto; flex-wrap:wrap;">
            @if($type !== 'all')
                <input type="hidden" name="type" value="{{ $type }}">
            @endif
            <input type="search" name="search" value="{{ $search }}"
                class="form-input admin-search-input"
                placeholder="Filter by username…"
                style="width:200px;">
            <button type="submit" class="btn btn--outline btn--sm">Search</button>
            @if($search)
            <a href="{{ route('admin.activity-logs', $type !== 'all' ? ['type' => $type] : []) }}" class="btn btn--outline btn--sm">Clear</a>
            @endif
        </form>
    </div>

    {{-- Desktop table --}}
    <div class="admin-table-wrap admin-table-wrap--log">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Date &amp; Time</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>User</th>
                    <th>IP Address</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="admin-td-muted" style="white-space:nowrap;">
                        {{ $log->created_at->format('d M Y') }}<br>
                        <span style="font-size:0.8rem;">{{ $log->created_at->format('H:i:s') }}</span>
                    </td>
                    <td>
                        @include('admin._log-badge', ['log' => $log])
                    </td>
                    <td style="max-width:320px; word-break:break-word;">{{ $log->description }}</td>
                    <td class="admin-td-muted">
                        @if($log->user)
                            <a href="{{ route('admin.users.detail', $log->user->id) }}" style="color:var(--accent);">{{ $log->user->username }}</a>
                        @else
                            <span style="color:var(--text-dim);">Guest</span>
                        @endif
                    </td>
                    <td class="admin-td-muted attempt-ip">{{ $log->ip_address ?? '—' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.activity-logs.delete', $log->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn--danger btn--xs"
                                data-confirm="Delete this log entry?">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:2rem; color:var(--text-dim);">
                        No activity logs found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="log-cards">
        @forelse($logs as $log)
        <div class="log-card">
            <div class="log-card__header">
                @include('admin._log-badge', ['log' => $log])
                <form method="POST" action="{{ route('admin.activity-logs.delete', $log->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn--danger btn--xs"
                        data-confirm="Delete this log entry?">Delete</button>
                </form>
            </div>
            <div class="log-card__desc">{{ $log->description }}</div>
            <div class="log-card__meta">
                <span>{{ $log->created_at->format('d M Y, H:i:s') }}</span>
                @if($log->user)
                    <a href="{{ route('admin.users.detail', $log->user->id) }}" style="color:var(--accent);">{{ $log->user->username }}</a>
                @else
                    <span>Guest</span>
                @endif
                @if($log->ip_address)
                    <span class="attempt-ip">{{ $log->ip_address }}</span>
                @endif
            </div>
        </div>
        @empty
        <p style="text-align:center; color:var(--text-dim); padding:2rem 0;">No activity logs found.</p>
        @endforelse
    </div>

    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem; margin-top:1rem;">
        <p style="color:var(--text-muted); font-size:0.85rem; margin:0;">
            Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }} {{ $logs->total() === 1 ? 'entry' : 'entries' }}
        </p>
        @if($logs->hasPages())
        <div style="display:flex; gap:0.4rem; flex-wrap:wrap;">
            @if($logs->onFirstPage())
                <span class="btn btn--outline btn--sm" style="opacity:0.4; cursor:default;">← Prev</span>
            @else
                <a href="{{ $logs->previousPageUrl() }}" class="btn btn--outline btn--sm">← Prev</a>
            @endif

            @foreach($logs->getUrlRange(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page => $url)
                @if($page === $logs->currentPage())
                    <span class="btn btn--primary btn--sm">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="btn btn--outline btn--sm">{{ $page }}</a>
                @endif
            @endforeach

            @if($logs->hasMorePages())
                <a href="{{ $logs->nextPageUrl() }}" class="btn btn--outline btn--sm">Next →</a>
            @else
                <span class="btn btn--outline btn--sm" style="opacity:0.4; cursor:default;">Next →</span>
            @endif
        </div>
        @endif
    </div>

</div>
@endsection
