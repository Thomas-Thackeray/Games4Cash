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

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User      $user,
        public CashOrder $order,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Confirmed – ' . $this->order->order_ref . ' | ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        $tokens = ['{first_name}' => $this->user->first_name, '{site_name}' => config('app.name')];
        return new Content(
            view: 'emails.order-confirmation',
            with: [
                'emailIntro'        => strtr(Setting::get('email_order_intro',         "Your cash quote has been received and we're reviewing it now.\nA member of our team will be in touch shortly."), $tokens),
                'emailPackagingNote' => strtr(Setting::get('email_order_packaging_note', 'Please ensure your games are ready and packaged securely before the collection date. All prices are estimates and may be adjusted upon physical inspection.'), $tokens),
            ],
        );
    }
}
