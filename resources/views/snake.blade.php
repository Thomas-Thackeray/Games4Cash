@extends('layouts.app')

@section('title', 'Snake — Games4Cash')
@section('meta_description', 'Play Snake and get on the leaderboard. How high can you score?')

@push('head_meta')
<style>
    .snake-wrap {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 2rem;
        align-items: start;
        max-width: 900px;
        margin: 0 auto;
    }
    .snake-canvas-col { display: flex; flex-direction: column; align-items: center; gap: 1.25rem; }
    #snake-canvas {
        display: block;
        border: 2px solid var(--border);
        border-radius: 10px;
        background: #0d0d0f;
        box-shadow: 0 0 40px rgba(230,57,70,0.08);
        cursor: pointer;
    }
    .snake-score-bar {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.88rem;
    }
    .snake-score-val {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--accent);
        font-variant-numeric: tabular-nums;
    }
    .snake-hiscore-val {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text-muted);
        font-variant-numeric: tabular-nums;
    }
    /* Overlay sits on top of canvas */
    .snake-overlay-wrap { position: relative; }
    #snake-overlay {
        position: absolute;
        inset: 0;
        border-radius: 10px;
        background: rgba(10,10,12,0.92);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        text-align: center;
        padding: 2rem;
        z-index: 10;
    }
    #snake-overlay.hidden { display: none; }
    .snake-overlay-title { font-size: 1.6rem; font-weight: 800; color: var(--text); }
    .snake-overlay-sub   { font-size: 0.88rem; color: var(--text-muted); }
    .snake-overlay-score { font-size: 2.5rem; font-weight: 900; color: var(--accent); line-height: 1; }
    #snake-name-form     { display: flex; flex-direction: column; gap: 0.6rem; width: 100%; max-width: 240px; }
    #snake-name-input    { text-align: center; }
    .snake-rank-badge {
        font-size: 1.1rem; font-weight: 800; color: var(--accent);
        background: rgba(230,57,70,0.1); border: 1px solid rgba(230,57,70,0.3);
        border-radius: 8px; padding: 0.5rem 1.25rem;
    }

    /* Leaderboard */
    .leaderboard-card {
        background: var(--bg-2);
        border: 1px solid var(--border);
        border-radius: 10px;
        overflow: hidden;
        position: sticky;
        top: 5rem;
    }
    .leaderboard-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border);
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--accent);
    }
    .leaderboard-row {
        display: grid;
        grid-template-columns: 28px 1fr auto auto;
        gap: 0.5rem;
        align-items: center;
        padding: 0.65rem 1.25rem;
        border-bottom: 1px solid rgba(255,255,255,0.04);
        font-size: 0.88rem;
        transition: background 0.15s;
    }
    .leaderboard-row:last-child { border-bottom: none; }
    .leaderboard-row.highlight { background: rgba(230,57,70,0.08); }
    .lb-rank  { font-weight: 700; color: var(--text-muted); font-size: 0.8rem; }
    .lb-rank.gold   { color: #f59e0b; }
    .lb-rank.silver { color: #94a3b8; }
    .lb-rank.bronze { color: #b45309; }
    .lb-name  { color: var(--text); font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .lb-score { font-weight: 700; color: var(--accent); font-variant-numeric: tabular-nums; }
    .lb-empty { padding: 1.5rem 1.25rem; color: var(--text-muted); font-size: 0.85rem; text-align: center; }

    .controls-hint {
        font-size: 0.78rem;
        color: var(--text-muted);
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        justify-content: center;
    }
    .controls-hint span { display: flex; align-items: center; gap: 0.3rem; }

    @media (max-width: 700px) {
        .snake-wrap { grid-template-columns: 1fr; }
        .leaderboard-card { position: static; }
        #snake-canvas { width: 100%; height: auto; }
    }
</style>
@endpush

@section('content')
<section class="section">
    <div class="container">

        <div class="section-header fade-up" style="margin-bottom:2rem;">
            <div>
                <h1 class="section-title">🐍 Snake</h1>
                <p style="color:var(--text-muted); font-size:0.9rem; margin-top:0.35rem;">Use arrow keys or WASD to play. Score big, get on the board.</p>
            </div>
        </div>

        <div class="snake-wrap fade-up">

            <!-- Game column -->
            <div class="snake-canvas-col">

                <!-- Score bar -->
                <div class="snake-score-bar" style="width:500px; max-width:100%;">
                    <div>
                        <div style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted); margin-bottom:0.15rem;">Score</div>
                        <div class="snake-score-val" id="score-display">0</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-muted); margin-bottom:0.15rem;">Best (session)</div>
                        <div class="snake-hiscore-val" id="hiscore-display">0</div>
                    </div>
                </div>

                <!-- Canvas + overlay -->
                <div class="snake-overlay-wrap" style="width:500px; max-width:100%;">
                    <canvas id="snake-canvas" width="500" height="500"></canvas>

                    <div id="snake-overlay">
                        <div class="snake-overlay-title" id="overlay-title">🐍 Snake</div>
                        <div class="snake-overlay-sub" id="overlay-sub">Click or press Space to start</div>
                        <div class="snake-overlay-score" id="overlay-score" style="display:none;"></div>

                        <div id="snake-name-form" style="display:none;">
                            <input id="snake-name-input" type="text" class="form-input" placeholder="Enter your name…" maxlength="30" autocomplete="off">
                            <button id="snake-submit-btn" class="btn btn--primary btn--sm">Submit Score</button>
                        </div>

                        <div id="overlay-rank" class="snake-rank-badge" style="display:none;"></div>
                        <button id="overlay-play-btn" class="btn btn--primary btn--sm" style="display:none;">Play Again</button>
                    </div>
                </div>

                <!-- Controls hint -->
                <div class="controls-hint">
                    <span>⬆⬇⬅➡ Arrow keys</span>
                    <span>WASD Move</span>
                    <span>Space Start / Pause</span>
                </div>
            </div>

            <!-- Leaderboard column -->
            <div class="leaderboard-card">
                <div class="leaderboard-header">🏆 Leaderboard</div>
                <div id="leaderboard-body">
                    @forelse($scores as $i => $s)
                    <div class="leaderboard-row">
                        <span class="lb-rank {{ $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : '')) }}">
                            {{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : '#' . ($i + 1))) }}
                        </span>
                        <span class="lb-name">{{ $s->name }}</span>
                        <span class="lb-score">{{ number_format($s->score) }}</span>
                        <span style="color:var(--text-muted); font-size:0.75rem; white-space:nowrap;">{{ $s->created_at->format('d M Y') }}</span>
                    </div>
                    @empty
                    <div class="lb-empty">No scores yet — be the first!</div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</section>

<script>
(function () {
    const COLS = 20, ROWS = 20, CELL = 25;
    const canvas  = document.getElementById('snake-canvas');
    const ctx     = canvas.getContext('2d');
    const scoreEl = document.getElementById('score-display');
    const hiEl    = document.getElementById('hiscore-display');
    const overlay = document.getElementById('snake-overlay');
    const oTitle  = document.getElementById('overlay-title');
    const oSub    = document.getElementById('overlay-sub');
    const oScore  = document.getElementById('overlay-score');
    const oRank   = document.getElementById('overlay-rank');
    const oPlay   = document.getElementById('overlay-play-btn');
    const nameForm = document.getElementById('snake-name-form');
    const nameIn   = document.getElementById('snake-name-input');
    const subBtn   = document.getElementById('snake-submit-btn');
    const lbBody   = document.getElementById('leaderboard-body');

    const COLORS = {
        bg:       '#0d0d0f',
        grid:     'rgba(255,255,255,0.03)',
        snake:    '#e63946',
        snakeHead:'#ff6b74',
        food:     '#fbbf24',
        foodGlow: 'rgba(251,191,36,0.4)',
        text:     '#ffffff',
    };

    let snake, dir, nextDir, food, score, hiScore = 0, gameLoop, speed, state, paused;

    function init() {
        snake   = [{x:10, y:10}, {x:9, y:10}, {x:8, y:10}];
        dir     = {x:1, y:0};
        nextDir = {x:1, y:0};
        score   = 0;
        speed   = 140;
        state   = 'idle'; // idle | playing | dead
        paused  = false;
        scoreEl.textContent = '0';
        placeFood();
        drawIdle();
    }

    function placeFood() {
        let pos;
        do {
            pos = {x: Math.floor(Math.random() * COLS), y: Math.floor(Math.random() * ROWS)};
        } while (snake.some(s => s.x === pos.x && s.y === pos.y));
        food = pos;
    }

    function drawGrid() {
        ctx.fillStyle = COLORS.bg;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.strokeStyle = COLORS.grid;
        ctx.lineWidth = 0.5;
        for (let x = 0; x <= COLS; x++) {
            ctx.beginPath(); ctx.moveTo(x * CELL, 0); ctx.lineTo(x * CELL, canvas.height); ctx.stroke();
        }
        for (let y = 0; y <= ROWS; y++) {
            ctx.beginPath(); ctx.moveTo(0, y * CELL); ctx.lineTo(canvas.width, y * CELL); ctx.stroke();
        }
    }

    function drawSnake() {
        snake.forEach((seg, i) => {
            const isHead = i === 0;
            const r = isHead ? 7 : 5;
            ctx.fillStyle = isHead ? COLORS.snakeHead : COLORS.snake;
            ctx.beginPath();
            ctx.roundRect(seg.x * CELL + 2, seg.y * CELL + 2, CELL - 4, CELL - 4, r);
            ctx.fill();
            if (isHead) {
                // Eyes
                ctx.fillStyle = '#0d0d0f';
                const ex = dir.x === 0 ? [5, 13] : (dir.x > 0 ? [14, 14] : [8, 8]);
                const ey = dir.y === 0 ? (dir.x > 0 ? [6, 16] : [6, 16]) : (dir.y > 0 ? [14, 14] : [8, 8]);
                ctx.beginPath(); ctx.arc(seg.x * CELL + ex[0], seg.y * CELL + ey[0], 2, 0, Math.PI * 2); ctx.fill();
                ctx.beginPath(); ctx.arc(seg.x * CELL + ex[1], seg.y * CELL + ey[1], 2, 0, Math.PI * 2); ctx.fill();
            }
        });
    }

    function drawFood() {
        const cx = food.x * CELL + CELL / 2, cy = food.y * CELL + CELL / 2;
        // Glow
        const grd = ctx.createRadialGradient(cx, cy, 2, cx, cy, CELL);
        grd.addColorStop(0, COLORS.foodGlow);
        grd.addColorStop(1, 'transparent');
        ctx.fillStyle = grd;
        ctx.beginPath(); ctx.arc(cx, cy, CELL, 0, Math.PI * 2); ctx.fill();
        // Apple
        ctx.fillStyle = COLORS.food;
        ctx.beginPath(); ctx.arc(cx, cy, CELL / 2 - 3, 0, Math.PI * 2); ctx.fill();
        ctx.fillStyle = '#166534';
        ctx.fillRect(cx - 1, cy - CELL / 2 + 2, 2, 5);
    }

    function drawIdle() {
        drawGrid();
        drawSnake();
        drawFood();
    }

    function step() {
        if (paused) return;
        dir = {...nextDir};
        const head = {x: snake[0].x + dir.x, y: snake[0].y + dir.y};

        // Wall collision
        if (head.x < 0 || head.x >= COLS || head.y < 0 || head.y >= ROWS) { return die(); }
        // Self collision
        if (snake.some(s => s.x === head.x && s.y === head.y)) { return die(); }

        snake.unshift(head);

        if (head.x === food.x && head.y === food.y) {
            score += 10;
            scoreEl.textContent = score;
            if (score > hiScore) { hiScore = score; hiEl.textContent = hiScore; }
            placeFood();
            // Speed up every 50pts
            if (score % 50 === 0 && speed > 60) { speed = Math.max(60, speed - 10); restart(); }
        } else {
            snake.pop();
        }

        drawGrid();
        drawFood();
        drawSnake();
    }

    function restart() {
        clearInterval(gameLoop);
        gameLoop = setInterval(step, speed);
    }

    function startGame() {
        state   = 'playing';
        paused  = false;
        overlay.classList.add('hidden');
        restart();
    }

    function die() {
        clearInterval(gameLoop);
        state = 'dead';
        drawGrid(); drawFood(); drawSnake();

        oTitle.textContent = 'Game Over';
        oScore.textContent = score;
        oScore.style.display = 'block';
        oSub.textContent = score > 0 ? 'Enter your name to save your score' : 'Better luck next time!';
        nameForm.style.display = score > 0 ? 'flex' : 'none';
        oRank.style.display  = 'none';
        oPlay.style.display  = 'block';
        oPlay.textContent    = 'Play Again';
        overlay.classList.remove('hidden');
        if (score > 0) { nameIn.value = ''; nameIn.focus(); }
    }

    function togglePause() {
        if (state !== 'playing') return;
        paused = !paused;
        if (paused) {
            clearInterval(gameLoop);
            oTitle.textContent = '⏸ Paused';
            oSub.textContent   = 'Press Space to resume';
            oScore.style.display = 'none';
            nameForm.style.display = 'none';
            oPlay.textContent = 'Resume';
            oPlay.style.display = 'block';
            oRank.style.display = 'none';
            overlay.classList.remove('hidden');
        } else {
            overlay.classList.add('hidden');
            restart();
        }
    }

    // Keyboard
    document.addEventListener('keydown', e => {
        if (document.activeElement === nameIn) return;
        const map = {
            ArrowUp:'up', ArrowDown:'down', ArrowLeft:'left', ArrowRight:'right',
            w:'up', s:'down', a:'left', d:'right',
            W:'up', S:'down', A:'left', D:'right',
        };
        const action = map[e.key];
        if (action) {
            e.preventDefault();
            if (state === 'idle' || state === 'dead') { init(); startGame(); return; }
            const moves = {
                up:    {x:0, y:-1}, down:  {x:0, y:1},
                left:  {x:-1, y:0}, right: {x:1, y:0},
            };
            const m = moves[action];
            // Prevent reversing
            if (m.x !== -dir.x || m.y !== -dir.y) nextDir = m;
        }
        if (e.key === ' ' || e.key === 'Spacebar') {
            e.preventDefault();
            if (state === 'idle' || state === 'dead') { init(); startGame(); }
            else togglePause();
        }
    });

    // Click canvas / overlay play btn
    canvas.addEventListener('click', () => {
        if (state === 'idle' || state === 'dead') { init(); startGame(); }
        else togglePause();
    });
    oPlay.addEventListener('click', () => {
        if (paused) { togglePause(); }
        else { init(); startGame(); }
    });

    // Score submission
    subBtn.addEventListener('click', submitScore);
    nameIn.addEventListener('keydown', e => { if (e.key === 'Enter') submitScore(); });

    function submitScore() {
        const name = nameIn.value.trim();
        if (!name) { nameIn.focus(); return; }

        subBtn.disabled = true;
        subBtn.textContent = 'Saving…';

        fetch('{{ route('snake.score') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({name, score}),
        })
        .then(r => r.json())
        .then(data => {
            nameForm.style.display = 'none';
            const medals = ['🥇','🥈','🥉'];
            oRank.textContent = data.rank <= 3
                ? medals[data.rank - 1] + ' #' + data.rank + ' on the leaderboard!'
                : 'You ranked #' + data.rank + '!';
            oRank.style.display = 'block';
            oPlay.style.display = 'block';
            updateLeaderboard(data.top10, data.rank <= 10 ? data.rank : null);
        })
        .catch(() => {
            subBtn.disabled = false;
            subBtn.textContent = 'Submit Score';
        });
    }

    function updateLeaderboard(top10, highlight) {
        const medals = ['🥇','🥈','🥉'];
        const rankClasses = ['gold','silver','bronze'];
        lbBody.innerHTML = top10.map((s, i) => {
            const pos = i + 1;
            const rankLabel = pos <= 3 ? medals[i] : '#' + pos;
            const rankCls   = rankClasses[i] || '';
            const hl        = highlight === pos ? ' highlight' : '';
            return `<div class="leaderboard-row${hl}">
                <span class="lb-rank ${rankCls}">${rankLabel}</span>
                <span class="lb-name">${escHtml(s.name)}</span>
                <span class="lb-score">${s.score.toLocaleString()}</span>
                <span style="color:var(--text-muted);font-size:0.75rem;white-space:nowrap;">${s.date}</span>
            </div>`;
        }).join('');
    }

    function escHtml(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // Boot
    init();
})();
</script>
<!-- ===== SNAKE HISTORY ===== -->
<section class="section" style="border-top:1px solid var(--border); padding-top:3rem;">
    <div class="container">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:3.5rem; align-items:start; max-width:900px;">
            <div>
                <h2 style="font-size:1.2rem; font-weight:700; color:var(--text); margin-bottom:1rem;">The History of Snake</h2>
                <p style="color:var(--text-muted); font-size:0.93rem; line-height:1.8; margin-bottom:1rem;">
                    Snake traces its roots back to 1976, when Gremlin Industries released <em>Blockade</em> — a two-player arcade game where each player controlled a line that grew as it moved, with the goal of forcing your opponent into a wall or your own trail. It was simple, addictive, and laid the foundation for everything that followed.
                </p>
                <p style="color:var(--text-muted); font-size:0.93rem; line-height:1.8;">
                    Variants appeared throughout the late 70s and 80s under names like <em>Worm</em> and <em>Nibbler</em>, but the concept truly exploded into mainstream culture in 1997 when Nokia developer Taneli Armanto wrote a version for the Nokia 6110. Pre-installed on hundreds of millions of phones worldwide, Nokia Snake became one of the most-played video games in history — not because people sought it out, but because it was simply <em>there</em>, on the device everyone carried.
                </p>
            </div>
            <div>
                <h2 style="font-size:1.2rem; font-weight:700; color:var(--text); margin-bottom:1rem;">A Game That Never Gets Old</h2>
                <p style="color:var(--text-muted); font-size:0.93rem; line-height:1.8; margin-bottom:1rem;">
                    What makes Snake so enduring is its perfect balance of simplicity and tension. The rules take seconds to learn — move, eat, grow, don't hit anything — but mastery is genuinely hard. As your snake gets longer, the available space shrinks and every turn becomes a calculated risk. The game gets harder the better you do, which is a rare and elegant design quality.
                </p>
                <p style="color:var(--text-muted); font-size:0.93rem; line-height:1.8;">
                    Thousands of Snake clones and remakes have been made across virtually every platform imaginable. Google famously hid a playable version in Google Maps and Search. The game has been rebuilt in every programming language as a learning exercise. Decades on, it remains a benchmark of good game design — proof that you don't need cutting-edge graphics or deep mechanics to create something genuinely compelling.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ===== RECENTLY VIEWED ===== -->
@if(!empty($recentGames))
<section class="section" style="border-top:1px solid var(--border);">
    <div class="container">
        <div class="section-header fade-up">
            <h2 class="section-title">Recently Viewed</h2>
            <a href="{{ route('search') }}" class="section-link">Browse All →</a>
        </div>
        <div class="games-grid games-grid--large fade-up">
            @foreach($recentGames as $game)
            <x-game-card :game="$game" />
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection
