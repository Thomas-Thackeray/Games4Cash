@extends('layouts.app')

@section('title', 'Gaming Blog')
@section('seo_title', 'Gaming Blog — News, Reviews & Tips | ' . config('app.name'))
@section('meta_description', 'Read the latest gaming news, in-depth reviews, and tips on selling your game collection for cash. Updated regularly by the ' . config('app.name') . ' team.')
@section('canonical', route('blog.index'))

@push('head_meta')
@php
$blogSchema = [
    '@context' => 'https://schema.org',
    '@type'    => 'Blog',
    'name'     => config('app.name') . ' Gaming Blog',
    'url'      => route('blog.index'),
    'description' => 'Gaming news, reviews, and tips from the ' . config('app.name') . ' team.',
];
@endphp
<script type="application/ld+json">{!! json_encode($blogSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

<!-- Blog hero -->
<div style="background:var(--bg-2);border-bottom:1px solid var(--border);padding:3.5rem 0 2.5rem;">
    <div class="container">
        <p style="font-size:0.8rem;letter-spacing:0.15em;text-transform:uppercase;color:var(--accent);font-weight:700;margin-bottom:0.5rem;">Blog</p>
        <h1 class="section-title">Gaming News &amp; Guides</h1>
        <p style="color:var(--text-muted);max-width:560px;margin-top:0.75rem;font-size:0.95rem;line-height:1.7;">
            The latest gaming news, reviews, and advice on getting the best price for your collection.
        </p>
    </div>
</div>

<section class="section">
    <div class="container">

        @if($posts->isEmpty())
        <div class="empty-state">
            <div class="icon">📝</div>
            <h3>No posts yet</h3>
            <p>Check back soon — we're working on something.</p>
        </div>
        @else

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.75rem;">
            @foreach($posts as $post)
            <article style="background:var(--bg-2);border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;display:flex;flex-direction:column;transition:border-color 0.2s,transform 0.2s;"
                     onmouseenter="this.style.borderColor='rgba(230,57,70,0.4)';this.style.transform='translateY(-2px)'"
                     onmouseleave="this.style.borderColor='var(--border)';this.style.transform=''">
                <a href="{{ route('blog.show', $post->slug) }}" style="display:block;overflow:hidden;aspect-ratio:800/420;">
                    <img src="{{ \App\Models\BlogPost::imagePath($post->image) }}"
                         alt="{{ e($post->title) }}"
                         style="width:100%;height:100%;object-fit:cover;display:block;transition:transform 0.3s;"
                         onmouseenter="this.style.transform='scale(1.03)'"
                         onmouseleave="this.style.transform=''">
                </a>
                <div style="padding:1.4rem;flex:1;display:flex;flex-direction:column;">
                    <div style="display:flex;align-items:center;gap:0.6rem;margin-bottom:0.75rem;">
                        <span style="font-size:0.75rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--accent);">
                            {{ \App\Models\BlogPost::imageLabel($post->image) }}
                        </span>
                        <span style="color:var(--border);">·</span>
                        <time datetime="{{ $post->published_at->toDateString() }}"
                              style="font-size:0.78rem;color:var(--text-muted);">
                            {{ $post->published_at->format('d M Y') }}
                        </time>
                    </div>
                    <h2 style="font-size:1.05rem;font-weight:700;margin-bottom:0.6rem;line-height:1.4;">
                        <a href="{{ route('blog.show', $post->slug) }}"
                           style="color:var(--text);text-decoration:none;"
                           onmouseenter="this.style.color='var(--accent)'"
                           onmouseleave="this.style.color='var(--text)'">
                            {{ $post->title }}
                        </a>
                    </h2>
                    @if($post->excerpt)
                    <p style="font-size:0.875rem;color:var(--text-muted);line-height:1.7;flex:1;">
                        {{ $post->excerpt }}
                    </p>
                    @endif
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:1rem;padding-top:0.9rem;border-top:1px solid var(--border);">
                        <span style="font-size:0.8rem;color:var(--text-muted);">By {{ $post->author }}</span>
                        <a href="{{ route('blog.show', $post->slug) }}" class="btn btn--outline btn--xs">Read →</a>
                    </div>
                </div>
            </article>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($posts->hasPages())
        <div class="pagination" style="margin-top:3rem;">
            @if($posts->onFirstPage())
            <span class="page-btn" style="opacity:0.4;">← Prev</span>
            @else
            <a href="{{ $posts->previousPageUrl() }}" class="page-btn">← Prev</a>
            @endif

            @foreach($posts->getUrlRange(1, $posts->lastPage()) as $page => $url)
            <a href="{{ $url }}" class="page-btn {{ $page === $posts->currentPage() ? 'active' : '' }}">{{ $page }}</a>
            @endforeach

            @if($posts->hasMorePages())
            <a href="{{ $posts->nextPageUrl() }}" class="page-btn">Next →</a>
            @else
            <span class="page-btn" style="opacity:0.4;">Next →</span>
            @endif
        </div>
        @endif

        @endif
    </div>
</section>

@endsection
