<?php

namespace App\Services;

use App\Models\Order;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeService
{
    public function __construct(
        protected OrderService $orderService
    ) {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe Checkout Session.
     *
     * @param array<int, array{name: string, price: int, quantity: int}> $items
     * @return Session|object
     */
    public function createCheckoutSession(
        array $items,
        int $feeCent,
        string $successUrl,
        string $cancelUrl,
        ?string $customerEmail = null,
        ?string $customerName = null
    ): object {
        $lineItems = [];

        foreach ($items as $item) {
            $lineItems[] = [
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
            $lineItems[] = [
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
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'locale' => app()->getLocale(),
        ];

        // Create a Stripe Customer to prefill name and email.
        if ($customerEmail !== null || $customerName !== null) {
            $customer = Customer::create(array_filter([
                'email' => $customerEmail,
                'name' => $customerName,
            ]));
            $sessionData['customer'] = $customer->id;
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
     * Create a Payment Intent for iDEAL payment.
     *
     * @return PaymentIntent|object
     */
    public function createPaymentIntent(
        int $amount,
        string $orderId,
        ?string $customerEmail = null,
        ?string $customerName = null
    ): object {
        $paymentIntentData = [
            'amount' => $amount,
            'currency' => 'eur',
            'payment_method_types' => ['ideal'],
            'metadata' => [
                'order_id' => $orderId,
            ],
        ];

        if ($customerEmail !== null || $customerName !== null) {
            $customer = Customer::create(array_filter([
                'email' => $customerEmail,
                'name' => $customerName,
            ]));
            $paymentIntentData['customer'] = $customer->id;
        }

        return PaymentIntent::create($paymentIntentData);
    }

    /**
     * Retrieve a Payment Intent.
     */
    public function getPaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return PaymentIntent::retrieve($paymentIntentId);
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
            config('services.stripe.webhook_secret')
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
            if ($session->payment_intent !== null) {
                $order->update(['stripe_payment_intent_id' => $session->payment_intent]);
            }
            $this->orderService->markOrderPaid($order);
        }
    }

    /**
     * Handle payment_intent.succeeded event.
     *
     * @param PaymentIntent|object $paymentIntent
     */
    public function handlePaymentIntentSucceeded(object $paymentIntent): void
    {
        $order = Order::where('stripe_payment_intent_id', $paymentIntent->id)
            ->orWhere('payment_id', $paymentIntent->id)
            ->first();

        if ($order !== null && $order->status !== 'paid') {
            $this->orderService->markOrderPaid($order);
        }
    }
}
