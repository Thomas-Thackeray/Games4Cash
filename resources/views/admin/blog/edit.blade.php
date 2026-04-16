@extends('layouts.app')
@section('title', 'Edit: ' . $post->title)

@push('head_meta')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
.ql-toolbar.ql-snow { background: var(--bg-2); border-color: var(--border); border-radius: var(--radius) var(--radius) 0 0; }
.ql-container.ql-snow { background: var(--bg-1); border-color: var(--border); border-radius: 0 0 var(--radius) var(--radius); min-height: 320px; font-size: 0.95rem; color: var(--text); }
.ql-editor { min-height: 300px; color: var(--text); }
.ql-toolbar .ql-stroke { stroke: var(--text-muted); }
.ql-toolbar .ql-fill { fill: var(--text-muted); }
.ql-toolbar .ql-picker { color: var(--text-muted); }
.ql-toolbar button:hover .ql-stroke, .ql-toolbar button.ql-active .ql-stroke { stroke: var(--accent); }
.ql-toolbar button:hover .ql-fill, .ql-toolbar button.ql-active .ql-fill { fill: var(--accent); }
.ql-toolbar .ql-picker-label:hover, .ql-toolbar .ql-picker-label.ql-active { color: var(--accent); }
.ql-toolbar .ql-picker-options { background: var(--bg-2); border-color: var(--border); }
.blog-image-option { cursor:pointer; display:flex; flex-direction:column; align-items:center; gap:0.5rem; }
.blog-image-option input[type=radio] { display:none; }
.blog-image-option img { width:100%; height:90px; object-fit:cover; border-radius:var(--radius); border:2px solid var(--border); transition:border-color 0.2s, box-shadow 0.2s; }
.blog-image-option input:checked + img { border-color:var(--accent); box-shadow:0 0 0 3px rgba(230,57,70,0.25); }
.blog-image-option span { font-size:0.8rem; color:var(--text-muted); }
</style>
@endpush

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Edit Post</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.blog.index') }}" style="color:var(--accent);">← All Posts</a></p>
        </div>
        @if($post->isPublished())
        <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="btn btn--outline btn--sm">View Live ↗</a>
        @endif
    </div>

    @if(session('flash_success'))
    <div class="alert alert--success" style="margin-bottom:1.5rem;">{{ session('flash_success') }}</div>
    @endif

    @if($errors->any())
    <div class="alert" style="background:rgba(230,57,70,0.1);border:1px solid rgba(230,57,70,0.3);border-radius:var(--radius);padding:1rem 1.25rem;margin-bottom:1.5rem;color:var(--accent);">
        <strong>Please fix the following:</strong>
        <ul style="margin:0.5rem 0 0 1.25rem;">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.blog.update', $post->id) }}" id="blog-form">
        @csrf
        @method('PATCH')

        <div class="settings-grid" style="align-items:start;">

            {{-- Main content --}}
            <div style="grid-column:1/-1;">
                <div class="settings-card">

                    <div class="form-group" style="margin-bottom:1.25rem;">
                        <label class="form-label">Title <span style="color:var(--accent);">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $post->title) }}"
                            class="form-input" style="width:100%;font-size:1.1rem;">
                        @error('title')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group" style="margin-bottom:1.25rem;">
                        <label class="form-label">Content <span style="color:var(--accent);">*</span></label>
                        <div id="quill-editor">{!! old('content', $post->content) !!}</div>
                        <input type="hidden" name="content" id="content-input">
                        @error('content')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Excerpt <span style="color:var(--text-muted);font-weight:400;">(optional)</span></label>
                        <textarea name="excerpt" rows="2" class="form-input"
                            style="width:100%;resize:vertical;"
                            placeholder="Short summary shown on blog index…">{{ old('excerpt', $post->excerpt) }}</textarea>
                        @error('excerpt')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                </div>
            </div>

            {{-- Sidebar meta --}}
            <div class="settings-card" style="grid-column:1/-1;max-width:640px;">
                <h2 class="settings-card__title">Post Details</h2>

                <div class="form-group" style="margin-bottom:1.25rem;">
                    <label class="form-label">Author <span style="color:var(--accent);">*</span></label>
                    <input type="text" name="author" value="{{ old('author', $post->author) }}"
                        class="form-input" style="width:100%;">
                    @error('author')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group" style="margin-bottom:1.25rem;">
                    <label class="form-label">Cover Image <span style="color:var(--accent);">*</span></label>
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.75rem;margin-top:0.5rem;">
                        @foreach(\App\Models\BlogPost::imageOptions() as $key)
                        <label class="blog-image-option">
                            <input type="radio" name="image" value="{{ $key }}"
                                {{ old('image', $post->image) === $key ? 'checked' : '' }}>
                            <img src="{{ \App\Models\BlogPost::imagePath($key) }}"
                                 alt="{{ \App\Models\BlogPost::imageLabel($key) }}">
                            <span>{{ \App\Models\BlogPost::imageLabel($key) }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('image')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Publishing</label>
                    <div style="display:flex;flex-direction:column;gap:0.75rem;margin-top:0.25rem;">
                        @if(!$post->isPublished())
                        <label style="display:flex;align-items:center;gap:0.6rem;cursor:pointer;">
                            <input type="checkbox" name="publish_now" value="1"
                                style="accent-color:var(--accent);width:16px;height:16px;"
                                {{ old('publish_now') ? 'checked' : '' }}
                                id="publish-now-check">
                            <span style="font-size:0.9rem;">Publish immediately</span>
                        </label>
                        @else
                        <p style="font-size:0.875rem;color:var(--text-muted);">
                            Published on {{ $post->published_at->format('d M Y, g:ia') }}
                        </p>
                        @endif
                        <div id="scheduled-date-wrap">
                            <label class="form-label" style="font-size:0.82rem;margin-bottom:0.35rem;">
                                {{ $post->isPublished() ? 'Published date:' : 'Or schedule for a specific date:' }}
                            </label>
                            <input type="datetime-local" name="published_at"
                                value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}"
                                class="form-input" style="width:100%;">
                        </div>
                        @if($post->isPublished())
                        <p style="font-size:0.8rem;color:var(--text-muted);">Set to a future date to reschedule, or clear the field to revert to draft.</p>
                        @endif
                    </div>
                    @error('published_at')<p class="form-error">{{ $message }}</p>@enderror
                </div>

            </div>

            <div style="grid-column:1/-1;display:flex;gap:0.75rem;align-items:center;padding-bottom:2rem;">
                <button type="submit" class="btn btn--primary">Save Changes</button>
                <a href="{{ route('admin.blog.index') }}" class="btn btn--outline">Cancel</a>
                <form method="POST" action="{{ route('admin.blog.destroy', $post->id) }}"
                      style="margin-left:auto;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn--danger btn--sm"
                        data-confirm="Permanently delete &quot;{{ e($post->title) }}&quot;?">
                        Delete Post
                    </button>
                </form>
            </div>

        </div>
    </form>

</div>

<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
(function () {
    var quill = new Quill('#quill-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [2, 3, 4, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['blockquote', 'code-block'],
                ['link'],
                [{ align: [] }],
                ['clean']
            ]
        }
    });

    document.getElementById('blog-form').addEventListener('submit', function () {
        document.getElementById('content-input').value = quill.root.innerHTML;
    });

    var publishNow = document.getElementById('publish-now-check');
    var scheduledWrap = document.getElementById('scheduled-date-wrap');
    if (publishNow) {
        function toggleScheduled() {
            scheduledWrap.style.opacity = publishNow.checked ? '0.4' : '1';
            scheduledWrap.querySelector('input').disabled = publishNow.checked;
        }
        publishNow.addEventListener('change', toggleScheduled);
        toggleScheduled();
    }
})();
</script>
@endsection
