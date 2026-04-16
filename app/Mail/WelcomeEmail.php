<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to ' . config('app.name') . ' – You\'re all set!',
        );
    }

    public function content(): Content
    {
        $tokens = ['{first_name}' => $this->user->first_name, '{site_name}' => config('app.name')];
        return new Content(
            view: 'emails.welcome',
            with: [
                'emailIntro'      => strtr(Setting::get('email_welcome_intro',       "Thank you for creating an account on {site_name}. Your account is all set — you can now explore thousands of games, browse by platform and genre, and discover your next favourite title."), $tokens),
                'emailFooterNote' => strtr(Setting::get('email_welcome_footer_note', 'If you did not create this account, you can safely ignore this email — no action is required.'), $tokens),
            ],
        );
    }
}
