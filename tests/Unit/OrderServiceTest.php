<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_order_paid_updates_status(): void
    {
        Mail::fake();

        $order = Order::create([
            'payment_id' => 'test_123',
            'payment_provider' => 'stripe',
            'customer_email' => 'test@example.com',
            'customer_name' => 'Test User',
            'subtotal' => 2500,
            'fee' => 29,
            'total' => 2529,
            'status' => 'pending',
        ]);

        $orderService = new OrderService();
        $orderService->markOrderPaid($order);

        $order->refresh();
        $this->assertEquals('paid', $order->status);
    }

    public function test_mark_order_paid_is_idempotent(): void
    {
        Mail::fake();

        $product = Product::factory()->create(['stock' => 10]);

        $order = Order::create([
            'payment_id' => 'test_idempotent',
            'payment_provider' => 'stripe',
            'customer_email' => 'test@example.com',
            'customer_name' => 'Test User',
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

        $orderService = new OrderService();
        $orderService->markOrderPaid($order);

        $product->refresh();
        // Stock should not have changed since order was already paid.
        $this->assertEquals(10, $product->stock);
    }

    public function test_mark_order_paid_decrements_stock(): void
    {
        Mail::fake();

        $product = Product::factory()->create([
            'stock' => 10,
            'sizes' => null,
        ]);

        $order = Order::create([
            'payment_id' => 'test_stock',
            'payment_provider' => 'stripe',
            'customer_email' => 'test@example.com',
            'customer_name' => 'Test User',
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

        $orderService = new OrderService();
        $orderService->markOrderPaid($order);

        $product->refresh();
        $this->assertEquals(7, $product->stock);
    }

    public function test_mark_order_paid_decrements_stock_for_sizes(): void
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
            'payment_id' => 'test_sizes',
            'payment_provider' => 'stripe',
            'customer_email' => 'test@example.com',
            'customer_name' => 'Test User',
            'subtotal' => 2500,
            'fee' => 29,
            'total' => 2529,
            'status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'size' => 'M',
            'quantity' => 5,
            'price' => 2500,
        ]);

        $orderService = new OrderService();
        $orderService->markOrderPaid($order);

        $product->refresh();
        $this->assertEquals(10, $product->sizes['M']);
        $this->assertEquals(10, $product->sizes['S']);
        $this->assertEquals(20, $product->sizes['L']);
    }

    public function test_mark_order_paid_handles_multiple_items(): void
    {
        Mail::fake();

        $product1 = Product::factory()->create([
            'stock' => 10,
            'sizes' => null,
        ]);

        $product2 = Product::factory()->create([
            'stock' => null,
            'sizes' => ['S' => 20, 'L' => 30],
        ]);

        $order = Order::create([
            'payment_id' => 'test_multiple',
            'payment_provider' => 'stripe',
            'customer_email' => 'test@example.com',
            'customer_name' => 'Test User',
            'subtotal' => 5000,
            'fee' => 29,
            'total' => 5029,
            'status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price' => 2500,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'size' => 'S',
            'quantity' => 5,
            'price' => 2500,
        ]);

        $orderService = new OrderService();
        $orderService->markOrderPaid($order);

        $product1->refresh();
        $product2->refresh();

        $this->assertEquals(8, $product1->stock);
        $this->assertEquals(15, $product2->sizes['S']);
        $this->assertEquals(30, $product2->sizes['L']);
    }

    public function test_stock_does_not_go_below_zero(): void
    {
        Mail::fake();

        $product = Product::factory()->create([
            'stock' => 2,
            'sizes' => null,
        ]);

        $order = Order::create([
            'payment_id' => 'test_below_zero',
            'payment_provider' => 'stripe',
            'customer_email' => 'test@example.com',
            'customer_name' => 'Test User',
            'subtotal' => 2500,
            'fee' => 29,
            'total' => 2529,
            'status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'price' => 2500,
        ]);

        $orderService = new OrderService();
        $orderService->markOrderPaid($order);

        $product->refresh();
        $this->assertEquals(0, $product->stock);
    }
}
