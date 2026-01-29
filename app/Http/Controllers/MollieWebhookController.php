<?php

namespace App\Http\Controllers;

use App\Services\Payment\MolliePaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MollieWebhookController extends Controller
{
    public function __construct(
        protected MolliePaymentService $mollieService
    ) {}

    /**
     * Handle Mollie webhook events.
     */
    public function handle(Request $request): Response
    {
        $paymentId = $request->input('id');

        if ($paymentId === null) {
            Log::warning('Mollie webhook received without payment ID');

            return response('Missing payment ID', 400);
        }

        try {
            $this->mollieService->handleWebhook($paymentId);
        } catch (\Exception $e) {
            Log::error('Mollie webhook processing failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return response('Error processing webhook', 500);
        }

        return response('OK', 200);
    }
}
