@extends('layouts.app')
@section('title', 'Contact Us')
@section('content')
<div class="container" style="max-width:600px; padding:4rem 1rem;">
    <h1 style="font-size:2.5rem; margin-bottom:0.5rem;">Contact Us</h1>
    <p style="color:var(--text-muted); line-height:1.9; margin-bottom:2rem;">
        Have a question, suggestion, or spotted an issue? We'd love to hear from you.
    </p>

    @if($errors->any())
    <div class="flash-banner flash-banner--error" style="margin-bottom:1.5rem;">
        <ul style="margin:0; padding-left:1.25rem;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('contact.submit') }}" style="display:flex; flex-direction:column; gap:1.25rem;">
        @csrf
        <div>
            <label style="display:block; margin-bottom:0.4rem; font-weight:500;">Name</label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="Your name"
                style="width:100%; padding:0.75rem 1rem; background:var(--bg-2); border:1px solid var(--border); border-radius:var(--radius); color:var(--text); font-size:1rem;">
        </div>
        <div>
            <label style="display:block; margin-bottom:0.4rem; font-weight:500;">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="your@email.com"
                style="width:100%; padding:0.75rem 1rem; background:var(--bg-2); border:1px solid var(--border); border-radius:var(--radius); color:var(--text); font-size:1rem;">
        </div>
        <div>
            <label style="display:block; margin-bottom:0.4rem; font-weight:500;">Contact Number <span style="color:var(--text-muted); font-weight:400;">(optional)</span></label>
            <input type="tel" name="contact_number" value="{{ old('contact_number') }}" placeholder="+44 7700 000000"
                style="width:100%; padding:0.75rem 1rem; background:var(--bg-2); border:1px solid var(--border); border-radius:var(--radius); color:var(--text); font-size:1rem;">
        </div>
        <div>
            <label style="display:block; margin-bottom:0.4rem; font-weight:500;">Message</label>
            <textarea name="message" rows="6" placeholder="Your message…"
                style="width:100%; padding:0.75rem 1rem; background:var(--bg-2); border:1px solid var(--border); border-radius:var(--radius); color:var(--text); font-size:1rem; resize:vertical;">{{ old('message') }}</textarea>
        </div>
        <button type="submit" class="btn btn--primary" style="align-self:flex-start;">Send Message</button>
    </form>
</div>
@endsection
