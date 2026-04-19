@extends('layouts.app')
@section('title', 'Set Up Two-Factor Authentication')

@section('content')
<div class="account-page">
    <div class="account-container">

        <aside class="account-sidebar">
            <div class="account-avatar">{{ strtoupper(substr(auth()->user()->username, 0, 1)) }}</div>
            <div class="account-sidebar__name">{{ auth()->user()->first_name }} {{ auth()->user()->surname }}</div>
            <div class="account-sidebar__username">&#64;{{ auth()->user()->username }}</div>
            <nav class="account-nav">
                <a href="{{ route('profile') }}" class="account-nav__link active">👤 Profile</a>
                <a href="{{ route('security') }}" class="account-nav__link">🔒 Security</a>
            </nav>
        </aside>

        <div class="account-main">
            <section class="account-card">
                <div class="account-card__header">
                    <h2 class="account-card__title">Set Up Two-Factor Authentication</h2>
                    <p class="account-card__subtitle">Scan the QR code with Google Authenticator (or any TOTP app), then enter the 6-digit code to confirm.</p>
                </div>

                {{-- QR Code --}}
                <div style="display:flex; flex-direction:column; align-items:center; gap:1.25rem; margin:1.5rem 0;">
                    <div style="background:#fff; padding:12px; border-radius:8px; border:1px solid var(--border); display:inline-block;">
                        {!! $qrSvg !!}
                    </div>
                    <p style="font-size:0.85rem; color:var(--text-muted); text-align:center;">
                        Can't scan? Enter this key manually:
                    </p>
                    <code style="font-size:1rem; letter-spacing:0.15em; background:var(--card-bg); padding:0.5rem 1rem; border-radius:6px; border:1px solid var(--border); word-break:break-all; text-align:center;">
                        {{ chunk_split($secret, 4, ' ') }}
                    </code>
                </div>

                {{-- Confirm code --}}
                @if($errors->any())
                <div class="alert alert--error" style="margin-bottom:1rem;">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('two-factor.enable') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="code">Verification Code <span style="color:var(--accent);">*</span></label>
                        <input type="text"
                               id="code"
                               name="code"
                               class="form-input{{ $errors->has('code') ? ' form-input--error' : '' }}"
                               placeholder="000000"
                               inputmode="numeric"
                               autocomplete="one-time-code"
                               maxlength="6"
                               autofocus
                               style="font-size:1.25rem; letter-spacing:0.2em; max-width:180px;">
                        @error('code')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div style="display:flex; gap:0.75rem; margin-top:1rem;">
                        <button type="submit" class="btn btn--primary">Enable 2FA</button>
                        <a href="{{ route('profile') }}" class="btn btn--outline">Cancel</a>
                    </div>
                </form>
            </section>
        </div>
    </div>
</div>
@endsection
