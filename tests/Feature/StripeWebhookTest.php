<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_rejects_missing_signature(): void
    {
        $response = $this->post('/webhook/stripe', [], [
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(400);
        $response->assertSee('Missing signature');
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $mockStripeService = Mockery::mock(StripeService::class);
        $mockStripeService->shouldReceive('constructWebhookEvent')
            ->once()
            ->andThrow(new \Exception('Invalid signature'));

        $this->app->instance(StripeService::class, $mockStripeService);

        $response = $this->post('/webhook/stripe', [], [
            'Content-Type' => 'application/json',
            'Stripe-Signature' => 'invalid_signature',
        ]);

        $response->assertStatus(400);
        $response->assertSee('Invalid signature');
    }

    public function test_webhook_handles_checkout_completed(): void
    {
        $order = Order::create([
            'stripe_session_id' => 'cs_test_webhook',
            'customer_email' => 'pending@example.com',
            'customer_name' => 'Pending',
            'subtotal' => 2500,
            'fee' => 29,
            'total' => 2532,
            'status' => 'pending',
        ]);

        $mockSession = new \stdClass();
        $mockSession->id = 'cs_test_webhook';
        $mockSession->payment_intent = 'pi_test_123';
        $mockSession->customer_details = new \stdClass();
        $mockSession->customer_details->email = 'customer@example.com';
        $mockSession->customer_details->name = 'Customer Name';

        $mockEvent = new \stdClass();
        $mockEvent->type = 'checkout.session.completed';
        $mockEvent->data = new \stdClass();
        $mockEvent->data->object = $mockSession;

        $mockStripeService = Mockery::mock(StripeService::class);
        $mockStripeService->shouldReceive('constructWebhookEvent')
            ->once()
            ->andReturn($mockEvent);
        $mockStripeService->shouldReceive('handleCheckoutCompleted')
            ->once()
            ->with($mockSession);

        $this->app->instance(StripeService::class, $mockStripeService);

        $response = $this->post('/webhook/stripe', [], [
            'Content-Type' => 'application/json',
            'Stripe-Signature' => 'valid_signature',
        ]);

        $response->assertStatus(200);
        $response->assertSee('OK');
    }

    public function test_webhook_ignores_unknown_events(): void
    {
        $mockEvent = new \stdClass();
        $mockEvent->type = 'payment_intent.created';
        $mockEvent->data = new \stdClass();
        $mockEvent->data->object = new \stdClass();

        $mockStripeService = Mockery::mock(StripeService::class);
        $mockStripeService->shouldReceive('constructWebhookEvent')
            ->once()
            ->andReturn($mockEvent);

        $this->app->instance(StripeService::class, $mockStripeService);

        $response = $this->post('/webhook/stripe', [], [
            'Content-Type' => 'application/json',
            'Stripe-Signature' => 'valid_signature',
        ]);

        $response->assertStatus(200);
    }

    public function test_stripe_service_updates_order_on_checkout_completed(): void
    {
        Mail::fake();

        // Customer info is now collected on our checkout page, not from Stripe.
        $order = Order::create([
            'stripe_session_id' => 'cs_test_update',
            'customer_email' => 'customer@example.com',
            'customer_name' => 'Test Customer',
            'customer_phone' => '+31612345678',
            'billing_address_line1' => 'Test Street 1',
            'billing_postal_code' => '1234AB',
            'billing_city' => 'Amsterdam',
            'subtotal' => 2500,
            'fee' => 29,
            'total' => 2532,
            'status' => 'pending',
        ]);

        $mockSession = new \stdClass();
        $mockSession->id = 'cs_test_update';
        $mockSession->payment_intent = 'pi_test_456';

        $stripeService = new StripeService();
        $stripeService->handleCheckoutCompleted($mockSession);

        $order->refresh();

        // Verify status and payment intent are updated.
        $this->assertEquals('paid', $order->status);
        $this->assertEquals('pi_test_456', $order->stripe_payment_intent_id);

        // Customer info should remain unchanged (collected on our checkout page).
        $this->assertEquals('customer@example.com', $order->customer_email);
        $this->assertEquals('Test Customer', $order->customer_name);
    }

    public function test_webhook_handles_payment_intent_succeeded(): void
    {
        $mockPaymentIntent = new \stdClass();
        $mockPaymentIntent->id = 'pi_test_webhook';

        $mockEvent = new \stdClass();
        $mockEvent->type = 'payment_intent.succeeded';
        $mockEvent->data = new \stdClass();
        $mockEvent->data->object = $mockPaymentIntent;

        $mockStripeService = Mockery::mock(StripeService::class);
        $mockStripeService->shouldReceive('constructWebhookEvent')
            ->once()
            ->andReturn($mockEvent);
        $mockStripeService->shouldReceive('handlePaymentIntentSucceeded')
            ->once()
            ->with($mockPaymentIntent);

        $this->app->instance(StripeService::class, $mockStripeService);

        $response = $this->post('/webhook/stripe', [], [
            'Content-Type' => 'application/json',
            'Stripe-Signature' => 'valid_signature',
        ]);

        $response->assertStatus(200);
        $response->assertSee('OK');
    }

    public function test_stripe_service_updates_order_on_payment_intent_succeeded(): void
    {
        Mail::fake();

        $order = Order::create([
            'stripe_payment_intent_id' => 'pi_test_success',
            'customer_email' => 'customer@example.com',
            'customer_name' => 'Test Customer',
            'billing_address_line1' => 'Test Street 1',
            'billing_postal_code' => '1234AB',
            'billing_city' => 'Amsterdam',
            'subtotal' => 2500,
            'fee' => 29,
            'total' => 2532,
            'status' => 'pending',
        ]);

        $mockPaymentIntent = new \stdClass();
        $mockPaymentIntent->id = 'pi_test_success';

        $stripeService = new StripeService();
        $stripeService->handlePaymentIntentSucceeded($mockPaymentIntent);

        $order->refresh();

        $this->assertEquals('paid', $order->status);
    }
}
