@extends('layouts.app')
@section('title', 'How Much Is My Game Worth? | Sell Games for Cash | ' . config('app.name'))
@section('meta_description', 'Find out how much cash you can get for your old games. Search any title and see our instant trade-in prices by platform — then book a free collection.')

@section('content')
<div class="container" style="padding: 0 0 5rem;">

    {{-- Hero --}}
    <section style="text-align:center; padding: 4rem 1rem 3rem;">
        <p style="font-size:0.8rem; font-weight:700; letter-spacing:0.15em; text-transform:uppercase; color:var(--accent); margin-bottom:0.75rem;">Instant Trade-In Valuations</p>
        <h1 style="font-size:clamp(2rem,5vw,3rem); font-weight:900; line-height:1.1; margin:0 0 1rem;">How Much Is My Game Worth?</h1>
        <p style="font-size:1.1rem; color:var(--text-muted); max-width:540px; margin:0 auto 2.5rem; line-height:1.6;">
            Search any title below and we'll show you exactly how much cash we'll pay — instantly, with no obligation.
        </p>

        {{-- Search box --}}
        <div style="max-width:560px; margin:0 auto; position:relative;">
            <input
                id="worth-search"
                type="search"
                placeholder="Type a game name, e.g. GTA V, FIFA 24…"
                autocomplete="off"
                style="width:100%; padding:1rem 1.25rem; font-size:1rem; border:2px solid var(--border); border-radius:var(--radius); background:var(--bg-card); color:var(--text); outline:none; box-sizing:border-box; font-family:'Outfit',sans-serif;"
            >
            <div id="worth-spinner" style="display:none; position:absolute; right:1rem; top:50%; transform:translateY(-50%); width:18px; height:18px; border:2px solid var(--border); border-top-color:var(--accent); border-radius:50%; animation:spin 0.7s linear infinite;"></div>
        </div>
    </section>

    {{-- Results --}}
    <div id="worth-results" style="max-width:760px; margin:0 auto; display:none;"></div>

    {{-- How it works --}}
    <section id="how-it-works" style="max-width:860px; margin:4rem auto 0; text-align:center;">
        <h2 style="font-size:1.6rem; font-weight:800; margin-bottom:0.5rem;">How It Works</h2>
        <p style="color:var(--text-muted); margin-bottom:2.5rem;">Selling your games for cash is fast, fair, and completely free.</p>
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1.5rem; text-align:center;">
            @foreach([
                ['🔍', 'Search Your Game',      'Type the title above and get an instant cash price.'],
                ['🛒', 'Add to Cash Basket',    'Select your platform and condition, then add to your basket.'],
                ['📦', 'Book a Collection',     'Submit your basket and we\'ll arrange a free collection from your door.'],
                ['💷', 'Get Paid',              'Once inspected we\'ll send your cash — straight to your bank.'],
            ] as [$icon, $title, $desc])
            <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); padding:1.75rem 1.25rem;">
                <div style="font-size:2rem; margin-bottom:0.75rem;">{{ $icon }}</div>
                <div style="font-weight:700; margin-bottom:0.4rem;">{{ $title }}</div>
                <p style="font-size:0.85rem; color:var(--text-muted); margin:0; line-height:1.5;">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </section>

    {{-- Trust signals --}}
    <section style="max-width:680px; margin:4rem auto 0; text-align:center;">
        <h2 style="font-size:1.4rem; font-weight:800; margin-bottom:1.5rem;">Why Sell With Us?</h2>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; text-align:left;">
            @foreach([
                ['✅', 'Free doorstep collection — we come to you'],
                ['💷', 'Fair, transparent prices — no hidden fees'],
                ['⚡', 'Fast payment once games are inspected'],
                ['🎮', 'We accept all major platforms and conditions'],
                ['📋', 'Online order tracking every step of the way'],
                ['🔒', 'Safe, secure and fully insured collections'],
            ] as [$icon, $text])
            <div style="display:flex; gap:0.6rem; align-items:flex-start; font-size:0.9rem;">
                <span>{{ $icon }}</span>
                <span style="color:var(--text-muted); line-height:1.5;">{{ $text }}</span>
            </div>
            @endforeach
        </div>
        <div style="margin-top:2rem; display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
            <a href="{{ route('search') }}" class="btn btn--outline">Browse All Games</a>
            <a href="{{ route('cash-basket.index') }}" class="btn btn--primary">View My Cash Basket</a>
        </div>
    </section>

</div>

<style>
@keyframes spin { to { transform: translateY(-50%) rotate(360deg); } }

.worth-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 1.25rem 1.5rem;
    margin-bottom: 0.75rem;
}
.worth-card__title {
    font-size: 1.05rem;
    font-weight: 700;
    margin: 0 0 0.75rem;
    color: var(--text);
    text-decoration: none;
}
.worth-card__title:hover { color: var(--accent); }
.worth-platform-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.worth-platform-chip {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 0.35rem 0.7rem;
    font-size: 0.82rem;
}
.worth-platform-chip__price {
    font-weight: 700;
    color: var(--accent);
}
.worth-no-results {
    text-align: center;
    padding: 2.5rem;
    color: var(--text-muted);
    font-size: 0.95rem;
}
</style>

@push('scripts')
<script>
(function () {
    const input    = document.getElementById('worth-search');
    const results  = document.getElementById('worth-results');
    const spinner  = document.getElementById('worth-spinner');
    let debounce   = null;

    input.addEventListener('input', function () {
        clearTimeout(debounce);
        const q = this.value.trim();

        if (q.length < 2) {
            results.style.display = 'none';
            results.innerHTML = '';
            spinner.style.display = 'none';
            return;
        }

        spinner.style.display = 'block';

        debounce = setTimeout(function () {
            fetch('/how-much-is-my-game-worth/search?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(function (data) {
                    spinner.style.display = 'none';
                    results.style.display = 'block';

                    if (!data.length) {
                        results.innerHTML = '<div class="worth-no-results">No games found matching "<strong>' + escapeHtml(q) + '</strong>". Try a different title.</div>';
                        return;
                    }

                    let html = '';
                    data.forEach(function (game) {
                        const titleHtml = game.game_url
                            ? '<a href="' + game.game_url + '" class="worth-card__title">' + escapeHtml(game.game_title) + '</a>'
                            : '<span class="worth-card__title">' + escapeHtml(game.game_title) + '</span>';

                        let chips = '';
                        game.platforms.forEach(function (p) {
                            chips += '<div class="worth-platform-chip">'
                                + '<span>' + escapeHtml(p.platform_name) + '</span>'
                                + '<span class="worth-platform-chip__price">' + escapeHtml(p.display_price) + '</span>'
                                + '</div>';
                        });

                        html += '<div class="worth-card">'
                            + '<div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap; margin-bottom:0.75rem;">'
                            + titleHtml;

                        if (game.game_url) {
                            html += '<a href="' + game.game_url + '" class="btn btn--primary" style="font-size:0.8rem; padding:0.4rem 0.9rem; white-space:nowrap;">View & Add to Basket</a>';
                        }

                        html += '</div>'
                            + '<div class="worth-platform-grid">' + chips + '</div>'
                            + '</div>';
                    });

                    results.innerHTML = html;
                })
                .catch(function () {
                    spinner.style.display = 'none';
                });
        }, 350);
    });

    function escapeHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
</script>
@endpush

@endsection
