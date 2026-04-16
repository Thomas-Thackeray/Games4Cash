<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password – {{ config('app.name') }}</title>
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
        .cta-btn { display: inline-block; background: #e63946; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 50px; font-weight: 700; font-size: 0.95rem; letter-spacing: 0.02em; }
        .cta-wrap { text-align: center; margin-bottom: 32px; }
        .url-box { background: #f8f8fc; border: 1px solid #e8e8f0; border-radius: 8px; padding: 14px 18px; margin-bottom: 28px; word-break: break-all; font-size: 0.8rem; color: #555577; }
        .divider { border: none; border-top: 1px solid #e8e8f0; margin: 28px 0; }
        .body-note { font-size: 0.85rem; color: #888899; line-height: 1.6; margin: 0 0 12px; }
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
            <h1 class="greeting">Reset your password</h1>
            <p class="intro">{!! nl2br(e($emailIntro)) !!}</p>

            @php
                $resetUrl = url(route('password.reset', [
                    'token' => $token,
                    'email' => $user->email,
                ], false));
            @endphp

            <div class="cta-wrap">
                <a href="{{ $resetUrl }}" class="cta-btn">Reset My Password</a>
            </div>

            <p style="font-size:0.85rem; color:#888899; text-align:center; margin-bottom:20px;">
                If the button doesn't work, copy and paste this link into your browser:
            </p>
            <div class="url-box">{{ $resetUrl }}</div>

            <hr class="divider">

            <p class="body-note">{!! nl2br(e($emailFooterNote)) !!}</p>
            <p class="body-note">
                For security, this link can only be used once and expires after 60 minutes.
            </p>
            <p class="body-note">
                Need help? <a href="{{ url('/contact') }}" style="color:#e63946;">Contact our support team</a>.
            </p>
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
