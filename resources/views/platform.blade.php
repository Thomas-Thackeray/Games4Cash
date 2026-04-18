@extends('layouts.app')

@section('title', $platformName . ' Games — Sell for Cash')
@section('meta_description', $platformConfig['meta'] ?? 'Browse ' . $platformName . ' games, check what they\'re worth, and get cash for your collection. Free pickup, fast quotes.')
@section('canonical', route('platform.show', ['id' => $id, 'name' => $platformSlug]))

@section('content')

<!-- ===== PLATFORM HERO ===== -->
<div class="platform-hero">
    <div class="container">

        {{-- Top row: icon + title + highlights --}}
        <div style="display:grid; grid-template-columns:1fr auto; gap:3rem; align-items:start;">
            <div>
                <div class="platform-hero__inner" style="margin-bottom:1.25rem;">
                    <div class="platform-logo">{{ $platformIcon }}</div>
                    <div>
                        <p style="font-size:0.75rem; letter-spacing:0.15em; text-transform:uppercase; color:var(--accent); font-weight:700; margin-bottom:0.4rem;">
                            Platform
                        </p>
                        <h1 class="section-title" style="margin:0;">{{ $platformName }}</h1>
                    </div>
                </div>

                @if(!empty($platformConfig['desc']))
                <p style="color:var(--text-muted); max-width:600px; font-size:0.95rem; line-height:1.75;">
                    {{ $platformConfig['desc'] }}
                </p>
                @elseif(!empty($platform['summary']))
                <p style="color:var(--text-muted); max-width:600px; font-size:0.95rem; line-height:1.75;">
                    {{ e(truncate_text($platform['summary'], 220)) }}
                </p>
                @endif
            </div>

            @if(!empty($platformConfig['highlights']))
            <div style="min-width:240px; background:rgba(255,255,255,0.03); border:1px solid var(--border); border-radius:10px; padding:1.25rem 1.5rem;">
                <p style="font-size:0.72rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:var(--accent); margin-bottom:0.85rem;">Why sell with us</p>
                <ul style="list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:0.6rem;">
                    @foreach($platformConfig['highlights'] as $point)
                    <li style="display:flex; gap:0.6rem; align-items:flex-start; font-size:0.83rem; color:var(--text-muted); line-height:1.5;">
                        <span style="color:var(--accent); flex-shrink:0; margin-top:0.1rem;">✓</span>
                        {{ $point }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        {{-- Platform nav chips --}}
        <div style="display:flex; gap:0.65rem; margin-top:2rem; flex-wrap:wrap;">
            @foreach(config('igdb.platforms') as $pName => $pData)
            <a href="{{ route('platform.show', ['id' => $pData['id'], 'name' => $pData['slug'] ?? $pName]) }}"
               class="chip {{ $pData['id'] === $id ? 'active' : '' }}">
                {{ $pData['icon'] }} {{ $pName }}
            </a>
            @endforeach
        </div>

    </div>
</div>

<!-- ===== GAMES GRID ===== -->
<section class="section">
    <div class="container">
        @if($error)
        <div class="error-banner">⚠️ {{ e($error) }}</div>
        @elseif(empty($games))
        <div class="empty-state">
            <div class="icon">🎮</div>
            <h3>No games found</h3>
            <p>No rated games found for this platform yet.</p>
        </div>
        @else
        <div class="section-header fade-up">
            <h2 class="section-title">{{ $platformName }} Games</h2>
            <span style="color:var(--text-muted); font-size:0.875rem;">Page {{ $page }}</span>
        </div>

        <div class="games-grid games-grid--large fade-up">
            @foreach($games as $game)
            <x-game-card :game="$game" />
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="pagination">
            @if($page > 1)
            <a href="{{ route('platform.show', ['id' => $id, 'name' => $platformSlug, 'page' => $page - 1]) }}" class="page-btn">← Prev</a>
            @endif
            @for($i = max(1, $page - 2); $i <= min(20, $page + 4); $i++)
            <a href="{{ route('platform.show', ['id' => $id, 'name' => $platformSlug, 'page' => $i]) }}"
               class="page-btn {{ $i === $page ? 'active' : '' }}">
                {{ $i }}
            </a>
            @endfor
            @if(count($games) === $limit)
            <a href="{{ route('platform.show', ['id' => $id, 'name' => $platformSlug, 'page' => $page + 1]) }}" class="page-btn">Next →</a>
            @endif
        </div>
        @endif
    </div>
</section>

@if($page === 1 && !empty($platformConfig['seo']))
<section class="section" style="padding-top:0; border-top:1px solid var(--border);">
    <div class="container">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:3.5rem; align-items:start; padding-top:2.5rem;">

            {{-- Left: main SEO copy --}}
            <div>
                <h2 style="font-size:1.2rem; font-weight:700; color:var(--text); margin-bottom:1rem; line-height:1.4;">
                    {{ $platformConfig['seo']['heading'] }}
                </h2>
                <p style="color:var(--text-muted); font-size:0.95rem; line-height:1.8; margin-bottom:1rem;">
                    {{ $platformConfig['seo']['body'] }}
                </p>
                @if(!empty($platformConfig['seo']['body2']))
                <p style="color:var(--text-muted); font-size:0.95rem; line-height:1.8;">
                    {{ $platformConfig['seo']['body2'] }}
                </p>
                @endif
            </div>

            {{-- Right: how it works + highlights recap --}}
            <div>
                <h2 style="font-size:1.2rem; font-weight:700; color:var(--text); margin-bottom:1rem; line-height:1.4;">
                    How to Sell Your {{ $platformName }} Games
                </h2>
                <ol style="list-style:none; padding:0; margin:0 0 1.5rem 0; display:flex; flex-direction:column; gap:0.85rem; counter-reset:steps;">
                    @foreach([
                        'Browse the ' . $platformName . ' titles above and check the cash price for each game.',
                        'Add the games you want to sell to your cash basket — pick the right platform and condition.',
                        'Submit your quote. We\'ll confirm by email and arrange free collection from your door.',
                        'Once we\'ve received and checked your games, we\'ll pay you quickly with no deductions.',
                    ] as $step)
                    <li style="display:flex; gap:0.85rem; align-items:flex-start; font-size:0.88rem; color:var(--text-muted); line-height:1.55; counter-increment:steps;">
                        <span style="flex-shrink:0; width:22px; height:22px; border-radius:50%; background:rgba(230,57,70,0.15); border:1px solid rgba(230,57,70,0.35); color:var(--accent); font-size:0.72rem; font-weight:700; display:flex; align-items:center; justify-content:center; margin-top:0.1rem;">{{ $loop->iteration }}</span>
                        {{ $step }}
                    </li>
                    @endforeach
                </ol>

                <a href="{{ route('search') }}" class="btn btn--primary btn--sm">💰 See What Your Games Are Worth</a>
            </div>

        </div>
    </div>
</section>
@endif

@endsection
