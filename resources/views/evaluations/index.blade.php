@extends('layouts.app')
@section('title', 'My Evaluation Requests')

@section('content')
<div class="account-page">
    <div class="account-container" style="max-width:800px; margin:0 auto;">
        <div class="account-main" style="width:100%;">

            <section class="account-card">
                <div class="account-card__header" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:0.75rem;">
                    <div>
                        <h2 class="account-card__title">My Evaluation Requests</h2>
                        <p class="account-card__subtitle">Track the status of games you've submitted for price evaluation.</p>
                    </div>
                    <a href="{{ route('evaluations.create') }}" class="btn btn--primary btn--sm">+ New Request</a>
                </div>

                @if($evaluations->isEmpty())
                <p style="color:var(--text-muted); font-size:0.88rem; margin-top:1rem;">You haven't submitted any evaluation requests yet.</p>
                @else
                <div style="display:flex; flex-direction:column; gap:0.75rem; margin-top:1rem;">
                    @foreach($evaluations as $ev)
                    <div style="padding:1rem; background:var(--card-bg); border:1px solid var(--border); border-radius:8px;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap;">
                            <div>
                                <div style="font-weight:600; font-size:0.95rem;">{{ $ev->game_title }}</div>
                                <div style="font-size:0.82rem; color:var(--text-muted); margin-top:0.2rem;">
                                    {{ $ev->platform }} &mdash; {{ $ev->condition }}
                                </div>
                                @if($ev->description)
                                <div style="font-size:0.82rem; color:var(--text-dim); margin-top:0.4rem; max-width:500px;">
                                    {{ Str::limit($ev->description, 120) }}
                                </div>
                                @endif
                            </div>
                            <div style="text-align:right; flex-shrink:0;">
                                @php
                                    $badgeStyle = match($ev->status) {
                                        'reviewed' => 'background:rgba(46,213,115,0.15); color:#2ed573;',
                                        'closed'   => 'background:rgba(255,255,255,0.06); color:var(--text-muted);',
                                        default    => 'background:rgba(230,57,70,0.15); color:var(--accent);',
                                    };
                                @endphp
                                <span style="display:inline-block; padding:3px 10px; border-radius:20px; font-size:0.75rem; font-weight:600; {{ $badgeStyle }}">
                                    {{ ucfirst($ev->status) }}
                                </span>
                                <div style="font-size:0.78rem; color:var(--text-dim); margin-top:0.35rem;">
                                    {{ $ev->created_at->format('d M Y') }}
                                </div>
                            </div>
                        </div>

                        @if($ev->admin_notes)
                        <div style="margin-top:0.75rem; padding:0.7rem 0.85rem; background:rgba(255,255,255,0.04); border-left:3px solid var(--accent); border-radius:4px; font-size:0.85rem; color:var(--text-muted);">
                            <strong style="color:var(--text);">Admin note:</strong> {{ $ev->admin_notes }}
                        </div>
                        @endif

                        @if(!empty($ev->image_paths))
                        <div style="margin-top:0.65rem; font-size:0.78rem; color:var(--text-dim);">
                            {{ count($ev->image_paths) }} {{ count($ev->image_paths) === 1 ? 'image' : 'images' }} attached
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>

                @if($evaluations->hasPages())
                <div style="margin-top:1.5rem; display:flex; justify-content:center;">
                    {{ $evaluations->links() }}
                </div>
                @endif
                @endif
            </section>

        </div>
    </div>
</div>
@endsection
