@extends('layouts.app')
@section('title', 'Submission from ' . $submission->name)

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Contact Submission</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.contact-submissions') }}" style="color:var(--accent);">← Back to Submissions</a></p>
        </div>
        <form method="POST" action="{{ route('admin.contact-submissions.delete', $submission->id) }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn--danger btn--sm"
                data-confirm="Permanently delete this submission?">
                Delete Submission
            </button>
        </form>
    </div>

    <div class="admin-section">
        <div class="admin-info-list" style="margin-bottom:2rem;">
            <div class="admin-info-row">
                <span>From</span>
                <strong>{{ $submission->name }}</strong>
            </div>
            <div class="admin-info-row">
                <span>Email</span>
                <strong>
                    <a href="mailto:{{ $submission->email }}" style="color:var(--accent);">{{ $submission->email }}</a>
                </strong>
            </div>
            <div class="admin-info-row">
                <span>Contact Number</span>
                <strong>{{ $submission->contact_number ?? '—' }}</strong>
            </div>
            <div class="admin-info-row">
                <span>Received</span>
                <strong>{{ $submission->created_at->format('d M Y, H:i') }} ({{ $submission->created_at->diffForHumans() }})</strong>
            </div>
            <div class="admin-info-row">
                <span>Status</span>
                <strong>
                    @if($submission->isRead())
                    <span class="admin-badge admin-badge--ok">Read {{ $submission->read_at->format('d M Y, H:i') }}</span>
                    @else
                    <span class="admin-badge admin-badge--warning">Unread</span>
                    @endif
                </strong>
            </div>
        </div>

        <h2 class="admin-section__title">Message</h2>
        <div style="background:var(--bg-2); border:1px solid var(--border); border-radius:var(--radius); padding:1.5rem; line-height:1.8; white-space:pre-wrap; color:var(--text);">{{ $submission->message }}</div>
    </div>

</div>
@endsection
