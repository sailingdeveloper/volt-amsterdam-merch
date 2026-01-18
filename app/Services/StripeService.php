<?php

namespace App\Services;

use App\Models\Order;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('stripe.secret'));
    }

    /**
     * Create a Stripe Checkout Session.
     *
     * @param array<int, array{name: string, price: int, quantity: int}> $allItem
     * @return Session|object
     */
    public function createCheckoutSession(
        array $allItem,
        int $feeCent,
        string $successUrl,
        string $cancelUrl,
        ?string $customerEmail = null
    ): object {
        $allLineItem = [];

        foreach ($allItem as $item) {
            $allLineItem[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $item['name'],
                    ],
                    'unit_amount' => $item['price'],
                ],
                'quantity' => $item['quantity'],
            ];
        }

        // Add processing fee as a line item.
        if ($feeCent > 0) {
            $allLineItem[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => __('shop.processing_fee'),
                    ],
                    'unit_amount' => $feeCent,
                ],
                'quantity' => 1,
            ];
        }

        $sessionData = [
            'payment_method_types' => ['ideal'],
            'line_items' => $allLineItem,
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'locale' => app()->getLocale(),
        ];

        if ($customerEmail !== null) {
            $sessionData['customer_email'] = $customerEmail;
        }

        return Session::create($sessionData);
    }

    /**
     * Retrieve a Checkout Session.
     */
    public function getSession(string $sessionId): Session
    {
        return Session::retrieve($sessionId);
    }

    /**
     * Verify and construct a webhook event.
     *
     * @return \Stripe\Event|object
     */
    public function constructWebhookEvent(string $payload, string $signature): object
    {
        return Webhook::constructEvent(
            $payload,
            $signature,
            config('stripe.webhook_secret')
        );
    }

    /**
     * Handle checkout.session.completed event.
     *
     * @param Session|object $session
     */
    public function handleCheckoutCompleted(object $session): void
    {
        $order = Order::where('stripe_session_id', $session->id)->first();

        if ($order !== null) {
            $order->update([
                'status' => 'paid',
                'stripe_payment_intent_id' => $session->payment_intent ?? null,
                'customer_email' => $session->customer_details?->email ?? $order->customer_email,
                'customer_name' => $session->customer_details?->name ?? $order->customer_name,
            ]);
        }
    }
}
