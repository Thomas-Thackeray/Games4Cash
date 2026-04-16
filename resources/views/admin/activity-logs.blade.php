@extends('layouts.app')
@section('title', 'Activity Logs')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Activity Logs</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.dashboard') }}" style="color:var(--accent);">← Dashboard</a></p>
        </div>
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
    <div class="admin-table-wrap">
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

    <p style="color:var(--text-muted); font-size:0.85rem; margin-top:1rem;">
        {{ $logs->count() }} {{ $logs->count() === 1 ? 'entry' : 'entries' }}
    </p>

</div>
@endsection
