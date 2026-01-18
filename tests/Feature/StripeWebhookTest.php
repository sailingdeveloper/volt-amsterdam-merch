<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'fee' => 32,
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
        $order = Order::create([
            'stripe_session_id' => 'cs_test_update',
            'customer_email' => 'pending@example.com',
            'customer_name' => 'Pending',
            'subtotal' => 2500,
            'fee' => 32,
            'total' => 2532,
            'status' => 'pending',
        ]);

        $mockSession = new \stdClass();
        $mockSession->id = 'cs_test_update';
        $mockSession->payment_intent = 'pi_test_456';
        $mockSession->customer_details = new \stdClass();
        $mockSession->customer_details->email = 'real@example.com';
        $mockSession->customer_details->name = 'Real Customer';

        $stripeService = new StripeService();
        $stripeService->handleCheckoutCompleted($mockSession);

        $order->refresh();

        $this->assertEquals('paid', $order->status);
        $this->assertEquals('pi_test_456', $order->stripe_payment_intent_id);
        $this->assertEquals('real@example.com', $order->customer_email);
        $this->assertEquals('Real Customer', $order->customer_name);
    }
}
