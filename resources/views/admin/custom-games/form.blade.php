@extends('layouts.app')
@section('title', $game ? 'Edit: ' . $game->title : 'New Custom Game')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">{{ $game ? 'Edit Game' : 'New Custom Game' }}</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.custom-games.index') }}" style="color:var(--accent);">← Custom Games</a></p>
        </div>
        @if($game)
        <a href="{{ route('game.show', $game->slug) }}" target="_blank" class="btn btn--outline btn--sm">View Page ↗</a>
        @endif
    </div>

    @if($errors->any())
    <div class="alert alert--error" style="margin-bottom:1.5rem;">
        <ul style="margin:0; padding-left:1.25rem;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
        action="{{ $game ? route('admin.custom-games.update', $game->id) : route('admin.custom-games.store') }}"
        enctype="multipart/form-data">
        @csrf
        @if($game) @method('PATCH') @endif

        {{-- Core details --}}
        <div class="settings-card settings-card--wide" style="margin-bottom:1.5rem;">
            <h2 class="settings-card__title">Game Details</h2>

            <div class="form-group" style="margin-top:1.25rem;">
                <label class="form-label">Title <span class="required">*</span></label>
                <input type="text" name="title" value="{{ old('title', $game?->title) }}"
                    class="form-input {{ $errors->has('title') ? 'is-invalid' : '' }}"
                    placeholder="e.g. Retro Racer Deluxe">
                @error('title')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group" style="margin-top:1rem;">
                <label class="form-label">Summary / Description</label>
                <textarea name="summary" rows="5"
                    class="form-input {{ $errors->has('summary') ? 'is-invalid' : '' }}"
                    style="resize:vertical;"
                    placeholder="A short description of the game displayed on its page.">{{ old('summary', $game?->summary) }}</textarea>
                @error('summary')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem; margin-top:1rem; align-items:start;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Developer</label>
                    <input type="text" name="developer" value="{{ old('developer', $game?->developer) }}"
                        class="form-input" placeholder="e.g. Nintendo">
                    @error('developer')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Publisher</label>
                    <input type="text" name="publisher" value="{{ old('publisher', $game?->publisher) }}"
                        class="form-input" placeholder="e.g. Nintendo">
                    @error('publisher')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Release Year</label>
                    <input type="number" name="release_year" value="{{ old('release_year', $game?->release_year) }}"
                        class="form-input" placeholder="{{ date('Y') }}" min="1950" max="{{ date('Y') + 2 }}">
                    @error('release_year')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>

            {{-- Genres --}}
            <div class="form-group" style="margin-top:1rem;">
                <label class="form-label">Genres</label>
                <p class="settings-hint" style="margin-bottom:0.5rem;">Select all that apply.</p>
                <div style="display:flex; flex-wrap:wrap; gap:0.5rem;">
                    @foreach($genres as $genre)
                    @php $checked = in_array($genre, old('genres', $game?->genres ?? [])); @endphp
                    <label style="display:flex; align-items:center; gap:0.35rem; font-size:0.85rem; cursor:pointer; padding:4px 10px; border:1px solid var(--border); border-radius:20px; {{ $checked ? 'border-color:var(--accent); color:var(--accent);' : '' }}">
                        <input type="checkbox" name="genres[]" value="{{ $genre }}"
                            {{ $checked ? 'checked' : '' }}
                            style="accent-color:var(--accent);">
                        {{ $genre }}
                    </label>
                    @endforeach
                </div>
                @error('genres.*')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            {{-- Cover image --}}
            <div class="form-group" style="margin-top:1rem;">
                <label class="form-label">Cover Image</label>
                @if($game?->cover_image_path)
                <div style="margin-bottom:0.75rem;">
                    <img src="{{ Storage::url($game->cover_image_path) }}" alt="Current cover"
                        style="width:80px; height:110px; object-fit:cover; border-radius:6px; border:1px solid var(--border);">
                    <p class="settings-hint" style="margin-top:0.3rem;">Upload a new image to replace the current cover.</p>
                </div>
                @endif
                <input type="file" name="cover_image" accept="image/*"
                    class="form-input {{ $errors->has('cover_image') ? 'is-invalid' : '' }}"
                    style="padding:0.5rem;">
                <p class="settings-hint">Max 5 MB. JPEG, PNG, WebP recommended. Ideal ratio: 3:4 (e.g. 300×400px).</p>
                @error('cover_image')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            {{-- Published toggle --}}
            <div class="form-group" style="margin-top:1rem; display:flex; align-items:center; gap:0.6rem;">
                <input type="checkbox" name="published" id="published" value="1"
                    {{ old('published', $game?->published ?? true) ? 'checked' : '' }}
                    style="accent-color:var(--accent); width:16px; height:16px;">
                <label for="published" class="form-label" style="margin:0; cursor:pointer;">Published (visible to the public)</label>
            </div>
        </div>

        {{-- Platform prices --}}
        <div class="settings-card settings-card--wide" style="margin-bottom:1.5rem;">
            <h2 class="settings-card__title">Cash Trade-in Prices</h2>
            <p class="settings-hint">Set the cash trade-in value per platform. Leave blank to hide that platform.</p>

            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:0.75rem; margin-top:1.25rem;">
                @foreach($platforms as $platformId => $platformName)
                @php $existingPrice = old("platform_prices.{$platformId}", $game?->platform_prices[(string)$platformId] ?? ''); @endphp
                <div class="form-group">
                    <label class="form-label" style="font-size:0.82rem;">{{ $platformName }}</label>
                    <div style="display:flex; align-items:center; gap:0.35rem;">
                        <span style="color:var(--text-muted); font-size:0.9rem;">£</span>
                        <input type="number" name="platform_prices[{{ $platformId }}]"
                            value="{{ $existingPrice !== '' ? number_format((float)$existingPrice, 2, '.', '') : '' }}"
                            class="form-input {{ $errors->has("platform_prices.{$platformId}") ? 'is-invalid' : '' }}"
                            placeholder="—" min="0" max="9999" step="0.01"
                            style="padding:0.45rem 0.65rem;">
                    </div>
                    @error("platform_prices.{$platformId}")<span class="field-error">{{ $message }}</span>@enderror
                </div>
                @endforeach
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
            <a href="{{ route('admin.custom-games.index') }}" class="btn btn--outline">Cancel</a>
            <button type="submit" class="btn btn--primary">{{ $game ? 'Save Changes' : 'Create Game' }}</button>
        </div>

    </form>

</div>
@endsection
