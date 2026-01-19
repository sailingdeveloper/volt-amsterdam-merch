<?php

namespace App\Http\Controllers;

use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __construct(
        protected StripeService $stripeService
    ) {}

    /**
     * Handle Stripe webhook events.
     */
    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        if ($signature === null) {
            return response('Missing signature', 400);
        }

        try {
            $event = $this->stripeService->constructWebhookEvent($payload, $signature);
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);

            return response('Invalid signature', 400);
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $this->stripeService->handleCheckoutCompleted($session);
                break;

            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $this->stripeService->handlePaymentIntentSucceeded($paymentIntent);
                break;

            default:
                // Ignore other event types.
                break;
        }

        return response('OK', 200);
    }
}
