<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User   $user,
        public string $token,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset Your ' . config('app.name') . ' Password',
        );
    }

    public function content(): Content
    {
        $tokens = ['{first_name}' => $this->user->first_name, '{site_name}' => config('app.name')];
        return new Content(
            view: 'emails.password-reset',
            with: [
                'emailIntro'      => strtr(Setting::get('email_reset_intro',       'Hi {first_name}, we received a request to reset the password for your {site_name} account. Click the button below to choose a new password. This link will expire in 60 minutes.'), $tokens),
                'emailFooterNote' => strtr(Setting::get('email_reset_footer_note', 'If you did not request a password reset, no action is required — your password will remain unchanged.'), $tokens),
            ],
        );
    }
}
