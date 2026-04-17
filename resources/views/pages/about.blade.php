@extends('layouts.app')
@section('title', 'About Us')
@section('meta_description', config('app.name') . ' is a UK game buying service offering instant cash prices for PS5, PS4, Xbox, Switch, and PC games — with free door-to-door collection and fast payment.')
@section('canonical', route('about'))
@section('content')

<div class="container" style="max-width:860px; padding:4rem 1rem 5rem;">

    <h1 style="font-size:2.5rem; margin-bottom:0.5rem;">About Us</h1>
    <p style="color:var(--text-muted); font-size:1.1rem; margin-bottom:2rem;">
        Turning your unwanted games into cash — quickly, fairly, and hassle-free.
    </p>

    {{-- Who we are --}}
    <section style="margin-bottom:3rem;">
        <p style="font-size:1rem; line-height:1.9; color:var(--text-muted); margin-bottom:1rem;">
            {{ config('app.name') }} is a UK-based game buying service built around one idea: getting you a fair price for your physical game collection without the hassle of listings, postage, or waiting for a buyer. We price every game using real market data — combining live retail prices, age of release, platform demand, and game condition — so you always know the offer is grounded in what your games are actually worth.
        </p>
        <p style="font-size:1rem; line-height:1.9; color:var(--text-muted);">
            We cover all major platforms including PlayStation 5, PlayStation 4, Xbox Series X|S, Xbox One, Nintendo Switch, and PC. Whether you have a single title or a collection of hundreds, you can get an instant quote, submit your basket, and arrange a free door-to-door collection — all from your account dashboard. No packing, no post office queues, no surprises.
        </p>
    </section>

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

    {{-- What we accept --}}
    <section style="margin-bottom:3rem;">
        <h2 style="font-size:1.5rem; margin-bottom:1rem;">What We Accept</h2>
        <p style="font-size:0.95rem; line-height:1.8; color:var(--text-muted); margin-bottom:1.25rem;">
            We buy physical game discs and cartridges across all major platforms. Games do not need to be in perfect condition — we accept them in three states:
        </p>
        <div style="display:flex; flex-direction:column; gap:0.75rem; margin-bottom:1.25rem;">
            @foreach([
                ['label'=>'Brand New','desc'=>'Sealed, unopened. Commands the highest cash offer.'],
                ['label'=>'Complete (In Case)','desc'=>'Disc or cartridge with its original case and cover art. Minor wear is fine.'],
                ['label'=>'Disc / Cartridge Only','desc'=>'No case required. We still buy disc-only titles, though the offer will reflect the missing packaging.'],
            ] as $cond)
            <div style="display:flex; gap:1rem; align-items:flex-start; background:var(--bg-2); border:1px solid var(--border); border-radius:var(--radius); padding:1rem 1.25rem;">
                <span style="font-weight:700; color:var(--white); white-space:nowrap; min-width:160px;">{{ $cond['label'] }}</span>
                <span style="color:var(--text-muted); font-size:0.9rem; line-height:1.6;">{{ $cond['desc'] }}</span>
            </div>
            @endforeach
        </div>
        <p style="font-size:0.9rem; color:var(--text-muted); line-height:1.7;">
            We do not currently buy digital codes, DLC vouchers, or accessories. All games are subject to a brief physical inspection upon collection — if a disc is heavily scratched or a cartridge is faulty, we will contact you before adjusting the offer.
        </p>
    </section>

    {{-- Pricing transparency --}}
    <section style="margin-bottom:3rem;">
        <h2 style="font-size:1.5rem; margin-bottom:1rem;">How Our Prices Work</h2>
        <p style="font-size:0.95rem; line-height:1.8; color:var(--text-muted); margin-bottom:1rem;">
            Every cash offer on {{ config('app.name') }} is calculated automatically using a formula that accounts for the current retail price of the game on Steam and other storefronts, a platform-specific demand factor, how old the game is, and its condition. We apply a consistent margin across all titles — no individual haggling, no hidden deductions after collection.
        </p>
        <p style="font-size:0.95rem; line-height:1.8; color:var(--text-muted);">
            Prices are refreshed regularly to reflect the market. The estimate you see when browsing is the same figure we use when your order is confirmed. If we ever need to adjust an offer following physical inspection we will always get in touch first — you are never obligated to accept.
        </p>
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
