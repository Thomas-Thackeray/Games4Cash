@extends('layouts.app')

@section('title', $post->title)
@section('seo_title', $post->title . ' | ' . config('app.name') . ' Blog')
@section('meta_description', $post->excerpt ?: $post->generateExcerpt(160))
@section('og_image', \App\Models\BlogPost::imagePath($post->image))
@section('og_type', 'article')
@section('canonical', route('blog.show', $post->slug))

@push('head_meta')
@php
$articleSchema = [
    '@context'      => 'https://schema.org',
    '@type'         => 'BlogPosting',
    'headline'      => $post->title,
    'description'   => $post->excerpt ?: $post->generateExcerpt(160),
    'image'         => \App\Models\BlogPost::imagePath($post->image),
    'author'        => ['@type' => 'Person', 'name' => $post->author],
    'publisher'     => ['@type' => 'Organization', 'name' => config('app.name'), 'url' => url('/')],
    'datePublished' => $post->published_at->toIso8601String(),
    'dateModified'  => $post->updated_at->toIso8601String(),
    'url'           => route('blog.show', $post->slug),
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => route('blog.show', $post->slug)],
];

$breadcrumbSchema = [
    '@context'        => 'https://schema.org',
    '@type'           => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home',  'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog',  'item' => route('blog.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title],
    ],
];
@endphp
<script type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

<article>

    {{-- Hero image --}}
    <div style="width:100%;aspect-ratio:800/320;overflow:hidden;background:var(--bg-2);border-bottom:1px solid var(--border);max-height:380px;">
        <img src="{{ \App\Models\BlogPost::imagePath($post->image) }}"
             alt="{{ e($post->title) }}"
             style="width:100%;height:100%;object-fit:cover;display:block;">
    </div>

    <div class="container" style="max-width:760px;padding:3rem 1rem 5rem;">

        {{-- Breadcrumb --}}
        <nav style="font-size:0.82rem;color:var(--text-muted);margin-bottom:1.75rem;">
            <a href="{{ route('home') }}" style="color:var(--text-muted);text-decoration:none;">Home</a>
            <span style="margin:0 0.4rem;opacity:0.5;">›</span>
            <a href="{{ route('blog.index') }}" style="color:var(--text-muted);text-decoration:none;">Blog</a>
            <span style="margin:0 0.4rem;opacity:0.5;">›</span>
            <span style="color:var(--text);">{{ $post->title }}</span>
        </nav>

        {{-- Category + date --}}
        <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem;">
            <span style="font-size:0.78rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--accent);">
                {{ \App\Models\BlogPost::imageLabel($post->image) }}
            </span>
            <span style="color:var(--border);">·</span>
            <time datetime="{{ $post->published_at->toDateString() }}"
                  style="font-size:0.82rem;color:var(--text-muted);">
                {{ $post->published_at->format('d F Y') }}
            </time>
        </div>

        {{-- Title --}}
        <h1 style="font-size:clamp(1.6rem,4vw,2.4rem);font-weight:700;line-height:1.25;margin-bottom:1.25rem;">
            {{ $post->title }}
        </h1>

        {{-- Author --}}
        <div style="display:flex;align-items:center;gap:0.75rem;padding:1rem 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border);margin-bottom:2.5rem;">
            <div style="width:36px;height:36px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.9rem;color:white;flex-shrink:0;">
                {{ strtoupper(substr($post->author, 0, 1)) }}
            </div>
            <div>
                <p style="font-weight:600;font-size:0.9rem;color:var(--text);margin:0;">{{ $post->author }}</p>
                <p style="font-size:0.78rem;color:var(--text-muted);margin:0;">{{ config('app.name') }}</p>
            </div>
        </div>

        {{-- Post content --}}
        <div class="blog-content">
            {!! $post->content !!}
        </div>

        {{-- Footer nav --}}
        <div style="margin-top:3.5rem;padding-top:1.5rem;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
            <a href="{{ route('blog.index') }}" class="btn btn--outline btn--sm">← Back to Blog</a>
            <a href="{{ route('search') }}" class="btn btn--primary btn--sm">Browse Games</a>
        </div>

    </div>
</article>

<style>
/* Blog content typography */
.blog-content {
    font-size: 1rem;
    line-height: 1.85;
    color: var(--text-muted);
}
.blog-content h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text);
    margin: 2rem 0 0.75rem;
    line-height: 1.3;
}
.blog-content h3 {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--text);
    margin: 1.75rem 0 0.6rem;
}
.blog-content h4 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text);
    margin: 1.5rem 0 0.5rem;
}
.blog-content p {
    margin: 0 0 1.25rem;
}
.blog-content a {
    color: var(--accent);
    text-decoration: underline;
    text-underline-offset: 3px;
}
.blog-content ul, .blog-content ol {
    margin: 0 0 1.25rem 1.5rem;
}
.blog-content li {
    margin-bottom: 0.4rem;
}
.blog-content blockquote {
    border-left: 3px solid var(--accent);
    margin: 1.5rem 0;
    padding: 0.75rem 1.25rem;
    background: var(--bg-2);
    border-radius: 0 var(--radius) var(--radius) 0;
    color: var(--text);
    font-style: italic;
}
.blog-content pre, .blog-content code {
    background: var(--bg-2);
    border: 1px solid var(--border);
    border-radius: 4px;
    font-family: monospace;
    font-size: 0.9rem;
}
.blog-content pre {
    padding: 1rem;
    overflow-x: auto;
    margin: 1.25rem 0;
}
.blog-content code {
    padding: 0.15rem 0.4rem;
}
.blog-content strong { color: var(--text); font-weight: 700; }
.blog-content em { font-style: italic; }
.blog-content img { max-width: 100%; border-radius: var(--radius); margin: 1rem 0; }
.blog-content hr { border: none; border-top: 1px solid var(--border); margin: 2rem 0; }
</style>

@endsection
