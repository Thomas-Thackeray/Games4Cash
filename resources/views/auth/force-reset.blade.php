@extends('layouts.app')
@section('title', 'Password Reset Required')

@section('content')
<div class="auth-page">
    <div class="auth-card">

        <div class="auth-card__header">
            <div class="auth-logo-icon">🔑</div>
            <h1 class="auth-card__title">New Password Required</h1>
            <p class="auth-card__subtitle">An administrator has requested that you set a new password before continuing.</p>
        </div>

        <div class="auth-alert" style="background:rgba(249,168,38,0.08); border-color:rgba(249,168,38,0.3); color:var(--rating-mid); margin-bottom:1.5rem;">
            You must choose a new password before you can access your account.
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

        <form method="POST" action="{{ route('password.force-reset.update') }}" novalidate>
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="password">New Password <span class="required">*</span></label>
                <div class="password-wrap">
                    <input type="password" id="password" name="password"
                        class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                        placeholder="Min. 12 characters"
                        autocomplete="new-password" required>
                    <button type="button" class="password-toggle" data-target="password" aria-label="Toggle">
                        <span class="eye-icon">👁</span>
                    </button>
                </div>
                <p class="password-hint">At least 12 characters including a number and a special character (e.g. !@#$%).</p>
                @error('password')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm New Password <span class="required">*</span></label>
                <div class="password-wrap">
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="form-input"
                        placeholder="Re-enter your new password"
                        autocomplete="new-password" required>
                    <button type="button" class="password-toggle" data-target="password_confirmation" aria-label="Toggle">
                        <span class="eye-icon">👁</span>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn--primary auth-submit">Set New Password</button>
        </form>

        <div class="auth-footer">
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" style="background:none; border:none; color:var(--accent); font-weight:600; cursor:pointer; font-size:0.9rem; font-family:inherit;">
                    Log out instead
                </button>
            </form>
        </div>

    </div>
</div>

<script>
document.querySelectorAll('.password-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        if (input) input.type = input.type === 'password' ? 'text' : 'password';
    });
});
</script>
@endsection
