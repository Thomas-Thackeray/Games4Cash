@extends('layouts.app')
@section('title', 'Two-Factor Verification')

@section('content')
<div class="container" style="max-width:420px; padding:4rem 1rem;">
    <div class="account-card">
        <div class="account-card__header" style="text-align:center; margin-bottom:1.5rem;">
            <div style="font-size:2rem; margin-bottom:0.5rem;">🔐</div>
            <h1 style="font-size:1.3rem; font-weight:700; margin:0;">Two-Factor Verification</h1>
            <p style="color:var(--text-muted); margin-top:0.4rem; font-size:0.9rem;">
                Open your authenticator app and enter the 6-digit code.
            </p>
        </div>

        @if($errors->any())
        <div class="alert alert--error" style="margin-bottom:1.25rem;">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('two-factor.verify') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="code">Authentication Code</label>
                <input type="text"
                       id="code"
                       name="code"
                       class="form-input{{ $errors->has('code') ? ' form-input--error' : '' }}"
                       placeholder="000000"
                       inputmode="numeric"
                       autocomplete="one-time-code"
                       maxlength="6"
                       autofocus
                       style="font-size:1.4rem; letter-spacing:0.25em; text-align:center;">
                @error('code')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="btn btn--primary" style="width:100%; margin-top:0.5rem;">
                Verify
            </button>
        </form>

        <p style="text-align:center; margin-top:1.25rem; font-size:0.85rem; color:var(--text-muted);">
            <a href="{{ route('login') }}" style="color:var(--accent);">Back to login</a>
        </p>
    </div>
</div>
@endsection
