@extends('layouts.app')
@section('title', 'Evaluation #' . $evaluation->id)

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Evaluation #{{ $evaluation->id }}</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.evaluations.index') }}" style="color:var(--accent);">← Back to Evaluations</a></p>
        </div>
    </div>

    <div class="admin-detail-grid">

        {{-- Left: submission info --}}
        <div class="admin-section">
            <h2 class="admin-section__title">Submission Details</h2>
            <div class="admin-info-list">
                <div class="admin-info-row"><span>Game</span><strong>{{ $evaluation->game_title }}</strong></div>
                <div class="admin-info-row"><span>Platform</span><strong>{{ $evaluation->platform }}</strong></div>
                <div class="admin-info-row"><span>Condition</span><strong>{{ $evaluation->condition }}</strong></div>
                <div class="admin-info-row"><span>Status</span><strong>{{ ucfirst($evaluation->status) }}</strong></div>
                <div class="admin-info-row"><span>Submitted</span><strong>{{ $evaluation->created_at->format('d M Y, H:i') }}</strong></div>
            </div>

            @if($evaluation->description)
            <h2 class="admin-section__title" style="margin-top:1.5rem;">User's Description</h2>
            <p style="font-size:0.9rem; color:var(--text-muted); line-height:1.7; white-space:pre-wrap;">{{ $evaluation->description }}</p>
            @endif

            {{-- Images --}}
            @if(!empty($evaluation->image_paths))
            <h2 class="admin-section__title" style="margin-top:1.5rem;">Images ({{ count($evaluation->image_paths) }})</h2>
            <div style="display:flex; flex-wrap:wrap; gap:0.75rem; margin-top:0.5rem;">
                @foreach($evaluation->image_paths as $path)
                <a href="{{ Storage::url($path) }}" target="_blank" rel="noopener">
                    <img src="{{ Storage::url($path) }}" alt="Evaluation image"
                        style="width:120px; height:90px; object-fit:cover; border-radius:6px; border:1px solid var(--border);">
                </a>
                @endforeach
            </div>
            @else
            <p style="font-size:0.85rem; color:var(--text-dim); margin-top:1rem;">No images were uploaded.</p>
            @endif
        </div>

        {{-- Right: user info + admin actions --}}
        <div class="admin-section">
            <h2 class="admin-section__title">Submitted By</h2>
            <div class="admin-info-list">
                <div class="admin-info-row"><span>Name</span><strong>{{ $evaluation->user->first_name }} {{ $evaluation->user->surname }}</strong></div>
                <div class="admin-info-row"><span>Username</span><strong>&#64;{{ $evaluation->user->username }}</strong></div>
                <div class="admin-info-row"><span>Email</span><strong>{{ $evaluation->user->email }}</strong></div>
            </div>
            <a href="{{ route('admin.users.detail', $evaluation->user_id) }}" class="btn btn--outline btn--sm" style="margin-top:0.75rem; display:inline-block;">
                View User Profile
            </a>

            {{-- Update form --}}
            <h2 class="admin-section__title" style="margin-top:2rem;">Update Status & Notes</h2>
            <form method="POST" action="{{ route('admin.evaluations.update', $evaluation->id) }}">
                @csrf
                @method('PATCH')

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input">
                        @foreach(['pending' => 'Pending', 'reviewed' => 'Reviewed', 'closed' => 'Closed'] as $val => $label)
                        <option value="{{ $val }}" {{ $evaluation->status === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group" style="margin-top:1rem;">
                    <label class="form-label">Admin Notes (visible to user)</label>
                    <textarea name="admin_notes" rows="4" class="form-input" style="resize:vertical;"
                        placeholder="Leave a note for the user, e.g. estimated price or next steps.">{{ old('admin_notes', $evaluation->admin_notes) }}</textarea>
                    @error('admin_notes')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div style="display:flex; justify-content:flex-end; margin-top:1rem;">
                    <button type="submit" class="btn btn--primary">Save Changes</button>
                </div>
            </form>
        </div>

    </div>

</div>
@endsection
