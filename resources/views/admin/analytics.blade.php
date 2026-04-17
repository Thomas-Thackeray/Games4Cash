@extends('layouts.app')
@section('title', 'Analytics')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Analytics</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.dashboard') }}" style="color:var(--accent);">← Dashboard</a></p>
        </div>
    </div>

    @if(!($hasTable ?? true))
    <div class="alert alert--error">Run <code>php artisan migrate</code> on the server to enable analytics tracking.</div>
    @else

    {{-- Summary stat cards --}}
    @php
    $periods = [
        'today'    => 'Today',
        'week'     => 'This Week',
        'month'    => 'This Month',
        'all_time' => 'All Time',
    ];
    @endphp
    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:1rem; margin-bottom:2rem;">
        @foreach($periods as $key => $label)
        <div class="stat-card">
            <div style="font-size:0.72rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--text-muted); margin-bottom:0.75rem;">{{ $label }}</div>
            <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:1rem;">
                <div>
                    <div class="stat-card__value" style="font-size:1.75rem;">{{ number_format($summary[$key]['views']) }}</div>
                    <div class="stat-card__label">Page Views</div>
                </div>
                <div style="text-align:right;">
                    <div class="stat-card__value" style="font-size:1.75rem; color:var(--accent);">{{ number_format($summary[$key]['visitors']) }}</div>
                    <div class="stat-card__label">Unique Visitors</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Daily chart (last 30 days) --}}
    <div class="admin-section" style="margin-bottom:2rem;">
        <h2 class="admin-section__title" style="margin-bottom:1rem;">Last 30 Days</h2>
        <div style="background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius); padding:1.5rem;">
            <div style="display:flex; gap:1.25rem; margin-bottom:1rem; font-size:0.82rem;">
                <span style="display:flex; align-items:center; gap:0.4rem;">
                    <span style="display:inline-block; width:12px; height:12px; border-radius:2px; background:var(--accent); opacity:0.7;"></span>
                    Page Views
                </span>
                <span style="display:flex; align-items:center; gap:0.4rem;">
                    <span style="display:inline-block; width:12px; height:12px; border-radius:2px; background:#38bdf8;"></span>
                    Unique Visitors
                </span>
            </div>
            <div style="overflow-x:auto;">
                <canvas id="analytics-chart" style="width:100%; min-width:600px; height:220px;"></canvas>
            </div>
        </div>
    </div>

    {{-- Top pages + referrers --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:2rem;">

        {{-- Top pages --}}
        <div class="admin-section">
            <h2 class="admin-section__title" style="margin-bottom:0.75rem;">Top Pages <span style="font-size:0.78rem; font-weight:400; color:var(--text-muted);">(last 30 days)</span></h2>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Path</th>
                            <th style="text-align:right; white-space:nowrap;">Views</th>
                            <th style="text-align:right; white-space:nowrap;">Visitors</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topPages as $page)
                        <tr>
                            <td style="font-size:0.82rem; word-break:break-all;">
                                <a href="{{ $page->path }}" target="_blank"
                                   style="color:var(--accent); text-decoration:none;">{{ $page->path }}</a>
                            </td>
                            <td style="text-align:right; font-weight:600;">{{ number_format($page->views) }}</td>
                            <td style="text-align:right; color:var(--text-muted);">{{ number_format($page->visitors) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="admin-td-muted" style="text-align:center; padding:1.5rem;">No data yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top referrers --}}
        <div class="admin-section">
            <h2 class="admin-section__title" style="margin-bottom:0.75rem;">Top Referrers <span style="font-size:0.78rem; font-weight:400; color:var(--text-muted);">(last 30 days)</span></h2>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Source</th>
                            <th style="text-align:right; white-space:nowrap;">Visits</th>
                            <th style="text-align:right; white-space:nowrap;">Visitors</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topReferrers as $ref)
                        <tr>
                            <td style="font-size:0.82rem; word-break:break-all;">
                                <a href="{{ $ref->referrer }}" target="_blank" rel="noopener noreferrer"
                                   style="color:var(--accent); text-decoration:none;">
                                    {{ preg_replace('#^https?://(www\.)?#i', '', $ref->referrer) }}
                                </a>
                            </td>
                            <td style="text-align:right; font-weight:600;">{{ number_format($ref->visits) }}</td>
                            <td style="text-align:right; color:var(--text-muted);">{{ number_format($ref->visitors) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="admin-td-muted" style="text-align:center; padding:1.5rem;">No external referrers yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    @endif
</div>

@if($hasTable ?? true)
<script>
(function () {
    const daily = @json($daily);

    const canvas  = document.getElementById('analytics-chart');
    if (!canvas) return;
    const ctx     = canvas.getContext('2d');
    const dpr     = window.devicePixelRatio || 1;
    const W       = canvas.offsetWidth;
    const H       = 220;
    canvas.width  = W * dpr;
    canvas.height = H * dpr;
    ctx.scale(dpr, dpr);

    const maxVal  = Math.max(...daily.map(d => d.views), 1);
    const padL    = 44, padR = 16, padT = 16, padB = 40;
    const chartW  = W - padL - padR;
    const chartH  = H - padT - padB;
    const barW    = chartW / daily.length;

    // Gridlines
    const steps = 4;
    ctx.strokeStyle = 'rgba(255,255,255,0.06)';
    ctx.lineWidth   = 1;
    for (let i = 0; i <= steps; i++) {
        const y = padT + (chartH / steps) * i;
        ctx.beginPath(); ctx.moveTo(padL, y); ctx.lineTo(padL + chartW, y); ctx.stroke();
        // y-axis labels
        const val = Math.round(maxVal - (maxVal / steps) * i);
        ctx.fillStyle    = 'rgba(255,255,255,0.3)';
        ctx.font         = `11px Outfit, sans-serif`;
        ctx.textAlign    = 'right';
        ctx.fillText(val, padL - 6, y + 4);
    }

    // Bars (views)
    daily.forEach((d, i) => {
        const bh = (d.views / maxVal) * chartH;
        const x  = padL + i * barW;
        const y  = padT + chartH - bh;
        ctx.fillStyle = 'rgba(230,57,70,0.55)';
        ctx.fillRect(x + 2, y, barW - 4, bh);
    });

    // Line (visitors)
    ctx.strokeStyle = '#38bdf8';
    ctx.lineWidth   = 2;
    ctx.lineJoin    = 'round';
    ctx.beginPath();
    daily.forEach((d, i) => {
        const x = padL + i * barW + barW / 2;
        const y = padT + chartH - (d.visitors / maxVal) * chartH;
        i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
    });
    ctx.stroke();

    // X-axis labels (every 5th day)
    ctx.fillStyle = 'rgba(255,255,255,0.3)';
    ctx.font      = '10px Outfit, sans-serif';
    ctx.textAlign = 'center';
    daily.forEach((d, i) => {
        if (i % 5 !== 0 && i !== daily.length - 1) return;
        const x = padL + i * barW + barW / 2;
        ctx.fillText(d.label, x, H - 6);
    });
})();
</script>
@endif
@endsection
