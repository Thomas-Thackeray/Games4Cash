@extends('layouts.app')
@section('title', 'Evaluation Requests')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Evaluation Requests</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.dashboard') }}" style="color:var(--accent);">← Dashboard</a></p>
        </div>
    </div>

    {{-- Status filter --}}
    <div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; flex-wrap:wrap;">
        @foreach(['all' => 'All', 'pending' => 'Pending', 'reviewed' => 'Reviewed', 'closed' => 'Closed'] as $val => $label)
        <a href="{{ route('admin.evaluations.index', ['status' => $val]) }}"
            class="btn btn--outline btn--sm {{ $status === $val ? 'btn--active' : '' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    @if($evaluations->isEmpty())
    <p style="color:var(--text-muted);">No evaluation requests found.</p>
    @else
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Game</th>
                    <th>Platform</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($evaluations as $ev)
                <tr>
                    <td class="admin-td-muted">{{ $ev->id }}</td>
                    <td><strong>{{ $ev->game_title }}</strong></td>
                    <td class="admin-td-muted">{{ $ev->platform }}</td>
                    <td class="admin-td-muted">
                        <a href="{{ route('admin.users.detail', $ev->user_id) }}" style="color:var(--accent);">
                            &#64;{{ $ev->user->username ?? '—' }}
                        </a>
                    </td>
                    <td>
                        @php
                            $cls = match($ev->status) {
                                'reviewed' => 'admin-badge--ok',
                                'closed'   => '',
                                default    => 'admin-badge--warning',
                            };
                        @endphp
                        <span class="admin-badge {{ $cls }}">{{ ucfirst($ev->status) }}</span>
                    </td>
                    <td class="admin-td-muted">{{ $ev->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="admin-row-actions">
                            <a href="{{ route('admin.evaluations.show', $ev->id) }}" class="btn btn--outline btn--xs">View</a>
                            <form method="POST" action="{{ route('admin.evaluations.destroy', $ev->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn--danger btn--xs"
                                    data-confirm="Delete this evaluation request?">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($evaluations->hasPages())
    <div style="margin-top:1.5rem; display:flex; justify-content:center;">
        {{ $evaluations->links() }}
    </div>
    @endif
    @endif

</div>
@endsection
