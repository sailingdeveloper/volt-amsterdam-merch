<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->get('/language/en');
    }

    public function test_checkout_redirects_if_cart_is_empty(): void
    {
        $response = $this->post('/checkout');

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');
    }

    public function test_checkout_creates_order_and_redirects_to_stripe(): void
    {
        $product = Product::factory()->create([
            'price' => 2500,
            'stock' => 10,
        ]);

        // Add item to cart.
        $cartService = app(CartService::class);
        $cartService->add($product->id, 2);

        // Create a mock session object.
        $mockSession = new \stdClass();
        $mockSession->id = 'cs_test_123';
        $mockSession->url = 'https://checkout.stripe.com/test';

        $mockStripeService = Mockery::mock(StripeService::class);
        $mockStripeService->shouldReceive('createCheckoutSession')
            ->once()
            ->andReturn($mockSession);

        $this->app->instance(StripeService::class, $mockStripeService);

        $response = $this->post('/checkout');

        $response->assertRedirect('https://checkout.stripe.com/test');

        // Verify order was created.
        $this->assertDatabaseHas('orders', [
            'stripe_session_id' => 'cs_test_123',
            'subtotal' => 5000,
            'fee' => 29,
            'total' => 5029,
            'status' => 'pending',
        ]);

        // Verify order items were created.
        $order = Order::where('stripe_session_id', 'cs_test_123')->first();
        $this->assertCount(1, $order->item);
        $this->assertEquals($product->id, $order->item->first()->product_id);
        $this->assertEquals(2, $order->item->first()->quantity);
    }

    public function test_checkout_success_page_displays_order(): void
    {
        $order = Order::create([
            'stripe_session_id' => 'cs_test_456',
            'customer_email' => 'test@example.com',
            'customer_name' => 'Test User',
            'subtotal' => 5000,
            'fee' => 29,
            'total' => 5029,
            'status' => 'paid',
        ]);

        $response = $this->get('/checkout/success?session_id=cs_test_456');

        $response->assertStatus(200);
        $response->assertSee('Order Confirmed');
        $response->assertSee('50,29');
    }

    public function test_checkout_success_clears_cart(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $cartService = app(CartService::class);
        $cartService->add($product->id);

        Order::create([
            'stripe_session_id' => 'cs_test_789',
            'customer_email' => 'test@example.com',
            'customer_name' => 'Test User',
            'subtotal' => 2500,
            'fee' => 29,
            'total' => 2532,
            'status' => 'paid',
        ]);

        $this->get('/checkout/success?session_id=cs_test_789');

        $this->assertTrue($cartService->isEmpty());
    }

    public function test_checkout_success_redirects_without_session_id(): void
    {
        $response = $this->get('/checkout/success');

        $response->assertRedirect(route('products.index'));
    }

    public function test_checkout_cancel_page_displays(): void
    {
        $response = $this->get('/checkout/cancel');

        $response->assertStatus(200);
        $response->assertSee('Order Cancelled');
    }
}
