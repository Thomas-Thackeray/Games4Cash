@extends('layouts.app')
@section('title', 'Privacy Policy')
@section('canonical', route('privacy'))
@section('content')
<div class="container" style="max-width:800px; padding:4rem 1rem 5rem;">
    <h1 style="font-size:2.5rem; margin-bottom:0.5rem;">Privacy Policy</h1>
    <p style="color:var(--text-muted); margin-bottom:2.5rem; font-size:0.9rem;">Last updated: April 2026</p>

    <p style="color:var(--text-muted); line-height:1.9; margin-bottom:2rem;">
        {{ config('app.name') }} is committed to protecting your personal information and handling it responsibly.
        This policy explains what data we collect, why we collect it, how it is used, and your rights under
        UK data protection law (UK GDPR and the Data Protection Act 2018).
    </p>

    @php
    $sections = [
        [
            'title' => '1. Who We Are',
            'body'  => config('app.name') . ' is the data controller for personal information collected through this website. If you have any questions or concerns about how we handle your data, please contact us via our <a href="' . route('contact') . '" style="color:var(--accent);">Contact Us</a> page.',
        ],
        [
            'title' => '2. Information We Collect',
            'items' => [
                '<strong>Account information</strong> — when you register, we collect your name, email address, username, and a hashed password.',
                '<strong>Order information</strong> — when you submit a cash quote we collect the collection address you provide, the items in your basket, and the declared conditions of those items.',
                '<strong>Contact messages</strong> — if you use the Contact Us form we collect your name, email address, and the content of your message.',
                '<strong>Usage data</strong> — we log searches, page views, and login activity for security and service improvement purposes.',
                '<strong>Technical data</strong> — IP address, browser type, and session identifiers collected automatically when you visit the site.',
            ],
        ],
        [
            'title' => '3. How We Use Your Information',
            'items' => [
                'To create and manage your account.',
                'To process cash quote submissions and arrange collections.',
                'To respond to contact form enquiries.',
                'To detect and prevent fraud, abuse, or security incidents.',
                'To improve the site and our service based on aggregated, anonymised usage data.',
                'To comply with our legal obligations.',
            ],
        ],
        [
            'title' => '4. Legal Basis for Processing',
            'body'  => 'We process your personal data on the following legal bases under UK GDPR: (a) <strong>Contract</strong> — processing is necessary to perform a contract with you (e.g. fulfilling a cash quote submission); (b) <strong>Legitimate interests</strong> — processing is necessary for our legitimate business interests, such as preventing fraud and improving our service, where these are not overridden by your rights; (c) <strong>Legal obligation</strong> — where we are required to process data to comply with the law; (d) <strong>Consent</strong> — where you have given clear consent (e.g. optional cookies).',
        ],
        [
            'title' => '5. Cookies',
            'body'  => 'We use strictly necessary session cookies to keep you logged in and to protect against cross-site request forgery. We also store a consent preference in your browser\'s local storage to remember that you have acknowledged this notice. We do not use advertising, tracking, or analytics cookies from third parties. You can disable cookies in your browser settings, but doing so may affect site functionality.',
        ],
        [
            'title' => '6. Data Sharing',
            'body'  => 'We do not sell, rent, or trade your personal data. We may share your information with: (a) courier or collection partners, solely for the purpose of arranging a collection at the address you provide; (b) law enforcement or regulatory bodies where required by law. All third parties who process data on our behalf are required to handle it in accordance with UK GDPR.',
        ],
        [
            'title' => '7. Data Retention',
            'body'  => 'We retain your account data for as long as your account is active. Order records are retained for a minimum of six years to comply with our legal and financial obligations. Contact form submissions are retained for up to 12 months. You may request deletion of your account at any time (see Your Rights below), subject to our retention obligations.',
        ],
        [
            'title' => '8. Security',
            'body'  => 'We implement appropriate technical and organisational measures to protect your personal data, including encrypted storage of passwords, HTTPS encryption in transit, and HTTP security headers. No method of transmission or storage is 100% secure; if you have concerns about the security of your account please contact us immediately.',
        ],
        [
            'title' => '9. Your Rights',
            'items' => [
                '<strong>Right of access</strong> — you can request a copy of the personal data we hold about you.',
                '<strong>Right to rectification</strong> — you can ask us to correct inaccurate or incomplete data.',
                '<strong>Right to erasure</strong> — you can ask us to delete your personal data, subject to legal retention requirements.',
                '<strong>Right to restriction</strong> — you can ask us to restrict processing of your data in certain circumstances.',
                '<strong>Right to data portability</strong> — you can ask for your data in a machine-readable format where processing is based on consent or contract.',
                '<strong>Right to object</strong> — you can object to processing based on legitimate interests.',
                'To exercise any of these rights, please contact us via our Contact Us page. We will respond within one calendar month.',
            ],
        ],
        [
            'title' => '10. Children',
            'body'  => 'This service is not directed at children under the age of 18. We do not knowingly collect personal data from anyone under 18. If you believe we have inadvertently collected such data, please contact us and we will delete it promptly.',
        ],
        [
            'title' => '11. Changes to This Policy',
            'body'  => 'We may update this Privacy Policy from time to time. The "last updated" date at the top of this page will reflect any changes. We encourage you to review this policy periodically.',
        ],
        [
            'title' => '12. How to Complain',
            'body'  => 'If you are unhappy with how we have handled your personal data, you have the right to lodge a complaint with the Information Commissioner\'s Office (ICO) at <strong>ico.org.uk</strong> or by calling 0303 123 1113.',
        ],
    ];
    @endphp

    @foreach($sections as $s)
    <h2 style="font-size:1.2rem; font-weight:700; margin-bottom:0.6rem; color:var(--text);">{{ $s['title'] }}</h2>
    @if(isset($s['body']))
    <p style="color:var(--text-muted); line-height:1.9; margin-bottom:1.75rem;">{!! $s['body'] !!}</p>
    @else
    <ul style="color:var(--text-muted); line-height:1.9; margin-bottom:1.75rem; padding-left:1.5rem; display:flex; flex-direction:column; gap:0.4rem;">
        @foreach($s['items'] as $item)
        <li>{!! $item !!}</li>
        @endforeach
    </ul>
    @endif
    @endforeach

</div>
@endsection
