@extends('layouts.app')
@section('title', 'Manage FAQs')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">FAQs</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.dashboard') }}" style="color:var(--accent);">← Dashboard</a></p>
        </div>
        <a href="{{ route('faq') }}" target="_blank" class="btn btn--outline btn--sm">View Public Page ↗</a>
    </div>

    @if(session('flash_success'))
    <div class="alert alert--success" style="margin-bottom:1.5rem;">{{ session('flash_success') }}</div>
    @endif

    <div class="settings-grid" style="align-items:start;">

        {{-- Existing FAQs --}}
        <div style="grid-column: 1 / -1;">
            @if($faqs->isEmpty())
            <div class="empty-state" style="padding:3rem 0;">
                <div class="icon">❓</div>
                <h3>No FAQs yet</h3>
                <p>Add your first FAQ using the form below.</p>
            </div>
            @else
            <div class="admin-section">
                <h2 class="admin-section__title">{{ $faqs->count() }} {{ $faqs->count() === 1 ? 'Entry' : 'Entries' }}</h2>
                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    @foreach($faqs as $faq)
                    <div class="account-card" style="display:flex; align-items:flex-start; gap:0.75rem 1rem; padding:1rem 1.25rem; flex-wrap:wrap;">
                        <div style="flex:1; min-width:200px;">
                            <p style="font-weight:600; color:var(--text); margin-bottom:0.25rem; word-break:break-word;">{{ $faq->title }}</p>
                            <p style="color:var(--text-muted); font-size:0.875rem; overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">{{ $faq->description }}</p>
                        </div>
                        <div style="display:flex; gap:0.5rem; flex-shrink:0; align-self:center;">
                            <a href="{{ route('admin.faqs.edit', $faq->id) }}" class="btn btn--outline btn--sm">Edit</a>
                            <form method="POST" action="{{ route('admin.faqs.destroy', $faq->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn--sm"
                                    style="background:rgba(230,57,70,0.12); color:var(--accent); border:1px solid rgba(230,57,70,0.3);"
                                    data-confirm="Delete &quot;{{ e($faq->title) }}&quot;?">Delete</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Add new FAQ --}}
        <div class="settings-card" style="grid-column: 1 / -1; max-width: 640px;">
            <h2 class="settings-card__title">Add New FAQ</h2>
            <form method="POST" action="{{ route('admin.faqs.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Question / Title</label>
                    <input type="text" name="title" value="{{ old('title') }}"
                        class="form-input" placeholder="e.g. How does the collection process work?"
                        style="width:100%;">
                    @error('title')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group" style="margin-top:1rem;">
                    <label class="form-label">Answer / Description</label>
                    <textarea name="description" rows="4"
                        class="form-input" placeholder="Explain the answer clearly…"
                        style="width:100%; resize:vertical;">{{ old('description') }}</textarea>
                    @error('description')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="btn btn--primary btn--sm" style="margin-top:1rem;">Add FAQ</button>
            </form>
        </div>

    </div>

</div>
@endsection
