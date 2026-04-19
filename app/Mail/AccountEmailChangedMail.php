<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountEmailChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $oldEmail) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your ' . config('app.name') . ' email address was changed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-email-changed',
            with: [
                'user'     => $this->user,
                'oldEmail' => $this->oldEmail,
            ],
        );
    }
}
