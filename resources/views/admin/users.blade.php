@extends('layouts.app')
@section('title', 'Manage Users')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Manage Users</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.dashboard') }}" style="color:var(--accent);">← Dashboard</a></p>
        </div>
        <form method="POST" action="{{ route('admin.users.force-reset-all') }}">
            @csrf
            <button type="submit" class="btn btn--danger btn--sm"
                data-confirm="Force ALL users to reset their password on next login?">
                Force All to Reset Password
            </button>
        </form>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('admin.users') }}" class="admin-search-form">
        <input type="search" name="search" value="{{ $search }}"
            class="form-input admin-search-input"
            placeholder="Search by name, username or email…">
        <button type="submit" class="btn btn--outline btn--sm">Search</button>
        @if($search)
        <a href="{{ route('admin.users') }}" class="btn btn--outline btn--sm">Clear</a>
        @endif
    </form>

    {{-- Table (hidden on mobile) --}}
    <div class="admin-table-wrap admin-table-wrap--desktop-only">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Registered</th>
                    <th>Last Active</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    <td>
                        <div class="admin-user-cell">
                            <div class="admin-user-avatar">{{ strtoupper(substr($u->username, 0, 1)) }}</div>
                            <div>
                                <div class="admin-user-name">{{ $u->first_name }} {{ $u->surname }}</div>
                                <div class="admin-user-username">&#64;{{ $u->username }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="admin-td-muted">{{ $u->email }}</td>
                    <td class="admin-td-muted">{{ $u->created_at->format('d M Y') }}</td>
                    <td class="admin-td-muted">
                        {{ $u->last_active_at ? $u->last_active_at->diffForHumans() : 'Never' }}
                    </td>
                    <td>
                        @if($u->force_password_reset)
                        <span class="admin-badge admin-badge--warning">Reset Pending</span>
                        @else
                        <span class="admin-badge admin-badge--ok">Active</span>
                        @endif
                    </td>
                    <td>
                        <div class="admin-row-actions">
                            <a href="{{ route('admin.users.detail', $u->id) }}" class="btn btn--outline btn--xs">View</a>

                            @unless($u->force_password_reset)
                            <form method="POST" action="{{ route('admin.users.force-reset', $u->id) }}">
                                @csrf
                                <button type="submit" class="btn btn--outline btn--xs"
                                    data-confirm="Force this user to reset their password?">
                                    Force Reset
                                </button>
                            </form>
                            @endunless

                            <form method="POST" action="{{ route('admin.users.delete', $u->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn--danger btn--xs"
                                    data-confirm="Permanently delete the account for {{ $u->username }}?">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:2rem; color:var(--text-dim);">
                        No users found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="admin-mobile-cards">
        @forelse($users as $u)
        <div class="admin-mobile-card">
            <div class="admin-mobile-card__header">
                <div style="display:flex; align-items:center; gap:0.65rem;">
                    <div class="admin-user-avatar">{{ strtoupper(substr($u->username, 0, 1)) }}</div>
                    <div>
                        <div class="admin-mobile-card__title">{{ $u->first_name }} {{ $u->surname }}</div>
                        <div class="admin-mobile-card__sub">&#64;{{ $u->username }}</div>
                    </div>
                </div>
                @if($u->force_password_reset)
                <span class="admin-badge admin-badge--warning">Reset Pending</span>
                @else
                <span class="admin-badge admin-badge--ok">Active</span>
                @endif
            </div>
            <div class="admin-mobile-card__meta">
                <span>{{ $u->email }}</span>
                <span>Joined {{ $u->created_at->format('d M Y') }}</span>
                <span>Active {{ $u->last_active_at ? $u->last_active_at->diffForHumans() : 'never' }}</span>
            </div>
            <div class="admin-mobile-card__actions">
                <a href="{{ route('admin.users.detail', $u->id) }}" class="btn btn--outline btn--xs">View</a>
                @unless($u->force_password_reset)
                <form method="POST" action="{{ route('admin.users.force-reset', $u->id) }}">
                    @csrf
                    <button type="submit" class="btn btn--outline btn--xs"
                        data-confirm="Force this user to reset their password?">Force Reset</button>
                </form>
                @endunless
                <form method="POST" action="{{ route('admin.users.delete', $u->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn--danger btn--xs"
                        data-confirm="Permanently delete the account for {{ $u->username }}?">Delete</button>
                </form>
            </div>
        </div>
        @empty
        <p style="color:var(--text-dim); text-align:center; padding:2rem 0;">No users found.</p>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
    <div class="history-pagination" style="margin-top:1.5rem;">
        <span class="history-pagination__info">
            Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }} users
        </span>
        <div class="history-pagination__btns">
            @if($users->onFirstPage())
                <span class="btn btn--outline btn--sm history-pagination__btn--disabled">← Previous</span>
            @else
                <a href="{{ $users->previousPageUrl() }}" class="btn btn--outline btn--sm">← Previous</a>
            @endif
            @if($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() }}" class="btn btn--outline btn--sm">Next →</a>
            @else
                <span class="btn btn--outline btn--sm history-pagination__btn--disabled">Next →</span>
            @endif
        </div>
    </div>
    @endif

</div>
@endsection
