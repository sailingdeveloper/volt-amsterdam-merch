<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Order $order
    ) {
        $this->locale($order->locale ?? 'nl');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('shop.order_confirmation_subject', ['number' => $this->order->id]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-confirmation',
        );
    }
}
