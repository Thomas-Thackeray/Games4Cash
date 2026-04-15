<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed – {{ $order->order_ref }}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f4f4f8; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        .wrapper { width: 100%; background-color: #f4f4f8; padding: 40px 16px; }
        .card { max-width: 580px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: #080810; padding: 36px 40px 28px; text-align: center; }
        .logo-icon { font-size: 2rem; color: #e63946; display: block; margin-bottom: 8px; }
        .logo-text { font-size: 1.8rem; font-weight: 800; color: #ffffff; letter-spacing: 0.06em; text-transform: uppercase; }
        .accent-bar { height: 4px; background: linear-gradient(90deg, #e63946, #ff6b6b); }
        .body { padding: 40px; }
        .greeting { font-size: 1.5rem; font-weight: 700; color: #0f0f1a; margin: 0 0 12px; }
        .intro { font-size: 1rem; color: #555577; line-height: 1.7; margin: 0 0 28px; }
        .ref-badge { display: inline-block; background: #f0f0f8; border: 1px solid #e0e0ee; border-radius: 8px; padding: 10px 20px; font-size: 1.1rem; font-weight: 800; color: #0f0f1a; letter-spacing: 0.06em; margin-bottom: 28px; }
        .section-title { font-size: 0.8rem; font-weight: 700; color: #999; text-transform: uppercase; letter-spacing: 0.1em; margin: 0 0 10px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .items-table th { text-align: left; font-size: 0.78rem; color: #999; text-transform: uppercase; letter-spacing: 0.06em; padding: 0 0 8px; border-bottom: 1px solid #e8e8f0; }
        .items-table td { padding: 10px 0; border-bottom: 1px solid #f0f0f6; font-size: 0.9rem; color: #333355; vertical-align: top; }
        .items-table td.price { text-align: right; font-weight: 600; white-space: nowrap; }
        .items-table tr.total-row td { border-bottom: none; padding-top: 14px; font-weight: 700; font-size: 1rem; color: #0f0f1a; }
        .condition-tag { display: inline-block; font-size: 0.75rem; color: #888899; background: #f4f4f8; border-radius: 4px; padding: 1px 6px; margin-top: 2px; }
        .info-box { background: #f8f8fc; border: 1px solid #e8e8f0; border-radius: 8px; padding: 18px 22px; margin-bottom: 24px; }
        .info-box p { margin: 0 0 6px; font-size: 0.88rem; color: #555577; }
        .info-box p:last-child { margin: 0; }
        .info-box strong { color: #0f0f1a; }
        .cta-btn { display: inline-block; background: #e63946; color: #ffffff; text-decoration: none; padding: 13px 30px; border-radius: 50px; font-weight: 700; font-size: 0.9rem; }
        .cta-wrap { text-align: center; margin: 28px 0; }
        .divider { border: none; border-top: 1px solid #e8e8f0; margin: 28px 0; }
        .body-note { font-size: 0.85rem; color: #888899; line-height: 1.6; margin: 0 0 10px; }
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
            <h1 class="greeting">Thank you, {{ $user->first_name }}!</h1>
            <p class="intro">
                Your cash quote has been received and we're reviewing it now.
                A member of our team will be in touch shortly with further information
                about your collection and payment.
            </p>

            <p class="section-title">Your Order Reference</p>
            <div><span class="ref-badge">{{ $order->order_ref }}</span></div>

            {{-- Items --}}
            <p class="section-title">Items in Your Quote</p>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Game</th>
                        <th style="text-align:right;">Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>
                            {{ $item['game_title'] }}
                            @if(! empty($item['platform_name']))
                                <span style="color:#999; font-size:0.8rem;"> — {{ $item['platform_name'] }}</span>
                            @endif
                            @if(! empty($item['condition_label']))
                                <br><span class="condition-tag">{{ $item['condition_label'] }}</span>
                            @endif
                        </td>
                        <td class="price">
                            {{ $item['display_price'] ?? '—' }}
                        </td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td>Total Estimated Value</td>
                        <td class="price">£{{ number_format($order->total_gbp, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            {{-- Collection address --}}
            <p class="section-title">Collection Address</p>
            <div class="info-box">
                <p>{{ $order->house_name_number }}, {{ $order->address_line1 }}</p>
                @if($order->address_line2)<p>{{ $order->address_line2 }}</p>@endif
                @if($order->address_line3)<p>{{ $order->address_line3 }}</p>@endif
                <p>{{ $order->city }}@if($order->county), {{ $order->county }}@endif</p>
                <p><strong>{{ $order->postcode }}</strong></p>
            </div>

            <div class="cta-wrap">
                <a href="{{ url(route('cash-orders.show', $order->order_ref)) }}" class="cta-btn">View Your Order</a>
            </div>

            <hr class="divider">

            <p class="body-note">
                Please ensure your games are ready and packaged securely before the collection date.
                All prices are estimates and may be adjusted upon physical inspection.
            </p>
            <p class="body-note">
                Questions? <a href="{{ url('/contact') }}" style="color:#e63946;">Contact our support team</a> quoting your order reference <strong>{{ $order->order_ref }}</strong>.
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
