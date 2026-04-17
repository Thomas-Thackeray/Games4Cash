@php
$sourceStyles = [
    'CeX'        => ['bg' => 'rgba(34,197,94,0.15)',   'color' => '#16a34a', 'border' => 'rgba(34,197,94,0.3)'  ],
    'CheapShark' => ['bg' => 'rgba(59,130,246,0.15)',  'color' => '#2563eb', 'border' => 'rgba(59,130,246,0.3)' ],
    'Steam'      => ['bg' => 'rgba(249,115,22,0.15)',  'color' => '#ea580c', 'border' => 'rgba(249,115,22,0.3)' ],
    'Override'   => ['bg' => 'rgba(168,85,247,0.15)',  'color' => '#9333ea', 'border' => 'rgba(168,85,247,0.3)' ],
    'Base Price' => ['bg' => 'rgba(100,116,139,0.15)', 'color' => '#64748b', 'border' => 'rgba(100,116,139,0.3)'],
];
$s = $sourceStyles[$source ?? ''] ?? null;
@endphp
@if($s)
<span style="display:inline-block; padding:0.2rem 0.55rem; border-radius:999px; font-size:0.78rem; font-weight:600; background:{{ $s['bg'] }}; color:{{ $s['color'] }}; border:1px solid {{ $s['border'] }};">{{ $source }}</span>
@else
<span style="color:var(--text-muted);">—</span>
@endif
