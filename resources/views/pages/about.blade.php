@extends('layouts.app')
@section('title', 'About Us')
@section('meta_description', 'Learn about ' . config('app.name') . ' — the easiest way to sell your unwanted games for cash with free door-to-door collection.')
@section('canonical', route('about'))
@section('content')

<div class="container" style="max-width:860px; padding:4rem 1rem 5rem;">

    <h1 style="font-size:2.5rem; margin-bottom:0.5rem;">About Us</h1>
    <p style="color:var(--text-muted); font-size:1.1rem; margin-bottom:3rem;">
        Turning your unwanted games into cash — quickly, fairly, and hassle-free.
    </p>

    {{-- How it works --}}
    <section style="margin-bottom:3rem;">
        <h2 style="font-size:1.5rem; margin-bottom:1.25rem;">How It Works</h2>
        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:1.25rem;">
            @foreach([
                ['icon'=>'🔍','title'=>'1. Browse & Value','body'=>'Search our catalogue and get an instant cash estimate for each game you want to sell. Prices update in real time based on current market values.'],
                ['icon'=>'🛒','title'=>'2. Fill Your Basket','body'=>'Add as many games as you like to your Cash Basket, select the condition of each one, and see your running total before you commit to anything.'],
                ['icon'=>'📦','title'=>'3. Submit a Quote','body'=>'When you\'re happy with the total, submit your quote and enter your collection address. We\'ll review your submission and get in touch to confirm.'],
                ['icon'=>'💰','title'=>'4. Get Paid','body'=>'Once we\'ve collected and verified your games, we\'ll send your payment. No hidden fees, no waiting around.'],
            ] as $step)
            <div style="background:var(--bg-2); border:1px solid var(--border); border-radius:var(--radius-lg); padding:1.5rem;">
                <div style="font-size:2rem; margin-bottom:0.75rem;">{{ $step['icon'] }}</div>
                <h3 style="font-size:1rem; font-weight:700; margin-bottom:0.5rem;">{{ $step['title'] }}</h3>
                <p style="color:var(--text-muted); font-size:0.9rem; line-height:1.7;">{{ $step['body'] }}</p>
            </div>
            @endforeach
        </div>
    </section>

    {{-- Why us --}}
    <section style="margin-bottom:3rem;">
        <h2 style="font-size:1.5rem; margin-bottom:1.25rem;">Why Sell With Us?</h2>
        <div style="display:flex; flex-direction:column; gap:1rem;">
            @foreach([
                ['icon'=>'💷','title'=>'Fair, Transparent Prices','body'=>'Our prices are calculated automatically from real market data, adjusted for the age of the game and current demand — no lowball offers.'],
                ['icon'=>'🚚','title'=>'Free Collection','body'=>'We come to you. No trips to a post office, no packaging hassle. Just box up your games and we\'ll collect from your door at a time that suits you.'],
                ['icon'=>'⚡','title'=>'Fast Payment','body'=>'Once your games are checked in we process payment quickly. You\'ll always know where your order stands through your account dashboard.'],
                ['icon'=>'🔒','title'=>'Safe & Secure','body'=>'Your personal information is handled in accordance with UK GDPR. We never share your data with third parties for marketing purposes.'],
            ] as $point)
            <div style="background:var(--bg-2); border:1px solid var(--border); border-radius:var(--radius); padding:1.25rem 1.5rem; display:flex; gap:1rem; align-items:flex-start;">
                <span style="font-size:1.5rem; flex-shrink:0;">{{ $point['icon'] }}</span>
                <div>
                    <h3 style="font-size:0.95rem; font-weight:700; margin-bottom:0.25rem;">{{ $point['title'] }}</h3>
                    <p style="color:var(--text-muted); font-size:0.875rem; line-height:1.7;">{{ $point['body'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- CTA --}}
    <section style="background:var(--bg-2); border:1px solid var(--border); border-radius:var(--radius-lg); padding:2rem; text-align:center;">
        <h2 style="font-size:1.4rem; margin-bottom:0.75rem;">Ready to turn your games into cash?</h2>
        <p style="color:var(--text-muted); margin-bottom:1.5rem;">Browse our catalogue, value your games instantly, and get a free collection quote today.</p>
        <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
            <a href="{{ route('search') }}" class="btn btn--primary">Browse Games</a>
            <a href="{{ route('faq') }}" class="btn btn--outline">Read the FAQ</a>
        </div>
    </section>

</div>

@endsection
