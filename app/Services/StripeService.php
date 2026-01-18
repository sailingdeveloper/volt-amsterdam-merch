<?php

namespace App\Services;

use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
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
            'invoice_creation' => [
                'enabled' => true,
            ],
            'billing_address_collection' => 'required',
            'phone_number_collection' => [
                'enabled' => true,
            ],
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
            $customerDetails = $session->customer_details ?? null;
            $address = $customerDetails->address ?? null;

            $order->update([
                'status' => 'paid',
                'stripe_payment_intent_id' => $session->payment_intent ?? null,
                'customer_email' => $customerDetails?->email ?? $order->customer_email,
                'customer_name' => $customerDetails?->name ?? $order->customer_name,
                'customer_phone' => $customerDetails?->phone ?? null,
                'billing_address_line1' => $address?->line1 ?? null,
                'billing_address_line2' => $address?->line2 ?? null,
                'billing_city' => $address?->city ?? null,
                'billing_postal_code' => $address?->postal_code ?? null,
                'billing_country' => $address?->country ?? null,
            ]);

            // Decrement stock for each order item.
            foreach ($order->item as $orderItem) {
                if ($orderItem->product !== null) {
                    $orderItem->product->decrementStockForSize(
                        $orderItem->size ?? '',
                        $orderItem->quantity
                    );
                }
            }

            $this->sendOrderConfirmationEmail($order);
        }
    }

    /**
     * Send order confirmation email to the customer and all admins.
     */
    protected function sendOrderConfirmationEmail(Order $order): void
    {
        if ($order->customer_email === null) {
            return;
        }

        // Send to customer.
        Mail::to($order->customer_email)->send(new OrderConfirmation($order));

        // Send to all admins.
        $allAdminEmail = User::pluck('email')->toArray();
        foreach ($allAdminEmail as $adminEmail) {
            if ($adminEmail !== $order->customer_email) {
                Mail::to($adminEmail)->send(new OrderConfirmation($order));
            }
        }
    }
}
