@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
<div class="account-page">
    <div class="account-container">

        {{-- Sidebar --}}
        <aside class="account-sidebar">
            <div class="account-avatar">{{ strtoupper(substr($user->username, 0, 1)) }}</div>
            <div class="account-sidebar__name">{{ $user->first_name }} {{ $user->surname }}</div>
            <div class="account-sidebar__username">&#64;{{ $user->username }}</div>
            <nav class="account-nav">
                <a href="{{ route('profile') }}" class="account-nav__link active">👤 Profile</a>
                <a href="{{ route('security') }}" class="account-nav__link">🔒 Security</a>
            </nav>
        </aside>

        {{-- Main content --}}
        <div class="account-main">

            {{-- Account Details --}}
            <section class="account-card">
                <div class="account-card__header">
                    <h2 class="account-card__title">Account Details</h2>
                    <p class="account-card__subtitle">Update your personal information.</p>
                </div>

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name <span class="required">*</span></label>
                            <input type="text" id="first_name" name="first_name"
                                value="{{ old('first_name', $user->first_name) }}"
                                class="form-input {{ $errors->has('first_name') ? 'is-invalid' : '' }}"
                                placeholder="John" autocomplete="given-name">
                            @error('first_name')<span class="field-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="surname">Surname <span class="required">*</span></label>
                            <input type="text" id="surname" name="surname"
                                value="{{ old('surname', $user->surname) }}"
                                class="form-input {{ $errors->has('surname') ? 'is-invalid' : '' }}"
                                placeholder="Smith" autocomplete="family-name">
                            @error('surname')<span class="field-error">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email"
                            value="{{ old('email', $user->email) }}"
                            class="form-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                            placeholder="john@example.com" autocomplete="email">
                        @error('email')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="contact_number">Contact Number <span class="required">*</span></label>
                        <input type="tel" id="contact_number" name="contact_number"
                            value="{{ old('contact_number', $user->contact_number) }}"
                            class="form-input {{ $errors->has('contact_number') ? 'is-invalid' : '' }}"
                            placeholder="+44 7700 900000" autocomplete="tel">
                        @error('contact_number')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="username">Username <span class="required">*</span></label>
                        <input type="text" id="username" name="username"
                            value="{{ old('username', $user->username) }}"
                            class="form-input {{ $errors->has('username') ? 'is-invalid' : '' }}"
                            placeholder="johngamer99" autocomplete="username">
                        <p class="password-hint">At least 12 characters and must include a number.</p>
                        @error('username')<span class="field-error">{{ $message }}</span>@enderror
                    </div>

                    {{-- Password confirmation — shown when email or username changes --}}
                    <div id="password-confirm-wrap" style="display:none;">
                        <div class="password-confirm-notice">
                            You are changing your email or username. Please enter your current password to confirm.
                        </div>
                        <div class="form-group" style="margin-top:1rem;">
                            <label for="current_password">Current Password <span class="required">*</span></label>
                            <div class="password-wrap">
                                <input type="password" id="current_password" name="current_password"
                                    class="form-input {{ $errors->has('current_password') ? 'is-invalid' : '' }}"
                                    placeholder="Enter your current password"
                                    autocomplete="current-password">
                                <button type="button" class="password-toggle" data-target="current_password" aria-label="Toggle">
                                    <span class="eye-icon">👁</span>
                                </button>
                            </div>
                            @error('current_password')<span class="field-error">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div style="display:flex; justify-content:flex-end;">
                        <button type="submit" class="btn btn--primary">Save Changes</button>
                    </div>
                </form>
            </section>

            {{-- Two-Factor Authentication --}}
            <section class="account-card" id="2fa">
                <div class="account-card__header">
                    <h2 class="account-card__title">Two-Factor Authentication</h2>
                    <p class="account-card__subtitle">Add an extra layer of security by requiring a code from your authenticator app each time you log in.</p>
                </div>

                @if($user->hasTwoFactorEnabled())
                <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:1.25rem;">
                    <span style="display:inline-flex; align-items:center; gap:0.35rem; background:rgba(52,211,153,0.12); color:#34d399; font-size:0.82rem; font-weight:600; padding:0.3rem 0.7rem; border-radius:20px;">
                        ✓ Enabled
                    </span>
                    <span style="font-size:0.85rem; color:var(--text-muted);">Active since {{ $user->two_factor_confirmed_at->format('d M Y') }}</span>
                </div>

                <div id="2fa-disable-wrap">
                    <button type="button" class="btn btn--outline btn--sm" id="2fa-disable-toggle">Disable 2FA</button>
                    <form method="POST" action="{{ route('two-factor.disable') }}" id="2fa-disable-form" style="display:none; margin-top:1rem;">
                        @csrf
                        <div class="form-group">
                            <label class="form-label" for="disable_password">Confirm your password to disable</label>
                            <input type="password" id="disable_password" name="password"
                                class="form-input{{ $errors->has('disable_password') ? ' form-input--error' : '' }}"
                                placeholder="Your current password"
                                autocomplete="current-password"
                                style="max-width:320px;">
                            @error('disable_password')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div style="display:flex; gap:0.75rem; margin-top:0.75rem;">
                            <button type="submit" class="btn btn--danger btn--sm">Disable 2FA</button>
                            <button type="button" class="btn btn--outline btn--sm" id="2fa-disable-cancel">Cancel</button>
                        </div>
                    </form>
                </div>
                @else
                <p style="font-size:0.88rem; color:var(--text-muted); margin-bottom:1.25rem;">
                    2FA is currently <strong style="color:var(--text);">disabled</strong>. We recommend enabling it to protect your account.
                </p>
                <a href="{{ route('two-factor.setup') }}" class="btn btn--primary btn--sm">Enable 2FA</a>
                @endif
            </section>

            {{-- Data Export --}}
            <section class="account-card">
                <div class="account-card__header">
                    <h2 class="account-card__title">Your Data</h2>
                    <p class="account-card__subtitle">Download a copy of everything we hold about you — account details, wishlist, cash basket and order history.</p>
                </div>
                <a href="{{ route('profile.export') }}" class="btn btn--outline">Download My Data (JSON)</a>
            </section>

            {{-- Danger Zone --}}
            <section class="account-card account-card--danger" id="danger-zone">
                <div class="account-card__header">
                    <h2 class="account-card__title account-card__title--danger">Danger Zone</h2>
                    <p class="account-card__subtitle">Permanently delete your account and all associated data. This cannot be undone.</p>
                </div>

                <button type="button" class="btn btn--danger" id="delete-account-toggle">Delete My Account</button>

                <div class="delete-confirm-form" id="delete-confirm-form" style="display:none;">
                    <div class="delete-confirm-notice">
                        To confirm, please enter your password below.
                    </div>
                    <form method="POST" action="{{ route('profile.destroy') }}">
                        @csrf
                        @method('DELETE')
                        <div class="form-group" style="margin-top:1rem;">
                            <label for="confirm_password">Your Password</label>
                            <input type="password" id="confirm_password" name="confirm_password"
                                class="form-input {{ $errors->has('confirm_password') ? 'is-invalid' : '' }}"
                                placeholder="Enter your password to confirm">
                            @error('confirm_password')<span class="field-error">{{ $message }}</span>@enderror
                        </div>
                        <div style="display:flex; gap:0.75rem; margin-top:0.5rem;">
                            <button type="submit" class="btn btn--danger">Yes, Delete My Account</button>
                            <button type="button" class="btn btn--outline" id="delete-account-cancel">Cancel</button>
                        </div>
                    </form>
                </div>
            </section>

        </div>
    </div>
</div>

<script>
// Show password confirmation when email or username is changed
(function () {
    const emailInput    = document.getElementById('email');
    const usernameInput = document.getElementById('username');
    const confirmWrap   = document.getElementById('password-confirm-wrap');
    const originalEmail    = emailInput ? emailInput.defaultValue : '';
    const originalUsername = usernameInput ? usernameInput.defaultValue : '';

    function checkSensitiveChange() {
        const changed = (emailInput && emailInput.value !== originalEmail)
                     || (usernameInput && usernameInput.value !== originalUsername);
        confirmWrap.style.display = changed ? 'block' : 'none';
    }

    if (emailInput)    emailInput.addEventListener('input', checkSensitiveChange);
    if (usernameInput) usernameInput.addEventListener('input', checkSensitiveChange);

    // If server returned a current_password error, keep the field visible
    @if($errors->has('current_password') || old('email', $user->email) !== $user->email || old('username', $user->username) !== $user->username)
    if (confirmWrap) confirmWrap.style.display = 'block';
    @endif
})();

// Password show/hide toggles
document.querySelectorAll('.password-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        if (input) input.type = input.type === 'password' ? 'text' : 'password';
    });
});

const toggle  = document.getElementById('delete-account-toggle');
const form    = document.getElementById('delete-confirm-form');
const cancel  = document.getElementById('delete-account-cancel');
if (toggle && form) {
    toggle.addEventListener('click', () => { form.style.display = 'block'; toggle.style.display = 'none'; });
    cancel.addEventListener('click', () => { form.style.display = 'none'; toggle.style.display = 'inline-flex'; });
}

// 2FA disable toggle
const disableToggle = document.getElementById('2fa-disable-toggle');
const disableForm   = document.getElementById('2fa-disable-form');
const disableCancel = document.getElementById('2fa-disable-cancel');
if (disableToggle && disableForm) {
    disableToggle.addEventListener('click', () => { disableForm.style.display = 'block'; disableToggle.style.display = 'none'; });
    if (disableCancel) disableCancel.addEventListener('click', () => { disableForm.style.display = 'none'; disableToggle.style.display = 'inline-flex'; });
}

@if($errors->has('disable_password'))
if (disableForm && disableToggle) { disableForm.style.display = 'block'; disableToggle.style.display = 'none'; }
@endif

// Auto-reveal delete form if there are confirm_password errors
@if($errors->has('confirm_password'))
if (form && toggle) { form.style.display = 'block'; toggle.style.display = 'none'; }
@endif
</script>
@endsection
