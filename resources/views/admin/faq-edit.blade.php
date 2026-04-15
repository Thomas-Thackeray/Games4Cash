@extends('layouts.app')
@section('title', 'Edit FAQ')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Edit FAQ</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.faqs.index') }}" style="color:var(--accent);">← Back to FAQs</a></p>
        </div>
    </div>

    <div class="settings-card" style="max-width:640px;">
        <h2 class="settings-card__title">Update Entry</h2>
        <form method="POST" action="{{ route('admin.faqs.update', $faq->id) }}">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label class="form-label">Question / Title</label>
                <input type="text" name="title"
                    value="{{ old('title', $faq->title) }}"
                    class="form-input" style="width:100%;">
                @error('title')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group" style="margin-top:1rem;">
                <label class="form-label">Answer / Description</label>
                <textarea name="description" rows="6"
                    class="form-input" style="width:100%; resize:vertical;">{{ old('description', $faq->description) }}</textarea>
                @error('description')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group" style="margin-top:1rem;">
                <label class="form-label">Sort Order</label>
                <p class="settings-hint">Lower numbers appear first on the FAQ page.</p>
                <input type="number" name="sort_order"
                    value="{{ old('sort_order', $faq->sort_order) }}"
                    min="0" class="form-input" style="width:100px;">
                @error('sort_order')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div style="display:flex; gap:0.75rem; margin-top:1.25rem;">
                <button type="submit" class="btn btn--primary btn--sm">Save Changes</button>
                <a href="{{ route('admin.faqs.index') }}" class="btn btn--outline btn--sm">Cancel</a>
            </div>
        </form>
    </div>

</div>
@endsection
