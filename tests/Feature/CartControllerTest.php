<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->get('/language/en');
    }

    public function test_cart_page_loads_successfully(): void
    {
        $response = $this->get('/cart');

        $response->assertStatus(200);
        $response->assertSee('Shopping Cart');
    }

    public function test_empty_cart_shows_empty_message(): void
    {
        $response = $this->get('/cart');

        $response->assertStatus(200);
        $response->assertSee('Your cart is empty');
    }

    public function test_can_add_product_to_cart(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $response = $this->post('/cart/add', [
            'product_id' => $product->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->get('/cart')->assertSee($product->name);
    }

    public function test_can_add_product_with_quantity(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $response = $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_cannot_add_out_of_stock_product(): void
    {
        $product = Product::factory()->outOfStock()->create();

        $response = $this->post('/cart/add', [
            'product_id' => $product->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_cannot_add_nonexistent_product(): void
    {
        $response = $this->post('/cart/add', [
            'product_id' => 99999,
        ]);

        $response->assertSessionHasErrors('product_id');
    }

    public function test_can_update_cart_quantity(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->post('/cart/add', ['product_id' => $product->id]);

        $response = $this->post('/cart/update', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_can_remove_item_from_cart(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->post('/cart/add', ['product_id' => $product->id]);

        $response = $this->post('/cart/remove', [
            'product_id' => $product->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->get('/cart')->assertSee('Your cart is empty');
    }

    public function test_cart_shows_subtotal_and_fee(): void
    {
        $product = Product::factory()->create([
            'price' => 2500,
            'stock' => 10,
        ]);

        $this->post('/cart/add', ['product_id' => $product->id]);

        $response = $this->get('/cart');

        $response->assertSee('25,00');
        $response->assertSee('0,29');
        $response->assertSee('25,29');
    }

    public function test_cart_displays_in_dutch(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->get('/language/nl');
        $this->post('/cart/add', ['product_id' => $product->id]);

        $response = $this->get('/cart');

        $response->assertSee('Winkelwagen');
        $response->assertSee('Subtotaal');
        $response->assertSee('Transactiekosten');
    }

    public function test_can_add_sized_product_to_cart(): void
    {
        $product = Product::factory()->withSizes()->create();

        $response = $this->post('/cart/add', [
            'product_id' => $product->id,
            'size' => 'M',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->get('/cart')
            ->assertSee($product->name)
            ->assertSee('Size: M');
    }

    public function test_cannot_add_sized_product_without_size(): void
    {
        $product = Product::factory()->withSizes()->create();

        $response = $this->post('/cart/add', [
            'product_id' => $product->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_cannot_add_out_of_stock_size(): void
    {
        $product = Product::factory()->withSizesOutOfStock()->create();

        $response = $this->post('/cart/add', [
            'product_id' => $product->id,
            'size' => 'M',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_same_product_different_sizes_are_separate_cart_items(): void
    {
        $product = Product::factory()->withSizes()->create();

        $this->post('/cart/add', ['product_id' => $product->id, 'size' => 'S']);
        $this->post('/cart/add', ['product_id' => $product->id, 'size' => 'M']);

        $response = $this->get('/cart');

        $response->assertSee('Size: S');
        $response->assertSee('Size: M');
    }

    public function test_can_update_customer_email(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        $this->post('/cart/add', ['product_id' => $product->id]);

        $response = $this->postJson('/cart/customer-info', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('carts', [
            'customer_email' => 'test@example.com',
        ]);
    }

    public function test_can_update_customer_name(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        $this->post('/cart/add', ['product_id' => $product->id]);

        $response = $this->postJson('/cart/customer-info', [
            'name' => 'John Doe',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('carts', [
            'customer_name' => 'John Doe',
        ]);
    }

    public function test_can_update_customer_phone(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        $this->post('/cart/add', ['product_id' => $product->id]);

        $response = $this->postJson('/cart/customer-info', [
            'phone' => '+31612345678',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('carts', [
            'customer_phone' => '+31612345678',
        ]);
    }

    public function test_customer_info_validates_email(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        $this->post('/cart/add', ['product_id' => $product->id]);

        $response = $this->postJson('/cart/customer-info', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_customer_info_creates_cart_if_not_exists(): void
    {
        $response = $this->postJson('/cart/customer-info', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('carts', [
            'customer_email' => 'test@example.com',
            'status' => 'active',
        ]);
    }
}
