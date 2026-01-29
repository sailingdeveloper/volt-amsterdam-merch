<?php

namespace App\Services\Payment;

use App\Contracts\PaymentServiceInterface;
use App\Models\Order;
use App\Services\OrderService;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class StripePaymentService implements PaymentServiceInterface
{
    public function __construct(
        protected OrderService $orderService
    ) {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Get the provider identifier.
     */
    public function getProvider(): string
    {
        return 'stripe';
    }

    /**
     * Create a payment for the given order.
     */
    public function createPayment(Order $order): PaymentResult
    {
        $paymentIntentData = [
            'amount' => $order->total,
            'currency' => 'eur',
            'payment_method_types' => ['ideal'],
            'metadata' => [
                'order_id' => $order->id,
            ],
        ];

        if ($order->customer_email !== null || $order->customer_name !== null) {
            $customer = Customer::create(array_filter([
                'email' => $order->customer_email,
                'name' => $order->customer_name,
            ]));
            $paymentIntentData['customer'] = $customer->id;
        }

        $paymentIntent = PaymentIntent::create($paymentIntentData);

        return new PaymentResult(
            paymentId: $paymentIntent->id,
            clientSecret: $paymentIntent->client_secret,
        );
    }

    /**
     * Get a payment by its ID.
     */
    public function getPayment(string $paymentId): object
    {
        return PaymentIntent::retrieve($paymentId);
    }

    /**
     * Handle a webhook request for a payment.
     */
    public function handleWebhook(string $paymentId): void
    {
        $order = Order::where('stripe_payment_intent_id', $paymentId)
            ->orWhere('payment_id', $paymentId)
            ->first();

        if ($order !== null && $order->status !== 'paid') {
            $this->orderService->markOrderPaid($order);
        }
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
     * Handle checkout.session.completed event (legacy support).
     *
     * @param object $session
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
     * @param object $paymentIntent
     */
    public function handlePaymentIntentSucceeded(object $paymentIntent): void
    {
        $this->handleWebhook($paymentIntent->id);
    }
}
