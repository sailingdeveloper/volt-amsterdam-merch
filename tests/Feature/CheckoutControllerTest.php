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

    public function test_checkout_page_redirects_if_cart_is_empty(): void
    {
        $response = $this->get('/checkout');

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');
    }

    public function test_checkout_page_displays_with_items(): void
    {
        $product = Product::factory()->create([
            'price' => 2500,
            'stock' => 10,
        ]);

        $cartService = app(CartService::class);
        $cartService->add($product->id, 2);

        $response = $this->get('/checkout');

        $response->assertStatus(200);
        $response->assertSee('Checkout');
        $response->assertSee('Contact Information');
        $response->assertSee('Billing Address');
        $response->assertSee('Order Summary');
        $response->assertSee('50,00');
    }

    public function test_checkout_page_shows_saved_customer_info(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $cartService = app(CartService::class);
        $cartService->add($product->id);
        $cartService->updateCustomerInfo([
            'email' => 'test@example.com',
            'name' => 'Test User',
            'phone' => '+31612345678',
        ]);

        $response = $this->get('/checkout');

        $response->assertStatus(200);
        $response->assertSee('test@example.com');
        $response->assertSee('Test User');
        $response->assertSee('+31612345678');
    }

    public function test_checkout_post_redirects_if_cart_is_empty(): void
    {
        $response = $this->post('/checkout', [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'phone' => '+31612345678',
            'billing_address_line1' => 'Test Street 1',
            'billing_postal_code' => '1234AB',
            'billing_city' => 'Amsterdam',
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');
    }

    public function test_checkout_validates_required_fields(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        $cartService = app(CartService::class);
        $cartService->add($product->id);

        $response = $this->post('/checkout', []);

        $response->assertSessionHasErrors(['email', 'name', 'billing_address_line1', 'billing_postal_code', 'billing_city']);
    }

    public function test_checkout_creates_order_and_shows_payment_page(): void
    {
        $product = Product::factory()->create([
            'price' => 2500,
            'stock' => 10,
        ]);

        // Add item to cart.
        $cartService = app(CartService::class);
        $cartService->add($product->id, 2);

        // Create a mock payment intent object.
        $mockPaymentIntent = new \stdClass();
        $mockPaymentIntent->id = 'pi_test_123';
        $mockPaymentIntent->client_secret = 'pi_test_123_secret_abc';

        $mockStripeService = Mockery::mock(StripeService::class);
        $mockStripeService->shouldReceive('createPaymentIntent')
            ->once()
            ->andReturn($mockPaymentIntent);

        $this->app->instance(StripeService::class, $mockStripeService);

        $response = $this->post('/checkout', [
            'email' => 'customer@example.com',
            'name' => 'John Doe',
            'phone' => '+31612345678',
            'billing_address_line1' => 'Main Street 123',
            'billing_postal_code' => '1234AB',
            'billing_city' => 'Amsterdam',
        ]);

        $response->assertStatus(200);
        $response->assertSee('Payment');
        $response->assertSee('Select your bank');
        $response->assertSee('pi_test_123_secret_abc');

        // Verify order was created with customer information.
        $this->assertDatabaseHas('orders', [
            'stripe_payment_intent_id' => 'pi_test_123',
            'customer_email' => 'customer@example.com',
            'customer_name' => 'John Doe',
            'customer_phone' => '+31612345678',
            'billing_address_line1' => 'Main Street 123',
            'billing_postal_code' => '1234AB',
            'billing_city' => 'Amsterdam',
            'subtotal' => 5000,
            'fee' => 29,
            'total' => 5029,
            'status' => 'pending',
        ]);

        // Verify order items were created.
        $order = Order::where('stripe_payment_intent_id', 'pi_test_123')->first();
        $this->assertCount(1, $order->item);
        $this->assertEquals($product->id, $order->item->first()->product_id);
        $this->assertEquals(2, $order->item->first()->quantity);
    }

    public function test_checkout_success_page_displays_order(): void
    {
        $order = Order::create([
            'stripe_payment_intent_id' => 'pi_test_456',
            'customer_email' => 'test@example.com',
            'customer_name' => 'Test User',
            'subtotal' => 5000,
            'fee' => 29,
            'total' => 5029,
            'status' => 'paid',
        ]);

        $response = $this->get('/checkout/success?payment_intent=pi_test_456&redirect_status=succeeded');

        $response->assertStatus(200);
        $response->assertSee('Order Confirmed');
        $response->assertSee('50,29');
    }

    public function test_checkout_marks_cart_as_converted(): void
    {
        $product = Product::factory()->create([
            'price' => 2500,
            'stock' => 10,
        ]);

        // Add item to cart.
        $this->post('/cart/add', ['product_id' => $product->id]);

        // Mock Stripe service.
        $mockPaymentIntent = new \stdClass();
        $mockPaymentIntent->id = 'pi_test_converted';
        $mockPaymentIntent->client_secret = 'pi_test_converted_secret';

        $mockStripeService = Mockery::mock(StripeService::class);
        $mockStripeService->shouldReceive('createPaymentIntent')
            ->once()
            ->andReturn($mockPaymentIntent);

        $this->app->instance(StripeService::class, $mockStripeService);

        // Go through checkout.
        $this->post('/checkout', [
            'email' => 'customer@example.com',
            'name' => 'John Doe',
            'phone' => '+31612345678',
            'billing_address_line1' => 'Main Street 123',
            'billing_postal_code' => '1234AB',
            'billing_city' => 'Amsterdam',
        ]);

        // Verify cart is marked as converted and linked to order.
        $order = Order::where('stripe_payment_intent_id', 'pi_test_converted')->first();
        $this->assertNotNull($order);
        $this->assertNotNull($order->cart);
        $this->assertEquals('converted', $order->cart->status);
    }

    public function test_checkout_success_redirects_without_payment_intent(): void
    {
        $response = $this->get('/checkout/success');

        $response->assertRedirect(route('products.index'));
    }

    public function test_checkout_success_redirects_to_cancel_on_failed_payment(): void
    {
        $order = Order::create([
            'stripe_payment_intent_id' => 'pi_test_failed',
            'customer_email' => 'test@example.com',
            'customer_name' => 'Test User',
            'subtotal' => 5000,
            'fee' => 29,
            'total' => 5029,
            'status' => 'pending',
        ]);

        $response = $this->get('/checkout/success?payment_intent=pi_test_failed&redirect_status=failed');

        $response->assertRedirect(route('checkout.cancel'));
    }

    public function test_checkout_cancel_page_displays(): void
    {
        $response = $this->get('/checkout/cancel');

        $response->assertStatus(200);
        $response->assertSee('Order Cancelled');
    }
}
