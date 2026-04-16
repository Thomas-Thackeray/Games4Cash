@extends('layouts.app')
@section('title', 'Email Templates')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Email Templates</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.dashboard') }}" style="color:var(--accent);">← Dashboard</a></p>
        </div>
        <button type="submit" form="email-templates-form" class="btn btn--primary">Save Templates</button>
    </div>

    {{--
        Three isolated test forms — placed outside the save form so they are
        never nested. Test buttons reference them via the form= attribute.
    --}}
    <form id="test-form-order"   method="POST" action="{{ route('admin.email-templates.test') }}">@csrf<input type="hidden" name="template" value="order"></form>
    <form id="test-form-welcome" method="POST" action="{{ route('admin.email-templates.test') }}">@csrf<input type="hidden" name="template" value="welcome"></form>
    <form id="test-form-reset"   method="POST" action="{{ route('admin.email-templates.test') }}">@csrf<input type="hidden" name="template" value="reset"></form>

    {{-- Token reference --}}
    <div class="settings-card settings-card--wide" style="margin-bottom:1.5rem; border-left:3px solid var(--accent);">
        <h2 class="settings-card__title">Available Placeholders</h2>
        <p class="settings-hint">Use these tokens anywhere in the text fields below — they are replaced with real values when the email is sent.</p>
        <div style="display:flex; gap:1.5rem; flex-wrap:wrap; margin-top:0.75rem;">
            <div><code style="background:rgba(255,255,255,0.07); padding:2px 8px; border-radius:4px; font-size:0.85rem;">{first_name}</code> <span class="settings-hint" style="display:inline;">— recipient's first name</span></div>
            <div><code style="background:rgba(255,255,255,0.07); padding:2px 8px; border-radius:4px; font-size:0.85rem;">{site_name}</code> <span class="settings-hint" style="display:inline;">— your site name ({{ config('app.name') }})</span></div>
        </div>
    </div>

    {{-- Save form wraps all cards so textareas are direct form children --}}
    <form method="POST" action="{{ route('admin.email-templates.update') }}" id="email-templates-form">
        @csrf

        {{-- Order Confirmation --}}
        <div class="settings-card settings-card--wide" style="margin-bottom:1.5rem;">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom:0.25rem;">
                <div>
                    <h2 class="settings-card__title" style="margin-bottom:0.25rem;">Order Confirmation Email</h2>
                    <p class="settings-hint">Sent to the customer after they submit a Get Cash quote.</p>
                </div>
                <button type="button" form="test-form-order"
                    class="btn btn--outline btn--sm" style="flex-shrink:0; margin-top:2px;"
                    data-confirm="Send a test Order Confirmation email to thomasthackeray0@gmail.com?">
                    Send Test
                </button>
            </div>

            <div class="form-group" style="margin-top:1.25rem;">
                <label class="form-label">Intro Paragraph</label>
                <p class="settings-hint">Shown directly below the "Thank you, {first_name}!" heading.</p>
                <textarea name="email_order_intro" rows="4"
                    class="form-input" style="width:100%; resize:vertical; font-size:0.9rem; line-height:1.6;">{{ old('email_order_intro', $templates['email_order_intro']) }}</textarea>
                @error('email_order_intro')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group" style="margin-top:1.25rem; padding-top:1.25rem; border-top:1px solid var(--border);">
                <label class="form-label">Packaging & Inspection Note</label>
                <p class="settings-hint">Shown near the bottom of the email, below the collection address.</p>
                <textarea name="email_order_packaging_note" rows="3"
                    class="form-input" style="width:100%; resize:vertical; font-size:0.9rem; line-height:1.6;">{{ old('email_order_packaging_note', $templates['email_order_packaging_note']) }}</textarea>
                @error('email_order_packaging_note')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Welcome Email --}}
        <div class="settings-card settings-card--wide" style="margin-bottom:1.5rem;">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom:0.25rem;">
                <div>
                    <h2 class="settings-card__title" style="margin-bottom:0.25rem;">Welcome Email</h2>
                    <p class="settings-hint">Sent when a user creates a new account.</p>
                </div>
                <button type="button" form="test-form-welcome"
                    class="btn btn--outline btn--sm" style="flex-shrink:0; margin-top:2px;"
                    data-confirm="Send a test Welcome email to thomasthackeray0@gmail.com?">
                    Send Test
                </button>
            </div>

            <div class="form-group" style="margin-top:1.25rem;">
                <label class="form-label">Intro Paragraph</label>
                <p class="settings-hint">Shown directly below the "Welcome, {first_name}!" heading.</p>
                <textarea name="email_welcome_intro" rows="4"
                    class="form-input" style="width:100%; resize:vertical; font-size:0.9rem; line-height:1.6;">{{ old('email_welcome_intro', $templates['email_welcome_intro']) }}</textarea>
                @error('email_welcome_intro')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group" style="margin-top:1.25rem; padding-top:1.25rem; border-top:1px solid var(--border);">
                <label class="form-label">Footer Note</label>
                <p class="settings-hint">Small note shown at the bottom of the email (e.g. "if you didn't create this account…").</p>
                <textarea name="email_welcome_footer_note" rows="2"
                    class="form-input" style="width:100%; resize:vertical; font-size:0.9rem; line-height:1.6;">{{ old('email_welcome_footer_note', $templates['email_welcome_footer_note']) }}</textarea>
                @error('email_welcome_footer_note')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Password Reset --}}
        <div class="settings-card settings-card--wide" style="margin-bottom:1.5rem;">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom:0.25rem;">
                <div>
                    <h2 class="settings-card__title" style="margin-bottom:0.25rem;">Password Reset Email</h2>
                    <p class="settings-hint">Sent when a user requests a password reset link.</p>
                </div>
                <button type="button" form="test-form-reset"
                    class="btn btn--outline btn--sm" style="flex-shrink:0; margin-top:2px;"
                    data-confirm="Send a test Password Reset email to thomasthackeray0@gmail.com?">
                    Send Test
                </button>
            </div>

            <div class="form-group" style="margin-top:1.25rem;">
                <label class="form-label">Intro Paragraph</label>
                <p class="settings-hint">Shown above the reset button. Use <code style="font-size:0.8rem;">{first_name}</code> for the recipient's name.</p>
                <textarea name="email_reset_intro" rows="4"
                    class="form-input" style="width:100%; resize:vertical; font-size:0.9rem; line-height:1.6;">{{ old('email_reset_intro', $templates['email_reset_intro']) }}</textarea>
                @error('email_reset_intro')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group" style="margin-top:1.25rem; padding-top:1.25rem; border-top:1px solid var(--border);">
                <label class="form-label">Footer Note</label>
                <p class="settings-hint">Shown at the bottom (e.g. "if you didn't request this…").</p>
                <textarea name="email_reset_footer_note" rows="2"
                    class="form-input" style="width:100%; resize:vertical; font-size:0.9rem; line-height:1.6;">{{ old('email_reset_footer_note', $templates['email_reset_footer_note']) }}</textarea>
                @error('email_reset_footer_note')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>

    </form>{{-- #email-templates-form --}}

</div>
@endsection
