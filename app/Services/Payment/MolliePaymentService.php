<?php

namespace App\Services\Payment;

use App\Contracts\PaymentServiceInterface;
use App\Models\Order;
use App\Services\OrderService;
use Mollie\Api\MollieApiClient;

class MolliePaymentService implements PaymentServiceInterface
{
    protected MollieApiClient $mollie;

    public function __construct(
        protected OrderService $orderService
    ) {
        $this->mollie = new MollieApiClient();
        $apiKey = config('services.mollie.key');
        if ($apiKey !== null) {
            $this->mollie->setApiKey($apiKey);
        }
    }

    /**
     * Get the provider identifier.
     */
    public function getProvider(): string
    {
        return 'mollie';
    }

    /**
     * Create a payment for the given order.
     */
    public function createPayment(Order $order): PaymentResult
    {
        $webhookUrl = config('services.mollie.webhook_url') ?? route('webhook.mollie');

        $payment = $this->mollie->payments->create([
            'amount' => [
                'currency' => 'EUR',
                'value' => number_format($order->total / 100, 2, '.', ''),
            ],
            'description' => config('app.name') . ' #' . $order->order_number,
            'redirectUrl' => route('checkout.success', ['order_id' => $order->id]),
            'webhookUrl' => $webhookUrl,
            'method' => 'ideal',
            'locale' => $this->getMollieLocale($order->locale ?? app()->getLocale()),
            'metadata' => [
                'order_id' => $order->id,
            ],
        ]);

        return new PaymentResult(
            paymentId: $payment->id,
            redirectUrl: $payment->getCheckoutUrl(),
        );
    }

    /**
     * Get a payment by its ID.
     */
    public function getPayment(string $paymentId): object
    {
        return $this->mollie->payments->get($paymentId);
    }

    /**
     * Handle a webhook request for a payment.
     */
    public function handleWebhook(string $paymentId): void
    {
        $payment = $this->mollie->payments->get($paymentId);

        if ($payment->isPaid()) {
            $order = Order::where('payment_id', $paymentId)->first();

            if ($order !== null && $order->status !== 'paid') {
                $this->orderService->markOrderPaid($order);
            }
        }
    }

    /**
     * Convert Laravel locale to Mollie locale format.
     */
    protected function getMollieLocale(string $locale): string
    {
        $localeMap = [
            'nl' => 'nl_NL',
            'en' => 'en_US',
            'de' => 'de_DE',
            'fr' => 'fr_FR',
        ];

        return $localeMap[$locale] ?? 'nl_NL';
    }
}
