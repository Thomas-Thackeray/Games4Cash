@extends('layouts.app')
@section('title', 'Forgot Password')

@section('content')
<div class="auth-page">
    <div class="auth-card">

        <div class="auth-card__header">
            <div class="auth-logo-icon">◈</div>
            <h1 class="auth-card__title">Forgot Password</h1>
            <p class="auth-card__subtitle">Enter your email and we'll send you a reset link</p>
        </div>

        @if(session('status'))
        <div class="auth-alert" style="background:rgba(44,182,125,0.12); border-color:rgba(44,182,125,0.3); color:#2cb67d;">
            {{ session('status') }}
        </div>
        @endif

        @if(! session('status'))
        <form method="POST" action="{{ route('password.email') }}" novalidate>
            @csrf
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

            <button type="submit" class="btn btn--primary auth-submit">Send Reset Link</button>
        </form>
        @endif

        <div class="auth-footer">
            <a href="{{ route('login') }}">← Back to Sign In</a>
        </div>

    </div>
</div>
@endsection
