@extends('layouts.app')
@section('title', 'Newsletter Management')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Newsletter</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.dashboard') }}" style="color:var(--accent);">← Dashboard</a></p>
        </div>
    </div>

    {{-- Stats --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:1rem; margin-bottom:2rem;">
        <div class="stat-card">
            <div style="font-size:0.72rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-muted); margin-bottom:0.75rem;">Active Subscribers</div>
            <div class="stat-card__value">{{ number_format($activeCount) }}</div>
            <div class="stat-card__label">Will receive next send</div>
        </div>
        <div class="stat-card">
            <div style="font-size:0.72rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-muted); margin-bottom:0.75rem;">Total (incl. unsubs)</div>
            <div class="stat-card__value">{{ number_format($totalCount) }}</div>
            <div class="stat-card__label">All time</div>
        </div>
        <div class="stat-card">
            <div style="font-size:0.72rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-muted); margin-bottom:0.75rem;">Unsubscribed</div>
            <div class="stat-card__value" style="color:var(--text-muted);">{{ number_format($totalCount - $activeCount) }}</div>
            <div class="stat-card__label">Opted out</div>
        </div>
    </div>

    {{-- Send newsletter form --}}
    <div class="admin-section" style="margin-bottom:2rem;">
        <h2 class="admin-section__title" style="margin-bottom:1rem;">Send Newsletter</h2>
        <p style="color:var(--text-muted); font-size:0.88rem; margin-bottom:1.25rem;">
            This will send an email to all <strong>{{ number_format($activeCount) }}</strong> active subscriber(s).
        </p>

        @if($activeCount === 0)
        <div class="alert alert--error">No active subscribers to send to yet.</div>
        @else
        <form method="POST" action="{{ route('admin.newsletter.send') }}">
            @csrf
            <div style="margin-bottom:1rem;">
                <label class="form-label">Subject Line</label>
                <input type="text" name="subject" class="form-input" maxlength="200"
                       placeholder="e.g. New games added this week!" value="{{ old('subject') }}" required>
                @error('subject')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div style="margin-bottom:1.25rem;">
                <label class="form-label">Email Body</label>
                <textarea name="body" class="form-input" rows="10"
                          placeholder="Write your newsletter content here. Plain text, line breaks are preserved."
                          required>{{ old('body') }}</textarea>
                @error('body')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn btn--primary"
                    data-confirm="Send this newsletter to {{ $activeCount }} subscriber(s)? This cannot be undone.">
                Send Newsletter to {{ number_format($activeCount) }} Subscriber(s)
            </button>
        </form>
        @endif
    </div>

    {{-- Subscriber list --}}
    <div class="admin-section">
        <h2 class="admin-section__title" style="margin-bottom:0.75rem;">Subscribers</h2>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Name</th>
                        <th>Source</th>
                        <th>Subscribed</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscribers as $sub)
                    <tr>
                        <td style="font-size:0.85rem;">{{ $sub->email }}</td>
                        <td style="color:var(--text-muted); font-size:0.85rem;">{{ $sub->name ?: '—' }}</td>
                        <td style="font-size:0.82rem; color:var(--text-muted);">{{ ucfirst($sub->source) }}</td>
                        <td style="font-size:0.82rem; color:var(--text-muted); white-space:nowrap;">
                            {{ $sub->subscribed_at?->format('d M Y') ?? '—' }}
                        </td>
                        <td>
                            @if($sub->isActive())
                            <span style="color:#22c55e; font-size:0.8rem; font-weight:600;">Active</span>
                            @else
                            <span style="color:var(--text-muted); font-size:0.8rem;">Unsubscribed</span>
                            @endif
                        </td>
                        <td style="text-align:right;">
                            <form method="POST" action="{{ route('admin.newsletter.destroy', $sub->id) }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn--danger btn--sm"
                                        data-confirm="Remove {{ $sub->email }} from the newsletter list?"
                                        onclick="if(confirm(this.dataset.confirm)){this.closest('form').submit();}">
                                    Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="admin-td-muted" style="text-align:center; padding:2rem;">No subscribers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subscribers->hasPages())
        <div style="margin-top:1rem;">{{ $subscribers->links() }}</div>
        @endif
    </div>

</div>
@endsection
