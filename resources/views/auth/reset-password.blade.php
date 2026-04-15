@extends('layouts.app')
@section('title', 'Reset Password')

@section('content')
<div class="auth-page">
    <div class="auth-card">

        <div class="auth-card__header">
            <div class="auth-logo-icon">◈</div>
            <h1 class="auth-card__title">New Password</h1>
            <p class="auth-card__subtitle">Choose a strong password for your account</p>
        </div>

        @if($errors->has('email'))
        <div class="auth-alert">{{ $errors->first('email') }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="form-group">
                <label for="password">New Password <span class="required">*</span></label>
                <div class="password-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                        placeholder="Min. 12 characters"
                        autocomplete="new-password"
                        required>
                    <button type="button" class="password-toggle" data-target="password" aria-label="Toggle visibility">
                        <span class="eye-icon">👁</span>
                    </button>
                </div>
                @error('password')
                <span class="field-error">{{ $message }}</span>
                @enderror
                <p style="font-size:0.78rem; color:var(--text-muted); margin-top:0.4rem;">
                    At least 12 characters, including a number and a symbol.
                </p>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password <span class="required">*</span></label>
                <div class="password-wrap">
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-input"
                        placeholder="Repeat your new password"
                        autocomplete="new-password"
                        required>
                    <button type="button" class="password-toggle" data-target="password_confirmation" aria-label="Toggle visibility">
                        <span class="eye-icon">👁</span>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn--primary auth-submit">Reset Password</button>
        </form>

        <div class="auth-footer">
            <a href="{{ route('login') }}">← Back to Sign In</a>
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
