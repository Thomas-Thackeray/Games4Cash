<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Evaluation Request</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f4f4f8; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        .wrapper { width: 100%; background-color: #f4f4f8; padding: 40px 16px; }
        .card { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: #080810; padding: 36px 40px 28px; text-align: center; }
        .logo-icon { font-size: 2rem; color: #e63946; display: block; margin-bottom: 8px; }
        .logo-text { font-size: 1.8rem; font-weight: 800; color: #ffffff; letter-spacing: 0.06em; text-transform: uppercase; }
        .accent-bar { height: 4px; background: linear-gradient(90deg, #e63946, #ff6b6b); }
        .body { padding: 40px; }
        .greeting { font-size: 1.3rem; font-weight: 700; color: #0f0f1a; margin: 0 0 12px; }
        .intro { font-size: 1rem; color: #555577; line-height: 1.7; margin: 0 0 24px; }
        .details-box { background: #f8f8fc; border: 1px solid #e8e8f0; border-radius: 8px; padding: 20px 24px; margin-bottom: 28px; }
        .details-box p { margin: 0 0 8px; font-size: 0.9rem; color: #555577; }
        .details-box p:last-child { margin: 0; }
        .details-box strong { color: #0f0f1a; }
        .desc-box { background: #f8f8fc; border: 1px solid #e8e8f0; border-radius: 8px; padding: 16px 20px; margin-bottom: 28px; font-size: 0.9rem; color: #555577; line-height: 1.6; }
        .cta-btn { display: inline-block; background: #e63946; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 50px; font-weight: 700; font-size: 0.95rem; letter-spacing: 0.02em; }
        .cta-wrap { text-align: center; margin-bottom: 32px; }
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
            <h1 class="greeting">New Evaluation Request</h1>
            <p class="intro">A user has submitted a game for price evaluation. Details are below.</p>

            <div class="details-box">
                <p><strong>Submission Details</strong></p>
                <p>Game: <strong>{{ $evaluation->game_title }}</strong></p>
                <p>Platform: <strong>{{ $evaluation->platform }}</strong></p>
                <p>Condition: <strong>{{ $evaluation->condition }}</strong></p>
                <p>Submitted by: <strong>{{ $evaluation->user->first_name }} {{ $evaluation->user->surname }}</strong> (&#64;{{ $evaluation->user->username }})</p>
                <p>Email: <strong>{{ $evaluation->user->email }}</strong></p>
                <p>Images attached: <strong>{{ count($evaluation->image_paths ?? []) }}</strong></p>
            </div>

            @if($evaluation->description)
            <p style="font-size:0.85rem; color:#555577; margin-bottom:0.5rem;"><strong>User's description:</strong></p>
            <div class="desc-box">{{ $evaluation->description }}</div>
            @endif

            <div class="cta-wrap">
                <a href="{{ $adminUrl }}" class="cta-btn">View in Admin Panel</a>
            </div>
        </div>

        <div class="footer-block">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>

    </div>
</div>
</body>
</html>
