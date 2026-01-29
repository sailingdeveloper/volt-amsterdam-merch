<?php

namespace App\Services\Payment;

readonly class PaymentResult
{
    public function __construct(
        public string $paymentId,
        public ?string $redirectUrl = null,
        public ?string $clientSecret = null,
    ) {}

    /**
     * Check if this payment requires a redirect (Mollie flow).
     */
    public function requiresRedirect(): bool
    {
        return $this->redirectUrl !== null;
    }

    /**
     * Check if this payment uses client-side confirmation (Stripe flow).
     */
    public function requiresClientConfirmation(): bool
    {
        return $this->clientSecret !== null;
    }
}
