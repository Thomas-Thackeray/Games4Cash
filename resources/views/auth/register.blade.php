@extends('layouts.app')
@section('title', 'Create Account')

@section('content')
<div class="auth-page">
    <div class="auth-card">

        <div class="auth-card__header">
            <div class="auth-logo-icon">◈</div>
            <h1 class="auth-card__title">Create Account</h1>
            <p class="auth-card__subtitle">Join to track and discover games</p>
        </div>

        @if($errors->any())
        <div class="auth-alert">
            <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:0.3rem;">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}" novalidate>
            @csrf

            {{-- Name row --}}
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name <span class="required">*</span></label>
                    <input
                        type="text"
                        id="first_name"
                        name="first_name"
                        value="{{ old('first_name') }}"
                        class="form-input {{ $errors->has('first_name') ? 'is-invalid' : '' }}"
                        placeholder="John"
                        autocomplete="given-name"
                        required>
                    @error('first_name')
                    <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="surname">Surname <span class="required">*</span></label>
                    <input
                        type="text"
                        id="surname"
                        name="surname"
                        value="{{ old('surname') }}"
                        class="form-input {{ $errors->has('surname') ? 'is-invalid' : '' }}"
                        placeholder="Smith"
                        autocomplete="family-name"
                        required>
                    @error('surname')
                    <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Email --}}
            <div class="form-group">
                <label for="email">Email Address <span class="required">*</span></label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    placeholder="john@example.com"
                    autocomplete="email"
                    required>
                @error('email')
                <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Contact number --}}
            <div class="form-group">
                <label for="contact_number">Contact Number <span class="required">*</span></label>
                <input
                    type="tel"
                    id="contact_number"
                    name="contact_number"
                    value="{{ old('contact_number') }}"
                    class="form-input {{ $errors->has('contact_number') ? 'is-invalid' : '' }}"
                    placeholder="+44 7700 900000"
                    autocomplete="tel"
                    required>
                @error('contact_number')
                <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Username --}}
            <div class="form-group">
                <label for="username">Username <span class="required">*</span></label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="{{ old('username') }}"
                    class="form-input {{ $errors->has('username') ? 'is-invalid' : '' }}"
                    placeholder="johngamer99"
                    autocomplete="username"
                    required>
                <p class="password-hint">At least 12 characters and must include a number. Letters, numbers, dashes, and underscores only.</p>
                @error('username')
                <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label for="password">Create Password <span class="required">*</span></label>
                <div class="password-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                        placeholder="Min. 12 characters"
                        autocomplete="new-password"
                        required>
                    <button type="button" class="password-toggle" data-target="password" aria-label="Toggle password visibility">
                        <span class="eye-icon">👁</span>
                    </button>
                </div>
                <p class="password-hint">Must be at least 12 characters and include a number and a special character (e.g. !@#$%)</p>
                @error('password')
                <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Confirm password --}}
            <div class="form-group">
                <label for="password_confirmation">Confirm Password <span class="required">*</span></label>
                <div class="password-wrap">
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-input {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                        placeholder="Re-enter your password"
                        autocomplete="new-password"
                        required>
                    <button type="button" class="password-toggle" data-target="password_confirmation" aria-label="Toggle password visibility">
                        <span class="eye-icon">👁</span>
                    </button>
                </div>
                @error('password_confirmation')
                <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn--primary auth-submit">Create Account</button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="{{ route('login') }}">Sign in</a>
        </div>

    </div>
</div>

<script>
document.querySelectorAll('.password-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        input.type = input.type === 'password' ? 'text' : 'password';
    });
});
</script>
@endsection
