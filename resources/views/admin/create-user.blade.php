@extends('layouts.app')
@section('title', 'Create User')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Create User</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.users') }}" style="color:var(--accent);">← Back to Users</a></p>
        </div>
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

    <div class="settings-card settings-card--wide">
        <h2 class="settings-card__title">New Account Details</h2>

        <form method="POST" action="{{ route('admin.users.store') }}" id="create-user-form">
            @csrf

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-top:1.25rem; align-items:start;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">First Name <span class="required">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}"
                        class="form-input {{ $errors->has('first_name') ? 'is-invalid' : '' }}"
                        placeholder="Jane">
                    @error('first_name')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Surname <span class="required">*</span></label>
                    <input type="text" name="surname" value="{{ old('surname') }}"
                        class="form-input {{ $errors->has('surname') ? 'is-invalid' : '' }}"
                        placeholder="Smith">
                    @error('surname')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="form-group" style="margin-top:1rem;">
                <label class="form-label">Email Address <span class="required">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="form-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    placeholder="jane@example.com">
                @error('email')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group" style="margin-top:1rem;">
                <label class="form-label">Username <span class="required">*</span></label>
                <input type="text" name="username" value="{{ old('username') }}"
                    class="form-input {{ $errors->has('username') ? 'is-invalid' : '' }}"
                    placeholder="Min. 12 characters, must include a number">
                <p class="password-hint">At least 12 characters, letters/numbers/dashes/underscores, must include at least one number.</p>
                @error('username')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group" style="margin-top:1rem;">
                <label class="form-label">Contact Number</label>
                <input type="text" name="contact_number" value="{{ old('contact_number') }}"
                    class="form-input {{ $errors->has('contact_number') ? 'is-invalid' : '' }}"
                    placeholder="Optional">
                @error('contact_number')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="form-group" style="margin-top:1rem;">
                <label class="form-label">Account Role <span class="required">*</span></label>
                <select name="role" class="form-input" id="role-select">
                    <option value="user" {{ old('role', 'user') === 'user' ? 'selected' : '' }}>Standard User</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                </select>
            </div>

            {{-- Admin password confirmation — only shown when role = admin --}}
            <div id="admin-confirm-wrap" style="display:none; margin-top:1rem;">
                <div class="settings-card" style="border-left:3px solid var(--accent); margin:0;">
                    <p style="margin:0 0 0.75rem; font-size:0.9rem; color:var(--text-muted);">
                        Creating an administrator account requires you to confirm your own password.
                    </p>
                    <div class="form-group">
                        <label class="form-label">Your Password <span class="required">*</span></label>
                        <div class="password-wrap">
                            <input type="password" name="admin_password" id="admin_password"
                                class="form-input {{ $errors->has('admin_password') ? 'is-invalid' : '' }}"
                                placeholder="Your current password" autocomplete="current-password">
                            <button type="button" class="password-toggle" data-target="admin_password" aria-label="Toggle">
                                <span class="eye-icon">👁</span>
                            </button>
                        </div>
                        @error('admin_password')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:1.5rem;">
                <a href="{{ route('admin.users') }}" class="btn btn--outline" style="margin-right:0.75rem;">Cancel</a>
                <button type="submit" class="btn btn--primary">Create Account</button>
            </div>
        </form>
    </div>

</div>

<script>
(function () {
    var roleSelect   = document.getElementById('role-select');
    var confirmWrap  = document.getElementById('admin-confirm-wrap');
    var adminPwInput = document.getElementById('admin_password');

    function toggle() {
        var isAdmin = roleSelect.value === 'admin';
        confirmWrap.style.display = isAdmin ? 'block' : 'none';
        adminPwInput.required = isAdmin;
    }

    roleSelect.addEventListener('change', toggle);
    toggle(); // run on page load (handles old() restoration)

    // Password-toggle eye button
    document.querySelectorAll('.password-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.dataset.target);
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    });
})();
</script>
@endsection
