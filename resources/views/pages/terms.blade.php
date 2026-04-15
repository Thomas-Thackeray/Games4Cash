@extends('layouts.app')
@section('title', 'Terms & Conditions')
@section('canonical', route('terms'))
@section('content')
<div class="container" style="max-width:800px; padding:4rem 1rem 5rem;">
    <h1 style="font-size:2.5rem; margin-bottom:0.5rem;">Terms &amp; Conditions</h1>
    <p style="color:var(--text-muted); margin-bottom:2.5rem; font-size:0.9rem;">Last updated: April 2026</p>

    <p style="color:var(--text-muted); line-height:1.9; margin-bottom:2rem;">
        Please read these Terms &amp; Conditions carefully before using {{ config('app.name') }} or submitting a cash quote.
        By using the site or submitting an order you agree to be bound by these terms.
        These terms are governed by the laws of England and Wales.
    </p>

    @php
    $sections = [
        [
            'title' => '1. About Us',
            'body'  => config('app.name') . ' is a game purchasing service that buys unwanted physical video games from members of the public in the United Kingdom. References to "we", "us", and "our" throughout these terms mean ' . config('app.name') . '. References to "you" and "your" mean the person selling games through this website.',
        ],
        [
            'title' => '2. Eligibility',
            'body'  => 'You must be at least 18 years of age to use this service and sell games through our site. By submitting a quote you confirm that you are 18 or over and that you are the legal owner of the items you are offering for sale, or that you have the full authority of the legal owner to sell them.',
        ],
        [
            'title' => '3. Quoted Prices & Valuations',
            'body'  => 'All prices displayed on this site are estimates only and are calculated automatically from third-party market data adjusted by our internal pricing model. Quoted prices are not guaranteed and are subject to change at any time without notice. A quote submitted through your Cash Basket does not constitute a binding offer or contract. We reserve the right to revise a quoted price — upward or downward — upon physical inspection of the items. We will always contact you with a revised offer before proceeding and you may withdraw your items at no cost if you do not accept the revised price.',
        ],
        [
            'title' => '4. Condition of Items',
            'body'  => 'You are responsible for accurately describing the condition of each item when submitting a quote. The condition options are: Brand New (sealed, unopened), Complete (disc, case, and manual all present), and Just Disc (disc only). If the condition of an item upon receipt differs materially from what was declared, we reserve the right to revise the offer for that item or to return it to you at our cost.',
        ],
        [
            'title' => '5. Order Acceptance',
            'body'  => 'A quote submission is a request for us to purchase your games. No contract of sale is formed until we have (a) received your items, (b) verified their condition, and (c) confirmed the agreed price in writing (including by email). We reserve the right to decline any order or return any item at our discretion, including where an item is not as described, counterfeit, damaged beyond the stated condition, or otherwise not suitable for resale.',
        ],
        [
            'title' => '6. Collection & Postage',
            'body'  => 'We offer a free door-to-door collection service at an agreed time. It is your responsibility to ensure items are appropriately packaged and ready for collection at the scheduled time. If a collection attempt fails due to your unavailability we may need to rearrange, which may affect your quote validity. Risk in the items passes to us upon collection by our courier.',
        ],
        [
            'title' => '7. Payment',
            'body'  => 'Once we have received and verified your items we will process payment for the agreed amount. Payment will be made via bank transfer to the account details you provide. We aim to process payments promptly following verification. We are not liable for delays caused by your bank or payment processor. Payments will not be made in cash.',
        ],
        [
            'title' => '8. Faulty, Counterfeit & Stolen Goods',
            'body'  => 'You warrant that all items sold to us are your lawful property, are not stolen, and are not counterfeit. If we reasonably suspect an item is stolen or counterfeit we are obliged to report the matter to the relevant authorities and the item will not be returned or paid for. Disc-only items with heavy scratching that render the disc unreadable will be treated as faulty and a revised (or nil) offer will be made. We may at our discretion return faulty items to you.',
        ],
        [
            'title' => '9. Cancellation',
            'body'  => 'You may withdraw your quote request at any time before a collection has been arranged by contacting us. Once a collection has been booked, cancellation may result in a small reimbursement fee for the courier cost already incurred. Once items have been collected and verified, the sale is considered complete and cannot be reversed.',
        ],
        [
            'title' => '10. Liability',
            'body'  => 'To the fullest extent permitted by law, ' . config('app.name') . ' shall not be liable for any indirect, incidental, or consequential loss arising from your use of this site or the selling service. Our total liability to you in connection with any transaction shall not exceed the amount paid or offered for the relevant items. Nothing in these terms excludes or limits liability for death or personal injury caused by negligence, fraud, or any other liability that cannot be excluded under English law.',
        ],
        [
            'title' => '11. Intellectual Property',
            'body'  => 'All game data, artwork, and metadata displayed on this site is sourced from licensed third-party providers and remains the property of their respective rights holders. You may not reproduce, scrape, or redistribute any content from this site without our express written permission.',
        ],
        [
            'title' => '12. Changes to These Terms',
            'body'  => 'We may update these Terms &amp; Conditions at any time. Continued use of the site after changes are posted constitutes acceptance of the revised terms. We recommend checking this page periodically.',
        ],
        [
            'title' => '13. Contact',
            'body'  => 'If you have any questions about these terms, please use our <a href="' . route('contact') . '" style="color:var(--accent);">Contact Us</a> page.',
        ],
    ];
    @endphp

    @foreach($sections as $s)
    <h2 style="font-size:1.2rem; font-weight:700; margin-bottom:0.6rem; color:var(--text);">{{ $s['title'] }}</h2>
    <p style="color:var(--text-muted); line-height:1.9; margin-bottom:1.75rem;">{!! $s['body'] !!}</p>
    @endforeach

</div>
@endsection
