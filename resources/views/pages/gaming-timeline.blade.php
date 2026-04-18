@extends('layouts.app')

@section('title', 'History of Gaming — A Timeline of Key Moments')
@section('meta_description', 'From Tennis for Two in 1958 to the latest generation of consoles — explore the most important events in video game history.')
@section('canonical', route('gaming-timeline'))

@push('head_meta')
<style>
    .tl-hero {
        background: linear-gradient(135deg, var(--bg-2) 0%, var(--bg) 100%);
        border-bottom: 1px solid var(--border);
        padding: 3.5rem 0 3rem;
    }

    /* Timeline layout */
    .timeline {
        position: relative;
        max-width: 860px;
        margin: 0 auto;
        padding: 2rem 0 4rem;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 50%;
        top: 0; bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, transparent, var(--border) 5%, var(--border) 95%, transparent);
        transform: translateX(-50%);
    }

    .tl-item {
        display: grid;
        grid-template-columns: 1fr 60px 1fr;
        gap: 0;
        margin-bottom: 2.5rem;
        position: relative;
    }

    /* Left-side card */
    .tl-item:nth-child(odd)  .tl-card { grid-column: 1; text-align: right; padding-right: 2rem; }
    .tl-item:nth-child(odd)  .tl-mid  { grid-column: 2; }
    .tl-item:nth-child(odd)  .tl-empty{ grid-column: 3; }

    /* Right-side card */
    .tl-item:nth-child(even) .tl-empty{ grid-column: 1; }
    .tl-item:nth-child(even) .tl-mid  { grid-column: 2; }
    .tl-item:nth-child(even) .tl-card { grid-column: 3; text-align: left;  padding-left: 2rem; }

    .tl-card {
        background: var(--bg-2);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 1.1rem 1.25rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .tl-card:hover {
        border-color: rgba(230,57,70,0.35);
        box-shadow: 0 4px 20px rgba(230,57,70,0.06);
    }

    .tl-cover {
        width: 54px;
        height: 72px;
        object-fit: cover;
        border-radius: 5px;
        border: 1px solid var(--border);
        flex-shrink: 0;
    }
    .tl-card-inner {
        display: flex;
        gap: 0.85rem;
        align-items: flex-start;
    }
    .tl-card-text { flex: 1; min-width: 0; }
    /* Right-aligned cards: image on the left */
    .tl-item:nth-child(even) .tl-card-inner { flex-direction: row; }
    /* Left-aligned cards: keep image on right to push towards spine */
    .tl-item:nth-child(odd)  .tl-card-inner { flex-direction: row-reverse; }

    .tl-mid {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0;
        position: relative;
        z-index: 1;
    }
    .tl-year {
        background: var(--accent);
        color: #fff;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        padding: 0.3rem 0.55rem;
        border-radius: 6px;
        white-space: nowrap;
        margin-top: 1rem;
    }
    .tl-dot {
        width: 12px; height: 12px;
        border-radius: 50%;
        background: var(--accent);
        border: 3px solid var(--bg);
        margin-top: 0.5rem;
        flex-shrink: 0;
    }

    .tl-tag {
        display: inline-block;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
        margin-bottom: 0.5rem;
    }
    .tl-tag--console  { background: rgba(59,130,246,0.15);  color: #60a5fa; }
    .tl-tag--game     { background: rgba(230,57,70,0.12);   color: var(--accent); }
    .tl-tag--industry { background: rgba(16,185,129,0.12);  color: #34d399; }
    .tl-tag--tech     { background: rgba(251,191,36,0.12);  color: #fbbf24; }
    .tl-tag--mobile   { background: rgba(167,139,250,0.12); color: #a78bfa; }

    .tl-title {
        font-size: 0.97rem;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 0.4rem;
        line-height: 1.35;
    }
    .tl-desc {
        font-size: 0.83rem;
        color: var(--text-muted);
        line-height: 1.65;
        margin: 0;
    }

    /* Mobile: single column */
    @media (max-width: 640px) {
        .timeline::before { left: 20px; }
        .tl-item { grid-template-columns: 40px 1fr; gap: 0; }
        .tl-item:nth-child(odd)  .tl-card,
        .tl-item:nth-child(even) .tl-card  { grid-column: 2; text-align: left; padding-left: 1.25rem; padding-right: 0; }
        .tl-item:nth-child(odd)  .tl-mid,
        .tl-item:nth-child(even) .tl-mid   { grid-column: 1; }
        .tl-item:nth-child(odd)  .tl-empty,
        .tl-item:nth-child(even) .tl-empty { display: none; }
        .tl-year { font-size: 0.65rem; padding: 0.2rem 0.4rem; }
        .tl-item:nth-child(odd)  .tl-card-inner { flex-direction: row; }
        .tl-cover { width: 44px; height: 58px; }
    }
</style>
@endpush

@section('content')

<!-- ===== HERO ===== -->
<div class="tl-hero">
    <div class="container">
        <p style="font-size:0.75rem; letter-spacing:0.15em; text-transform:uppercase; color:var(--accent); font-weight:700; margin-bottom:0.5rem;">History</p>
        <h1 class="section-title">The History of Video Games</h1>
        <p style="color:var(--text-muted); font-size:0.97rem; line-height:1.75; margin-top:0.85rem; max-width:600px;">
            From a physics experiment in 1958 to a global industry worth hundreds of billions — the story of video games is one of the fastest and most remarkable cultural revolutions in human history.
        </p>
        <div style="display:flex; flex-wrap:wrap; gap:0.5rem; margin-top:1.5rem;">
            <span class="tl-tag tl-tag--console">Console</span>
            <span class="tl-tag tl-tag--game">Landmark Game</span>
            <span class="tl-tag tl-tag--industry">Industry</span>
            <span class="tl-tag tl-tag--tech">Technology</span>
            <span class="tl-tag tl-tag--mobile">Mobile</span>
        </div>
    </div>
</div>

<!-- ===== TIMELINE ===== -->
<section class="section">
    <div class="container">
    <div class="timeline">

        @foreach($events as $event)
        @php
            $tagLabel   = match($event['tag']) { 'game' => 'Landmark Game', 'tech' => 'Technology', 'mobile' => 'Mobile', default => ucfirst($event['tag']) };
            $coverUrl   = (!empty($event['igdb_slug']) && isset($imageMap[$event['igdb_slug']])) ? $imageMap[$event['igdb_slug']] : null;
        @endphp
        <div class="tl-item fade-up">
            @if($loop->odd)
                <div class="tl-card">
                    <span class="tl-tag tl-tag--{{ $event['tag'] }}">{{ $tagLabel }}</span>
                    <div class="tl-card-inner">
                        <div class="tl-card-text">
                            <div class="tl-title">{{ $event['title'] }}</div>
                            <p class="tl-desc">{{ $event['desc'] }}</p>
                        </div>
                        @if($coverUrl)
                        <img src="{{ $coverUrl }}" alt="{{ $event['title'] }} cover" class="tl-cover" loading="lazy">
                        @endif
                    </div>
                </div>
                <div class="tl-mid">
                    <div class="tl-year">{{ $event['year'] }}</div>
                    <div class="tl-dot"></div>
                </div>
                <div class="tl-empty"></div>
            @else
                <div class="tl-empty"></div>
                <div class="tl-mid">
                    <div class="tl-year">{{ $event['year'] }}</div>
                    <div class="tl-dot"></div>
                </div>
                <div class="tl-card">
                    <span class="tl-tag tl-tag--{{ $event['tag'] }}">{{ $tagLabel }}</span>
                    <div class="tl-card-inner">
                        @if($coverUrl)
                        <img src="{{ $coverUrl }}" alt="{{ $event['title'] }} cover" class="tl-cover" loading="lazy">
                        @endif
                        <div class="tl-card-text">
                            <div class="tl-title">{{ $event['title'] }}</div>
                            <p class="tl-desc">{{ $event['desc'] }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        @endforeach

    </div>
    </div>
</section>

<!-- ===== CTA ===== -->
<section class="section" style="border-top:1px solid var(--border); background:rgba(255,255,255,0.02);">
    <div class="container" style="text-align:center;">
        <h2 style="font-size:1.2rem; font-weight:700; margin-bottom:0.75rem;">Own a Piece of Gaming History?</h2>
        <p style="color:var(--text-muted); font-size:0.93rem; max-width:460px; margin:0 auto 1.5rem; line-height:1.7;">
            If you have games from any era sitting on the shelf, find out what they're worth and sell them for cash — fast, free collection, no hassle.
        </p>
        <a href="{{ route('search') }}" class="btn btn--primary">💰 See What Your Games Are Worth</a>
    </div>
</section>

@endsection
