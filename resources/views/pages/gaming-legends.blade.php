@extends('layouts.app')

@section('title', 'Gaming Legends — Notable People in Video Game History')
@section('meta_description', 'Meet the designers, programmers, and visionaries who shaped the video game industry — from Miyamoto and Carmack to Kojima and beyond.')
@section('canonical', route('gaming-legends'))

@push('head_meta')
<style>
    /* ── Hero ── */
    .gl-hero {
        background: linear-gradient(135deg, var(--bg-2) 0%, var(--bg) 100%);
        border-bottom: 1px solid var(--border);
        padding: 3.5rem 0 3rem;
    }

    /* ── Filter bar ── */
    .gl-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1.5rem;
    }
    .gl-filter-btn {
        background: var(--bg-2);
        border: 1px solid var(--border);
        color: var(--text-muted);
        border-radius: 999px;
        padding: 0.35rem 1rem;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: border-color 0.2s, color 0.2s, background 0.2s;
        font-family: 'Outfit', sans-serif;
    }
    .gl-filter-btn:hover,
    .gl-filter-btn.active {
        border-color: var(--accent);
        color: var(--accent);
        background: rgba(230,57,70,0.07);
    }

    /* ── Grid ── */
    .gl-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
        gap: 1.5rem;
        margin-top: 2.5rem;
    }

    /* ── Person card ── */
    .gl-card {
        background: var(--bg-2);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
    }
    .gl-card:hover {
        border-color: rgba(230,57,70,0.4);
        box-shadow: 0 6px 28px rgba(230,57,70,0.08);
        transform: translateY(-2px);
    }

    .gl-card-top {
        display: flex;
        gap: 1rem;
        padding: 1.25rem 1.25rem 1rem;
        align-items: flex-start;
    }

    .gl-avatar {
        width: 76px;
        height: 76px;
        border-radius: 50%;
        object-fit: cover;
        object-position: top center;
        border: 2px solid var(--border);
        flex-shrink: 0;
        background: var(--bg);
    }
    .gl-avatar-initials {
        width: 76px;
        height: 76px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(230,57,70,0.25), rgba(230,57,70,0.08));
        border: 2px solid rgba(230,57,70,0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--accent);
        flex-shrink: 0;
        letter-spacing: -0.02em;
    }

    .gl-card-meta {
        flex: 1;
        min-width: 0;
    }
    .gl-card-name {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text);
        line-height: 1.25;
        margin-bottom: 0.2rem;
    }
    .gl-card-role {
        font-size: 0.78rem;
        color: var(--accent);
        font-weight: 600;
        margin-bottom: 0.35rem;
    }
    .gl-card-born {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .gl-category-tag {
        display: inline-block;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
        margin-bottom: 0.5rem;
    }
    .gl-tag--designer  { background: rgba(59,130,246,0.12);  color: #60a5fa; }
    .gl-tag--developer { background: rgba(16,185,129,0.12);  color: #34d399; }
    .gl-tag--business  { background: rgba(251,191,36,0.12);  color: #fbbf24; }
    .gl-tag--pioneer   { background: rgba(230,57,70,0.12);   color: var(--accent); }
    .gl-tag--creator   { background: rgba(167,139,250,0.12); color: #a78bfa; }

    .gl-card-body {
        padding: 0 1.25rem 1rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }
    .gl-bio {
        font-size: 0.82rem;
        color: var(--text-muted);
        line-height: 1.7;
        margin: 0;
    }

    .gl-known {
        display: flex;
        flex-wrap: wrap;
        gap: 0.3rem;
    }
    .gl-known-label {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--text-muted);
        width: 100%;
        margin-bottom: 0.1rem;
    }
    .gl-known-pill {
        font-size: 0.73rem;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 4px;
        padding: 0.2rem 0.5rem;
        color: var(--text-muted);
        white-space: nowrap;
    }

    /* ── Count bar ── */
    .gl-count {
        font-size: 0.83rem;
        color: var(--text-muted);
        margin-top: 2rem;
    }
    .gl-count strong { color: var(--text); }

    /* ── Mobile ── */
    @media (max-width: 500px) {
        .gl-grid { grid-template-columns: 1fr; }
        .gl-avatar, .gl-avatar-initials { width: 60px; height: 60px; font-size: 1.2rem; }
    }
</style>
@endpush

@section('content')

<!-- ===== HERO ===== -->
<div class="gl-hero">
    <div class="container">
        <p style="font-size:0.75rem; letter-spacing:0.15em; text-transform:uppercase; color:var(--accent); font-weight:700; margin-bottom:0.5rem;">People</p>
        <h1 class="section-title">Gaming Legends</h1>
        <p style="color:var(--text-muted); font-size:0.97rem; line-height:1.75; margin-top:0.85rem; max-width:640px;">
            The designers, programmers, and visionaries who built the video game industry from scratch — and the executives, creators, and pioneers who shaped it into the largest entertainment medium on the planet.
        </p>

        <!-- Filter buttons -->
        <div class="gl-filters">
            <button class="gl-filter-btn active" data-filter="all">All</button>
            <button class="gl-filter-btn" data-filter="designer">Designers</button>
            <button class="gl-filter-btn" data-filter="developer">Developers</button>
            <button class="gl-filter-btn" data-filter="pioneer">Pioneers</button>
            <button class="gl-filter-btn" data-filter="business">Business</button>
            <button class="gl-filter-btn" data-filter="creator">Creators</button>
        </div>
    </div>
</div>

<!-- ===== GRID ===== -->
<section class="section">
    <div class="container">
        <p class="gl-count" id="gl-count"><strong>{{ count($people) }}</strong> people</p>
        <div class="gl-grid" id="gl-grid">

            @foreach($people as $person)
            @php
                $photo    = isset($photoMap[$person['wiki']]) ? $photoMap[$person['wiki']] : null;
                $initials = collect(explode(' ', $person['name']))->map(fn($w) => strtoupper($w[0]))->take(2)->implode('');
                $tagClass = 'gl-tag--' . $person['category'];
                $tagLabel = match($person['category']) {
                    'designer'  => 'Designer',
                    'developer' => 'Developer',
                    'pioneer'   => 'Pioneer',
                    'business'  => 'Business',
                    'creator'   => 'Creator',
                    default     => ucfirst($person['category']),
                };
            @endphp
            <div class="gl-card fade-up" data-category="{{ $person['category'] }}">
                <div class="gl-card-top">
                    @if($photo)
                        <img src="{{ $photo }}" alt="{{ $person['name'] }}" class="gl-avatar" loading="lazy">
                    @else
                        <div class="gl-avatar-initials">{{ $initials }}</div>
                    @endif
                    <div class="gl-card-meta">
                        <span class="gl-category-tag {{ $tagClass }}">{{ $tagLabel }}</span>
                        <div class="gl-card-name">{{ $person['flag'] }} {{ $person['name'] }}</div>
                        <div class="gl-card-role">{{ $person['role'] }}</div>
                        <div class="gl-card-born">b. {{ $person['born'] }}</div>
                    </div>
                </div>
                <div class="gl-card-body">
                    <p class="gl-bio">{{ $person['bio'] }}</p>
                    <div class="gl-known">
                        <span class="gl-known-label">Known for</span>
                        @foreach($person['known'] as $work)
                            <span class="gl-known-pill">{{ $work }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach

        </div>
    </div>
</section>

<!-- ===== CTA ===== -->
<section class="section" style="border-top:1px solid var(--border); background:rgba(255,255,255,0.02);">
    <div class="container" style="text-align:center;">
        <h2 style="font-size:1.2rem; font-weight:700; margin-bottom:0.75rem;">Own Games Made by These Legends?</h2>
        <p style="color:var(--text-muted); font-size:0.93rem; max-width:460px; margin:0 auto 1.5rem; line-height:1.7;">
            Browse thousands of titles, see what your games are worth, and get a free collection quote today.
        </p>
        <a href="{{ route('search') }}" class="btn btn--primary">💰 See What Your Games Are Worth</a>
    </div>
</section>

@endsection

@push('scripts')
<script>
(function () {
    var btns  = document.querySelectorAll('.gl-filter-btn');
    var cards = document.querySelectorAll('.gl-card');
    var count = document.getElementById('gl-count');

    btns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            btns.forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');

            var filter  = btn.dataset.filter;
            var visible = 0;

            cards.forEach(function (card) {
                var show = filter === 'all' || card.dataset.category === filter;
                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            count.innerHTML = '<strong>' + visible + '</strong> ' + (visible === 1 ? 'person' : 'people');
        });
    });
})();
</script>
@endpush
