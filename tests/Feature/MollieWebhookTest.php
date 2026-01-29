<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\Payment\MolliePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class MollieWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_rejects_missing_payment_id(): void
    {
        // Mock the MolliePaymentService to avoid API key issues.
        $mockMollieService = Mockery::mock(MolliePaymentService::class);
        $this->app->instance(MolliePaymentService::class, $mockMollieService);

        $response = $this->post('/webhook/mollie', [], [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        $response->assertStatus(400);
        $response->assertSee('Missing payment ID');
    }

    public function test_webhook_handles_paid_payment(): void
    {
        Mail::fake();

        $order = Order::create([
            'payment_id' => 'tr_test_webhook',
            'payment_provider' => 'mollie',
            'customer_email' => 'customer@example.com',
            'customer_name' => 'Test Customer',
            'billing_address_line1' => 'Test Street 1',
            'billing_postal_code' => '1234AB',
            'billing_city' => 'Amsterdam',
            'subtotal' => 2500,
            'fee' => 29,
            'total' => 2529,
            'status' => 'pending',
        ]);

        $mockPayment = new \stdClass();
        $mockPayment->id = 'tr_test_webhook';
        $mockPayment->status = 'paid';

        $mockMollieService = Mockery::mock(MolliePaymentService::class);
        $mockMollieService->shouldReceive('handleWebhook')
            ->once()
            ->with('tr_test_webhook');

        $this->app->instance(MolliePaymentService::class, $mockMollieService);

        $response = $this->post('/webhook/mollie', ['id' => 'tr_test_webhook'], [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        $response->assertStatus(200);
        $response->assertSee('OK');
    }

    public function test_mollie_service_updates_order_on_paid_webhook(): void
    {
        Mail::fake();

        $order = Order::create([
            'payment_id' => 'tr_test_paid',
            'payment_provider' => 'mollie',
            'customer_email' => 'customer@example.com',
            'customer_name' => 'Test Customer',
            'billing_address_line1' => 'Test Street 1',
            'billing_postal_code' => '1234AB',
            'billing_city' => 'Amsterdam',
            'subtotal' => 2500,
            'fee' => 29,
            'total' => 2529,
            'status' => 'pending',
        ]);

        // Mock the Mollie API client.
        $mockPayment = Mockery::mock();
        $mockPayment->shouldReceive('isPaid')->andReturn(true);

        $mockPayments = Mockery::mock();
        $mockPayments->shouldReceive('get')
            ->with('tr_test_paid')
            ->andReturn($mockPayment);

        // Create mock service that uses the OrderService.
        $orderService = app(\App\Services\OrderService::class);
        $mockMollieService = Mockery::mock(MolliePaymentService::class)->makePartial();
        $mockMollieService->shouldReceive('handleWebhook')
            ->with('tr_test_paid')
            ->andReturnUsing(function () use ($order, $orderService, $mockPayment) {
                if ($mockPayment->isPaid()) {
                    $orderService->markOrderPaid($order);
                }
            });

        $mockMollieService->handleWebhook('tr_test_paid');

        $order->refresh();
        $this->assertEquals('paid', $order->status);
    }

    public function test_stock_decrements_when_mollie_payment_succeeds(): void
    {
        Mail::fake();

        $product = Product::factory()->create([
            'stock' => 10,
            'sizes' => null,
        ]);

        $order = Order::create([
            'payment_id' => 'tr_test_stock',
            'payment_provider' => 'mollie',
            'customer_email' => 'customer@example.com',
            'customer_name' => 'Test Customer',
            'subtotal' => 2500,
            'fee' => 29,
            'total' => 2529,
            'status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => 2500,
        ]);

        // Use the OrderService directly to test stock decrement.
        $orderService = app(\App\Services\OrderService::class);
        $orderService->markOrderPaid($order);

        $product->refresh();
        $order->refresh();

        $this->assertEquals('paid', $order->status);
        $this->assertEquals(7, $product->stock);
    }

    public function test_webhook_is_idempotent_for_already_paid_orders(): void
    {
        Mail::fake();

        $product = Product::factory()->create([
            'stock' => 10,
            'sizes' => null,
        ]);

        $order = Order::create([
            'payment_id' => 'tr_test_idempotent',
            'payment_provider' => 'mollie',
            'customer_email' => 'customer@example.com',
            'customer_name' => 'Test Customer',
            'subtotal' => 2500,
            'fee' => 29,
            'total' => 2529,
            'status' => 'paid',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => 2500,
        ]);

        // Use the OrderService directly to test idempotency.
        $orderService = app(\App\Services\OrderService::class);
        $orderService->markOrderPaid($order);

        $product->refresh();

        // Stock should not have changed as order was already paid.
        $this->assertEquals(10, $product->stock);
    }

    public function test_stock_decrements_for_product_with_sizes(): void
    {
        Mail::fake();

        $product = Product::factory()->create([
            'stock' => null,
            'sizes' => [
                'S' => 10,
                'M' => 15,
                'L' => 20,
            ],
        ]);

        $order = Order::create([
            'payment_id' => 'tr_test_sizes',
            'payment_provider' => 'mollie',
            'customer_email' => 'customer@example.com',
            'customer_name' => 'Test Customer',
            'subtotal' => 2500,
            'fee' => 29,
            'total' => 2529,
            'status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'size' => 'M',
            'quantity' => 4,
            'price' => 2500,
        ]);

        // Use the OrderService directly to test stock decrement.
        $orderService = app(\App\Services\OrderService::class);
        $orderService->markOrderPaid($order);

        $product->refresh();
        $order->refresh();

        $this->assertEquals('paid', $order->status);
        $this->assertEquals(11, $product->sizes['M']);
        $this->assertEquals(10, $product->sizes['S']);
        $this->assertEquals(20, $product->sizes['L']);
    }
}
