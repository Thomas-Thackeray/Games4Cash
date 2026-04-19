@extends('layouts.app')
@section('title', 'Custom Games')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Custom Games</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.dashboard') }}" style="color:var(--accent);">← Dashboard</a></p>
        </div>
        <a href="{{ route('admin.custom-games.create') }}" class="btn btn--primary btn--sm">+ New Game</a>
    </div>

    @if($games->isEmpty())
    <p style="color:var(--text-muted);">No custom games yet. <a href="{{ route('admin.custom-games.create') }}" style="color:var(--accent);">Create one.</a></p>
    @else
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Developer</th>
                    <th>Year</th>
                    <th>Platforms with Prices</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($games as $game)
                <tr>
                    <td><strong>{{ $game->title }}</strong></td>
                    <td class="admin-td-muted">{{ $game->developer ?? '—' }}</td>
                    <td class="admin-td-muted">{{ $game->release_year ?? '—' }}</td>
                    <td class="admin-td-muted">{{ count($game->platform_prices ?? []) }}</td>
                    <td>
                        @if($game->published)
                        <span class="admin-badge admin-badge--ok">Published</span>
                        @else
                        <span class="admin-badge">Draft</span>
                        @endif
                    </td>
                    <td>
                        <div class="admin-row-actions">
                            <a href="{{ route('game.show', $game->slug) }}" target="_blank" class="btn btn--outline btn--xs">View</a>
                            <a href="{{ route('admin.custom-games.edit', $game->id) }}" class="btn btn--outline btn--xs">Edit</a>
                            <form method="POST" action="{{ route('admin.custom-games.destroy', $game->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn--danger btn--xs"
                                    data-confirm="Delete '{{ $game->title }}'?">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($games->hasPages())
    <div style="margin-top:1.5rem; display:flex; justify-content:center;">
        {{ $games->links() }}
    </div>
    @endif
    @endif

</div>
@endsection
