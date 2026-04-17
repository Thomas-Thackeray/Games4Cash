@extends('layouts.app')
@section('title', 'Page Error')
@section('content')
<div class="admin-page">
    <div class="admin-header">
        <div>
            <h1 class="admin-title">Page Error</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.dashboard') }}" style="color:var(--accent);">← Dashboard</a></p>
        </div>
    </div>
    <div class="settings-card settings-card--wide" style="border-left:3px solid #e63946;">
        <h2 class="settings-card__title" style="color:#e63946;">Error in {{ $context }}</h2>
        <p style="font-family:monospace; background:rgba(230,57,70,0.08); padding:1rem; border-radius:6px; word-break:break-all; margin-bottom:0.75rem;">
            {{ $error }}
        </p>
        <p class="admin-td-muted" style="font-size:0.8rem; font-family:monospace;">{{ $file }}</p>
    </div>
</div>
@endsection
