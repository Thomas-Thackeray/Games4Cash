@extends('layouts.app')
@section('title', 'Request a Price Evaluation')

@section('content')
<div class="account-page">
    <div class="account-container" style="max-width:700px; margin:0 auto;">

        <div class="account-main" style="width:100%;">
            <section class="account-card">
                <div class="account-card__header">
                    <h2 class="account-card__title">Request a Price Evaluation</h2>
                    <p class="account-card__subtitle">
                        Submit images and details of your game and we'll give you a price estimate.
                        You can submit up to 20 requests per day.
                    </p>
                </div>

                @if($errors->any())
                <div class="alert alert--error" style="margin-bottom:1.25rem;">
                    <ul style="margin:0; padding-left:1.25rem;">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('evaluations.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="game_title">Game Title <span class="required">*</span></label>
                        <input type="text" id="game_title" name="game_title"
                            value="{{ old('game_title') }}"
                            class="form-input {{ $errors->has('game_title') ? 'is-invalid' : '' }}"
                            placeholder="e.g. The Last of Us Part II">
                        @error('game_title')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group" style="margin-top:1rem;">
                        <label for="platform">Platform <span class="required">*</span></label>
                        <select id="platform" name="platform"
                            class="form-input {{ $errors->has('platform') ? 'is-invalid' : '' }}">
                            <option value="">— Select platform —</option>
                            @foreach($platforms as $id => $name)
                            <option value="{{ $name }}" {{ old('platform') === $name ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                            @endforeach
                            <option value="Other" {{ old('platform') === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('platform')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group" style="margin-top:1rem;">
                        <label for="condition">Condition <span class="required">*</span></label>
                        <select id="condition" name="condition"
                            class="form-input {{ $errors->has('condition') ? 'is-invalid' : '' }}">
                            <option value="">— Select condition —</option>
                            @foreach($conditions as $key => $label)
                            <option value="{{ $key }}" {{ old('condition') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                        @error('condition')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group" style="margin-top:1rem;">
                        <label for="description">Additional Details</label>
                        <textarea id="description" name="description" rows="4"
                            class="form-input {{ $errors->has('description') ? 'is-invalid' : '' }}"
                            placeholder="Any extra info — missing manuals, special edition, damage, etc.">{{ old('description') }}</textarea>
                        @error('description')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group" style="margin-top:1rem;">
                        <label>Photos (up to 5 images, max 5 MB each)</label>
                        <p class="password-hint" style="margin-bottom:0.5rem;">Upload photos of the front, back, disc, and any damage. Accepted formats: JPEG, PNG, GIF, WebP.</p>
                        <input type="file" name="images[]" id="images" multiple accept="image/*"
                            class="form-input {{ $errors->has('images') || $errors->has('images.*') ? 'is-invalid' : '' }}"
                            style="padding:0.5rem;">
                        @error('images')<span class="field-error">{{ $message }}</span>@enderror
                        @error('images.*')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:1.5rem; flex-wrap:wrap; gap:0.75rem;">
                        <a href="{{ route('evaluations.index') }}" class="btn btn--outline">My Submissions</a>
                        <button type="submit" class="btn btn--primary">Submit for Evaluation</button>
                    </div>
                </form>
            </section>
        </div>

    </div>
</div>
@endsection
