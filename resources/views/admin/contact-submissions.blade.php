@extends('layouts.app')
@section('title', 'Contact Submissions')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Contact Submissions</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.dashboard') }}" style="color:var(--accent);">← Dashboard</a></p>
        </div>
        <span class="admin-badge {{ $submissions->total() > 0 ? 'admin-badge--warning' : 'admin-badge--ok' }}" style="align-self:center; font-size:0.95rem; padding:0.4rem 0.9rem;">
            {{ number_format($submissions->total()) }} {{ Str::plural('submission', $submissions->total()) }}
        </span>
    </div>

    {{-- Filter tabs --}}
    <div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; flex-wrap:wrap;">
        <a href="{{ route('admin.contact-submissions') }}"
            class="btn btn--sm {{ $filter === 'all' ? 'btn--primary' : 'btn--outline' }}">All</a>
        <a href="{{ route('admin.contact-submissions', ['filter' => 'unread']) }}"
            class="btn btn--sm {{ $filter === 'unread' ? 'btn--primary' : 'btn--outline' }}">Unread</a>
        <a href="{{ route('admin.contact-submissions', ['filter' => 'read']) }}"
            class="btn btn--sm {{ $filter === 'read' ? 'btn--primary' : 'btn--outline' }}">Read</a>
    </div>

    {{-- Table --}}
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Message Preview</th>
                    <th>Received</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $sub)
                <tr style="{{ $sub->isRead() ? '' : 'font-weight:600;' }}">
                    <td>
                        @if($sub->isRead())
                        <span class="admin-badge admin-badge--ok">Read</span>
                        @else
                        <span class="admin-badge admin-badge--warning">Unread</span>
                        @endif
                    </td>
                    <td>{{ $sub->name }}</td>
                    <td class="admin-td-muted">{{ $sub->email }}</td>
                    <td class="admin-td-muted" style="max-width:280px;">
                        <span style="display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            {{ Str::limit($sub->message, 80) }}
                        </span>
                    </td>
                    <td class="admin-td-muted">{{ $sub->created_at->format('d M Y, H:i') }}</td>
                    <td>
                        <div class="admin-row-actions">
                            <a href="{{ route('admin.contact-submissions.view', $sub->id) }}" class="btn btn--outline btn--xs">View</a>
                            <form method="POST" action="{{ route('admin.contact-submissions.delete', $sub->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn--danger btn--xs"
                                    data-confirm="Delete this submission?">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:2rem; color:var(--text-dim);">
                        No submissions found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($submissions->hasPages())
    <div class="history-pagination" style="margin-top:1.5rem;">
        <span class="history-pagination__info">
            Showing {{ $submissions->firstItem() }}–{{ $submissions->lastItem() }} of {{ $submissions->total() }} submissions
        </span>
        <div class="history-pagination__btns">
            @if($submissions->onFirstPage())
                <span class="btn btn--outline btn--sm history-pagination__btn--disabled">← Previous</span>
            @else
                <a href="{{ $submissions->previousPageUrl() }}" class="btn btn--outline btn--sm">← Previous</a>
            @endif
            @if($submissions->hasMorePages())
                <a href="{{ $submissions->nextPageUrl() }}" class="btn btn--outline btn--sm">Next →</a>
            @else
                <span class="btn btn--outline btn--sm history-pagination__btn--disabled">Next →</span>
            @endif
        </div>
    </div>
    @endif

</div>
@endsection
