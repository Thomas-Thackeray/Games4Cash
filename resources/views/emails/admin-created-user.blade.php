<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Created</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f4f4f8; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        .wrapper { width: 100%; background-color: #f4f4f8; padding: 40px 16px; }
        .card { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: #080810; padding: 36px 40px 28px; text-align: center; }
        .logo-icon { font-size: 2rem; color: #e63946; display: block; margin-bottom: 8px; }
        .logo-text { font-size: 1.8rem; font-weight: 800; color: #ffffff; letter-spacing: 0.06em; text-transform: uppercase; }
        .accent-bar { height: 4px; background: linear-gradient(90deg, #e63946, #ff6b6b); }
        .body { padding: 40px; }
        .greeting { font-size: 1.5rem; font-weight: 700; color: #0f0f1a; margin: 0 0 12px; }
        .intro { font-size: 1rem; color: #555577; line-height: 1.7; margin: 0 0 28px; }
        .details-box { background: #f8f8fc; border: 1px solid #e8e8f0; border-radius: 8px; padding: 20px 24px; margin-bottom: 28px; }
        .details-box p { margin: 0 0 8px; font-size: 0.9rem; color: #555577; }
        .details-box p:last-child { margin: 0; }
        .details-box strong { color: #0f0f1a; }
        .cta-btn { display: inline-block; background: #e63946; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 50px; font-weight: 700; font-size: 0.95rem; letter-spacing: 0.02em; }
        .cta-wrap { text-align: center; margin-bottom: 32px; }
        .divider { border: none; border-top: 1px solid #e8e8f0; margin: 28px 0; }
        .body-note { font-size: 0.85rem; color: #888899; line-height: 1.6; margin: 0 0 12px; }
        .link-fallback { font-size: 0.8rem; color: #888899; word-break: break-all; }
        .footer-block { background: #f0f0f6; padding: 24px 40px; text-align: center; }
        .footer-block p { margin: 0 0 6px; font-size: 0.8rem; color: #888899; }
        .footer-block a { color: #e63946; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">

        <div class="header">
            <span class="logo-icon">◈</span>
            <span class="logo-text">{{ config('app.name') }}</span>
        </div>
        <div class="accent-bar"></div>

        <div class="body">
            <h1 class="greeting">Welcome, {{ $user->first_name }}!</h1>
            <p class="intro">An account has been created for you on {{ config('app.name') }}. Click the button below to set your password and get started.</p>

            <div class="details-box">
                <p><strong>Your Account Details</strong></p>
                <p>Name: <strong>{{ $user->first_name }} {{ $user->surname }}</strong></p>
                <p>Username: <strong>{{ $user->username }}</strong></p>
                <p>Email: <strong>{{ $user->email }}</strong></p>
            </div>

            <div class="cta-wrap">
                <a href="{{ $setupUrl }}" class="cta-btn">Set Your Password</a>
            </div>

            <hr class="divider">

            <p class="body-note">This link will expire in 60 minutes. If you didn't expect this email, you can safely ignore it — no account will be active until you set a password.</p>
            <p class="body-note">If the button above doesn't work, copy and paste this link into your browser:</p>
            <p class="link-fallback">{{ $setupUrl }}</p>
        </div>

        <div class="footer-block">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>
                <a href="{{ url('/privacy') }}">Privacy Policy</a> &nbsp;&middot;&nbsp;
                <a href="{{ url('/terms') }}">Terms &amp; Conditions</a>
            </p>
        </div>

    </div>
</div>
</body>
</html>
