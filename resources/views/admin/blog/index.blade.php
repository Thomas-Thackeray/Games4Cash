@extends('layouts.app')
@section('title', 'Blog Posts')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Blog Posts</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.dashboard') }}" style="color:var(--accent);">← Dashboard</a></p>
        </div>
        <div style="display:flex;gap:0.75rem;align-items:center;">
            <a href="{{ route('blog.index') }}" target="_blank" class="btn btn--outline btn--sm">View Blog ↗</a>
            <a href="{{ route('admin.blog.create') }}" class="btn btn--primary btn--sm">+ New Post</a>
        </div>
    </div>

    @if(session('flash_success'))
    <div class="alert alert--success" style="margin-bottom:1.5rem;">{{ session('flash_success') }}</div>
    @endif

    @if($posts->isEmpty())
    <div class="empty-state" style="padding:4rem 0;">
        <div class="icon">📝</div>
        <h3>No posts yet</h3>
        <p>Create your first blog post to get started.</p>
        <a href="{{ route('admin.blog.create') }}" class="btn btn--primary btn--sm" style="margin-top:1rem;">Write a Post</a>
    </div>
    @else

    {{-- Desktop table --}}
    <div class="admin-table-wrap admin-table-wrap--desktop-only">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Status</th>
                    <th>Published</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($posts as $post)
                <tr>
                    <td style="width:64px;">
                        <img src="{{ \App\Models\BlogPost::imagePath($post->image) }}"
                             alt="{{ \App\Models\BlogPost::imageLabel($post->image) }}"
                             style="width:56px;height:32px;object-fit:cover;border-radius:4px;background:var(--bg-2);">
                    </td>
                    <td>
                        <span style="font-weight:600;color:var(--text);">{{ $post->title }}</span>
                        <br>
                        <span style="font-size:0.78rem;color:var(--text-muted);">/blog/{{ $post->slug }}</span>
                    </td>
                    <td class="admin-td-muted">{{ $post->author }}</td>
                    <td>
                        @if($post->isPublished())
                        <span class="admin-badge admin-badge--ok">Published</span>
                        @elseif($post->published_at)
                        <span class="admin-badge" style="background:rgba(251,191,36,0.12);color:#fbbf24;border-color:rgba(251,191,36,0.3);">Scheduled</span>
                        @else
                        <span class="admin-badge" style="background:rgba(148,163,184,0.12);color:var(--text-muted);border-color:rgba(148,163,184,0.2);">Draft</span>
                        @endif
                    </td>
                    <td class="admin-td-muted">
                        {{ $post->published_at ? $post->published_at->format('d M Y') : '—' }}
                    </td>
                    <td>
                        <div style="display:flex;gap:0.4rem;align-items:center;">
                            @if($post->isPublished())
                            <a href="{{ route('blog.show', $post->slug) }}" target="_blank"
                               class="btn btn--outline btn--xs" title="View live post">View</a>
                            @endif
                            <a href="{{ route('admin.blog.edit', $post->id) }}" class="btn btn--outline btn--sm">Edit</a>
                            <form method="POST" action="{{ route('admin.blog.destroy', $post->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn--danger btn--xs"
                                    data-confirm="Delete &quot;{{ e($post->title) }}&quot;? This cannot be undone.">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Mobile cards --}}
    <div class="admin-mobile-cards">
        @foreach($posts as $post)
        <div class="admin-mobile-card">
            <div class="admin-mobile-card__header">
                <div style="flex:1;min-width:0;">
                    <p style="font-weight:600;color:var(--text);margin-bottom:0.2rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $post->title }}</p>
                    <p style="font-size:0.78rem;color:var(--text-muted);">{{ $post->author }}</p>
                </div>
                <div style="display:flex;gap:0.4rem;flex-shrink:0;align-items:center;">
                    <a href="{{ route('admin.blog.edit', $post->id) }}" class="btn btn--outline btn--sm">Edit</a>
                    <form method="POST" action="{{ route('admin.blog.destroy', $post->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn--danger btn--xs"
                            data-confirm="Delete &quot;{{ e($post->title) }}&quot;?">Delete</button>
                    </form>
                </div>
            </div>
            <div class="admin-mobile-card__meta">
                @if($post->isPublished())
                <span class="admin-badge admin-badge--ok">Published</span>
                @elseif($post->published_at)
                <span class="admin-badge" style="background:rgba(251,191,36,0.12);color:#fbbf24;border-color:rgba(251,191,36,0.3);">Scheduled</span>
                @else
                <span class="admin-badge" style="background:rgba(148,163,184,0.12);color:var(--text-muted);border-color:rgba(148,163,184,0.2);">Draft</span>
                @endif
                <span>{{ $post->published_at ? $post->published_at->format('d M Y') : 'Not published' }}</span>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($posts->hasPages())
    <div class="history-pagination" style="margin-top:1.5rem;">
        <span class="history-pagination__info">
            Showing {{ $posts->firstItem() }}–{{ $posts->lastItem() }} of {{ $posts->total() }} posts
        </span>
        <div class="history-pagination__btns">
            @if($posts->onFirstPage())
                <span class="btn btn--outline btn--sm history-pagination__btn--disabled">← Previous</span>
            @else
                <a href="{{ $posts->previousPageUrl() }}" class="btn btn--outline btn--sm">← Previous</a>
            @endif
            @if($posts->hasMorePages())
                <a href="{{ $posts->nextPageUrl() }}" class="btn btn--outline btn--sm">Next →</a>
            @else
                <span class="btn btn--outline btn--sm history-pagination__btn--disabled">Next →</span>
            @endif
        </div>
    </div>
    @endif

    @endif

</div>
@endsection
