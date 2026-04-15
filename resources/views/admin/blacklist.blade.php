@extends('layouts.app')
@section('title', 'Blacklisted Passwords')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Blacklisted Passwords</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.dashboard') }}" style="color:var(--accent);">← Dashboard</a></p>
        </div>
        <span class="admin-badge admin-badge--ok" style="align-self:center; font-size:0.95rem; padding:0.4rem 0.9rem;">
            {{ number_format($passwords->total()) }} entries
        </span>
    </div>

    {{-- Add new password --}}
    <div class="admin-section" style="margin-bottom:2rem;">
        <h2 class="admin-section__title">Add Password to Blacklist</h2>
        <form method="POST" action="{{ route('admin.blacklist.add') }}" style="display:flex; gap:0.75rem; align-items:flex-start; flex-wrap:wrap;">
            @csrf
            <input type="text" name="password"
                class="form-input"
                style="flex:1; min-width:220px; max-width:400px;"
                placeholder="Enter password to blacklist…"
                autocomplete="off">
            <button type="submit" class="btn btn--primary btn--sm">Add to Blacklist</button>
        </form>
        @if($errors->has('password'))
        <p style="color:var(--danger); margin-top:0.5rem; font-size:0.9rem;">{{ $errors->first('password') }}</p>
        @endif
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('admin.blacklist') }}" class="admin-search-form" style="margin-bottom:1.5rem;">
        <input type="search" name="search" value="{{ $search }}"
            class="form-input admin-search-input"
            placeholder="Search blacklisted passwords…">
        <button type="submit" class="btn btn--outline btn--sm">Search</button>
        @if($search)
        <a href="{{ route('admin.blacklist') }}" class="btn btn--outline btn--sm">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Password</th>
                    <th>Added</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($passwords as $entry)
                <tr>
                    <td class="admin-td-muted" style="width:60px;">{{ $entry->id }}</td>
                    <td><code style="background:var(--bg-3); padding:0.2rem 0.5rem; border-radius:4px; font-size:0.9rem;">{{ $entry->password }}</code></td>
                    <td class="admin-td-muted">{{ $entry->created_at ? $entry->created_at->format('d M Y') : '—' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.blacklist.remove', $entry->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn--danger btn--xs"
                                data-confirm="Remove this password from the blacklist?">
                                Remove
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center; padding:2rem; color:var(--text-dim);">
                        {{ $search ? 'No passwords match your search.' : 'The blacklist is empty.' }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($passwords->hasPages())
    <div class="history-pagination" style="margin-top:1.5rem;">
        <span class="history-pagination__info">
            Showing {{ $passwords->firstItem() }}–{{ $passwords->lastItem() }} of {{ $passwords->total() }} entries
        </span>
        <div class="history-pagination__btns">
            @if($passwords->onFirstPage())
                <span class="btn btn--outline btn--sm history-pagination__btn--disabled">← Previous</span>
            @else
                <a href="{{ $passwords->previousPageUrl() }}" class="btn btn--outline btn--sm">← Previous</a>
            @endif
            @if($passwords->hasMorePages())
                <a href="{{ $passwords->nextPageUrl() }}" class="btn btn--outline btn--sm">Next →</a>
            @else
                <span class="btn btn--outline btn--sm history-pagination__btn--disabled">Next →</span>
            @endif
        </div>
    </div>
    @endif

</div>
@endsection
