@extends('layouts.app')
@section('title', 'Sign In')

@section('content')
<div class="auth-page">
    <div class="auth-card">

        <div class="auth-card__header">
            <div class="auth-logo-icon">◈</div>
            <h1 class="auth-card__title">Welcome Back</h1>
            <p class="auth-card__subtitle">Sign in to your account</p>
        </div>

        @if($errors->has('login'))
        <div class="auth-alert">{{ $errors->first('login') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            {{-- Username --}}
            <div class="form-group">
                <label for="username">Username <span class="required">*</span></label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="{{ old('username') }}"
                    class="form-input {{ $errors->has('username') ? 'is-invalid' : '' }}"
                    placeholder="Your username"
                    autocomplete="username"
                    required>
                @error('username')
                <span class="field-error">{{ $message }}</span>
                @enderror
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
                    placeholder="your@email.com"
                    autocomplete="email"
                    required>
                @error('email')
                <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <div class="password-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                        placeholder="Your password"
                        autocomplete="current-password"
                        required>
                    <button type="button" class="password-toggle" data-target="password" aria-label="Toggle password visibility">
                        <span class="eye-icon">👁</span>
                    </button>
                </div>
                @error('password')
                <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn--primary auth-submit">Sign In</button>
        </form>

        <div class="auth-footer" style="display:flex; flex-direction:column; gap:0.5rem; align-items:center;">
            <a href="{{ route('password.request') }}" style="font-size:0.875rem; color:var(--text-muted);">Forgot your password?</a>
            <span>Don't have an account? <a href="{{ route('register') }}">Create one</a></span>
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
