<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Quote Submitted</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f4f4f8; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        .wrapper { width: 100%; background-color: #f4f4f8; padding: 40px 16px; }
        .card { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: #080810; padding: 36px 40px 28px; text-align: center; }
        .logo-icon { font-size: 2rem; color: #e63946; display: block; margin-bottom: 8px; }
        .logo-text { font-size: 1.8rem; font-weight: 800; color: #ffffff; letter-spacing: 0.06em; text-transform: uppercase; }
        .accent-bar { height: 4px; background: linear-gradient(90deg, #e63946, #ff6b6b); }
        .body { padding: 40px; }
        .greeting { font-size: 1.4rem; font-weight: 700; color: #0f0f1a; margin: 0 0 12px; }
        .intro { font-size: 1rem; color: #555577; line-height: 1.7; margin: 0 0 28px; }
        .details-box { background: #f8f8fc; border: 1px solid #e8e8f0; border-radius: 8px; padding: 20px 24px; margin-bottom: 24px; }
        .details-box p { margin: 0 0 8px; font-size: 0.9rem; color: #555577; }
        .details-box p:last-child { margin: 0; }
        .details-box strong { color: #0f0f1a; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 0.88rem; }
        .items-table th { background: #f0f0f6; text-align: left; padding: 8px 12px; color: #555577; font-weight: 600; border-bottom: 2px solid #e8e8f0; }
        .items-table td { padding: 9px 12px; border-bottom: 1px solid #f0f0f6; color: #333344; vertical-align: top; }
        .items-table tr:last-child td { border-bottom: none; }
        .total-row { background: #fff8f8; }
        .total-row td { font-weight: 700; color: #e63946 !important; font-size: 0.95rem; }
        .cta-btn { display: inline-block; background: #e63946; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 50px; font-weight: 700; font-size: 0.95rem; letter-spacing: 0.02em; }
        .cta-wrap { text-align: center; margin-bottom: 8px; }
        .badge { display: inline-block; background: rgba(230,57,70,0.1); color: #e63946; border: 1px solid rgba(230,57,70,0.25); border-radius: 6px; padding: 3px 10px; font-size: 0.78rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 14px; }
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
            <span class="badge">Admin Alert</span>
            <h1 class="greeting">New Quote Submitted</h1>
            <p class="intro">{!! nl2br(e($emailBody)) !!}</p>

            <div class="details-box">
                <p><strong>Order Details</strong></p>
                <p>Reference: <strong>{{ $order->order_ref }}</strong></p>
                <p>Customer: <strong>{{ $user->first_name }} {{ $user->surname }}</strong> ({{ $user->username }})</p>
                <p>Email: <strong>{{ $user->email }}</strong></p>
                <p>Total: <strong>£{{ number_format((float) $order->total_gbp, 2) }}</strong></p>
                <p>Submitted: <strong>{{ now()->format('d M Y, H:i') }}</strong></p>
                <p>Collection address: <strong>{{ implode(', ', array_filter([$order->house_name_number . ' ' . $order->address_line1, $order->address_line2, $order->address_line3, $order->city, $order->county, $order->postcode])) }}</strong></p>
            </div>

            @if(!empty($items))
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Game</th>
                        <th>Platform</th>
                        <th>Condition</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td>{{ $item['game_title'] ?? '—' }}</td>
                        <td>{{ $item['platform_name'] ?? '—' }}</td>
                        <td>{{ $item['condition_label'] ?? '—' }}</td>
                        <td>{{ $item['display_price'] ?? '—' }}</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="3"><strong>Total</strong></td>
                        <td><strong>£{{ number_format((float) $order->total_gbp, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
            @endif

            <div class="cta-wrap">
                <a href="{{ url('/admin/orders') }}" class="cta-btn">View Order in Admin</a>
            </div>
        </div>

        <div class="footer-block">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Admin notification — do not reply.</p>
        </div>

    </div>
</div>
</body>
</html>
