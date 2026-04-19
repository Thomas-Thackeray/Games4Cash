<?php

namespace App\Mail;

use App\Models\CashOrder;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminNewQuoteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User      $user,
        public CashOrder $order,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . config('app.name') . '] New Quote Submitted — ' . $this->order->order_ref,
        );
    }

    public function content(): Content
    {
        $items = is_string($this->order->items)
            ? json_decode($this->order->items, true)
            : (array) $this->order->items;

        $tokens = [
            '{order_ref}'   => $this->order->order_ref,
            '{username}'    => $this->user->username,
            '{first_name}'  => $this->user->first_name,
            '{total}'       => '£' . number_format((float) $this->order->total_gbp, 2),
            '{items_count}' => count($items),
            '{site_name}'   => config('app.name'),
        ];

        return new Content(
            view: 'emails.admin-new-quote',
            with: [
                'user'      => $this->user,
                'order'     => $this->order,
                'items'     => $items,
                'emailBody' => strtr(
                    Setting::get('email_admin_new_quote_body', 'A new cash quote has been submitted on {site_name}.'),
                    $tokens
                ),
            ],
        );
    }
}
