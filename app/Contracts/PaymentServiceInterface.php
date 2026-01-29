<?php

namespace App\Contracts;

use App\Models\Order;
use App\Services\Payment\PaymentResult;

interface PaymentServiceInterface
{
    /**
     * Create a payment for the given order.
     */
    public function createPayment(Order $order): PaymentResult;

    /**
     * Get a payment by its ID.
     */
    public function getPayment(string $paymentId): object;

    /**
     * Handle a webhook request from the payment provider.
     */
    public function handleWebhook(string $paymentId): void;

    /**
     * Get the provider identifier.
     */
    public function getProvider(): string;
}
