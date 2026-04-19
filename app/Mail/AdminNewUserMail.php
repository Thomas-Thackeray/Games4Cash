<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNewUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . config('app.name') . '] New User Registration — ' . $this->user->username,
        );
    }

    public function content(): Content
    {
        $tokens = [
            '{username}'   => $this->user->username,
            '{first_name}' => $this->user->first_name,
            '{surname}'    => $this->user->surname,
            '{email}'      => $this->user->email,
            '{site_name}'  => config('app.name'),
        ];

        return new Content(
            view: 'emails.admin-new-user',
            with: [
                'user'      => $this->user,
                'emailBody' => strtr(
                    Setting::get('email_admin_new_user_body', 'A new user has just registered on {site_name}.'),
                    $tokens
                ),
            ],
        );
    }
}
