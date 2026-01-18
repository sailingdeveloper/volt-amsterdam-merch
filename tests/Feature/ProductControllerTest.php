<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_displays_products(): void
    {
        $productFirst = Product::factory()->create(['name' => 'Test Product One']);
        $productSecond = Product::factory()->create(['name' => 'Test Product Two']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Test Product One');
        $response->assertSee('Test Product Two');
    }

    public function test_landing_page_does_not_show_inactive_products(): void
    {
        $activeProduct = Product::factory()->create(['name' => 'Active Product']);
        $inactiveProduct = Product::factory()->inactive()->create(['name' => 'Inactive Product']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Active Product');
        $response->assertDontSee('Inactive Product');
    }

    public function test_product_detail_page_displays_product(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'This is a test description.',
            'price' => 2500,
        ]);

        $response = $this->get('/products/test-product');

        $response->assertStatus(200);
        $response->assertSee('Test Product');
        $response->assertSee('This is a test description.');
        $response->assertSee('25,00');
    }

    public function test_product_detail_page_returns_404_for_nonexistent_product(): void
    {
        $response = $this->get('/products/nonexistent');

        $response->assertStatus(404);
    }

    public function test_product_detail_page_returns_404_for_inactive_product(): void
    {
        Product::factory()->inactive()->create(['slug' => 'inactive-product']);

        $response = $this->get('/products/inactive-product');

        $response->assertStatus(404);
    }

    public function test_product_shows_out_of_stock_status(): void
    {
        Product::factory()->outOfStock()->create([
            'name' => 'Out of Stock Product',
            'slug' => 'out-of-stock',
        ]);

        $response = $this->get('/products/out-of-stock');

        $response->assertStatus(200);
        $response->assertSee('Out of stock');
    }

    public function test_landing_page_displays_in_correct_language(): void
    {
        Product::factory()->create([
            'name' => 'English Name',
            'name_nl' => 'Dutch Name',
        ]);

        // Test English (default).
        $responseEnglish = $this->get('/');
        $responseEnglish->assertSee('Our Products');

        // Test Dutch.
        $this->get('/language/nl');
        $responseDutch = $this->get('/');
        $responseDutch->assertSee('Onze Producten');
    }
}
