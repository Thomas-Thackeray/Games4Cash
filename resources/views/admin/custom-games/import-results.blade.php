@extends('layouts.app')
@section('title', 'Import Results')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Import Results</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.custom-games.index') }}" style="color:var(--accent);">← Custom Games</a></p>
        </div>
    </div>

    <div class="settings-card settings-card--wide" style="margin-bottom:1.5rem;">
        <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1.25rem;">
            <div style="width:48px; height:48px; border-radius:50%; background:rgba(39,174,96,0.12); display:flex; align-items:center; justify-content:center; font-size:1.4rem; flex-shrink:0;">✓</div>
            <div>
                <h2 style="font-size:1.2rem; font-weight:700; margin:0;">Import Complete</h2>
                <p style="color:var(--text-muted); font-size:0.9rem; margin:0.2rem 0 0;">
                    <strong style="color:var(--text);">{{ $created }}</strong> {{ Str::plural('game', $created) }} created successfully.
                    @if(count($skipped) > 0)
                    <span style="color:var(--accent);"> {{ count($skipped) }} {{ Str::plural('row', count($skipped)) }} skipped.</span>
                    @endif
                </p>
            </div>
        </div>

        @if(count($skipped) > 0)
        <div style="background:rgba(230,57,70,0.05); border:1px solid rgba(230,57,70,0.2); border-radius:8px; padding:1rem; margin-bottom:1.25rem;">
            <p style="font-size:0.85rem; font-weight:600; margin:0 0 0.5rem; color:var(--accent);">Skipped Rows</p>
            <ul style="margin:0; padding-left:1.25rem; font-size:0.83rem; color:var(--text-muted); display:flex; flex-direction:column; gap:0.25rem;">
                @foreach($skipped as $msg)
                <li>{{ $msg }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <p style="font-size:0.85rem; color:var(--text-muted); margin:0;">
            Each game has been created with a placeholder cover image. Visit the game list to upload covers individually.
        </p>
    </div>

    <div style="display:flex; gap:0.75rem;">
        <a href="{{ route('admin.custom-games.index') }}" class="btn btn--primary">View All Custom Games</a>
        <a href="{{ route('admin.custom-games.import') }}" class="btn btn--outline">Import Another File</a>
    </div>

</div>
@endsection
