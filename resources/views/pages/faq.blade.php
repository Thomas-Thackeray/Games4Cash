@extends('layouts.app')
@section('title', 'FAQ')
@section('meta_description', 'Frequently asked questions about selling your games for cash — how it works, what we accept, and how to get a free collection quote.')
@section('canonical', route('faq'))
@section('content')
<div class="container" style="max-width:800px; padding:4rem 1rem 5rem;">
    <h1 style="font-size:2.5rem; margin-bottom:0.5rem;">Frequently Asked Questions</h1>
    <p style="color:var(--text-muted); margin-bottom:2.5rem;">
        Everything you need to know about selling your games with us.
        Can't find an answer? <a href="{{ route('contact') }}" style="color:var(--accent);">Get in touch</a>.
    </p>

    @if($faqs->isEmpty())
    <p style="color:var(--text-muted);">No FAQs have been added yet. Please check back soon.</p>
    @else
    <div style="display:flex; flex-direction:column; gap:1rem;">
        @foreach($faqs as $faq)
        <details style="background:var(--bg-2); border:1px solid var(--border); border-radius:var(--radius); padding:1.25rem 1.5rem;">
            <summary style="font-weight:600; font-size:1.05rem; cursor:pointer; list-style:none; display:flex; justify-content:space-between; align-items:center; gap:1rem;">
                {{ $faq->title }}
                <span style="font-size:1.2rem; color:var(--text-muted); flex-shrink:0;">+</span>
            </summary>
            <p style="color:var(--text-muted); line-height:1.9; margin-top:1rem;">{{ $faq->description }}</p>
        </details>
        @endforeach
    </div>
    @endif
</div>
@endsection
