<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = new CartService();
    }

    public function test_cart_starts_empty(): void
    {
        $this->assertTrue($this->cartService->isEmpty());
        $this->assertEquals(0, $this->cartService->getCount());
    }

    public function test_can_add_item_to_cart(): void
    {
        $product = Product::factory()->create(['price' => 2500]);

        $this->cartService->add($product->id);

        $this->assertFalse($this->cartService->isEmpty());
        $this->assertEquals(1, $this->cartService->getCount());
    }

    public function test_can_add_item_with_quantity(): void
    {
        $product = Product::factory()->create(['price' => 2500]);

        $this->cartService->add($product->id, 3);

        $this->assertEquals(3, $this->cartService->getCount());
    }

    public function test_adding_same_product_increases_quantity(): void
    {
        $product = Product::factory()->create(['price' => 2500]);

        $this->cartService->add($product->id, 2);
        $this->cartService->add($product->id, 3);

        $this->assertEquals(5, $this->cartService->getCount());
    }

    public function test_can_update_item_quantity(): void
    {
        $product = Product::factory()->create(['price' => 2500]);

        $this->cartService->add($product->id, 2);
        $this->cartService->update($product->id, 5);

        $this->assertEquals(5, $this->cartService->getCount());
    }

    public function test_updating_to_zero_removes_item(): void
    {
        $product = Product::factory()->create(['price' => 2500]);

        $this->cartService->add($product->id, 2);
        $this->cartService->update($product->id, 0);

        $this->assertTrue($this->cartService->isEmpty());
    }

    public function test_can_remove_item(): void
    {
        $product = Product::factory()->create(['price' => 2500]);

        $this->cartService->add($product->id, 2);
        $this->cartService->remove($product->id);

        $this->assertTrue($this->cartService->isEmpty());
    }

    public function test_can_clear_cart(): void
    {
        $productFirst = Product::factory()->create(['price' => 2500]);
        $productSecond = Product::factory()->create(['price' => 1500]);

        $this->cartService->add($productFirst->id);
        $this->cartService->add($productSecond->id);
        $this->cartService->clear();

        $this->assertTrue($this->cartService->isEmpty());
    }

    public function test_calculates_subtotal_correctly(): void
    {
        $productFirst = Product::factory()->create(['price' => 2500]);
        $productSecond = Product::factory()->create(['price' => 1500]);

        $this->cartService->add($productFirst->id, 2);
        $this->cartService->add($productSecond->id, 1);

        // 2 * 2500 + 1 * 1500 = 6500 cents.
        $this->assertEquals(6500, $this->cartService->getSubtotal());
    }

    public function test_calculates_fee_correctly(): void
    {
        $product = Product::factory()->create(['price' => 2500]);

        $this->cartService->add($product->id);

        $this->assertEquals(32, $this->cartService->getFee());
    }

    public function test_fee_is_zero_for_empty_cart(): void
    {
        $this->assertEquals(0, $this->cartService->getFee());
    }

    public function test_calculates_total_correctly(): void
    {
        $product = Product::factory()->create(['price' => 2500]);

        $this->cartService->add($product->id, 2);

        // 2 * 2500 + 32 = 5032 cents.
        $this->assertEquals(5032, $this->cartService->getTotal());
    }

    public function test_formats_subtotal_correctly(): void
    {
        $product = Product::factory()->create(['price' => 2500]);

        $this->cartService->add($product->id);

        $this->assertEquals('25,00', $this->cartService->getFormattedSubtotal());
    }

    public function test_formats_fee_correctly(): void
    {
        $product = Product::factory()->create(['price' => 2500]);

        $this->cartService->add($product->id);

        $this->assertEquals('0,32', $this->cartService->getFormattedFee());
    }

    public function test_formats_total_correctly(): void
    {
        $product = Product::factory()->create(['price' => 2500]);

        $this->cartService->add($product->id);

        $this->assertEquals('25,32', $this->cartService->getFormattedTotal());
    }

    public function test_get_items_with_product_returns_product_details(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 2500,
        ]);

        $this->cartService->add($product->id, 2);

        $allItem = $this->cartService->getItemWithProduct();

        $this->assertCount(1, $allItem);
        $this->assertEquals('Test Product', $allItem[0]['product']->name);
        $this->assertEquals(2, $allItem[0]['quantity']);
        $this->assertEquals(5000, $allItem[0]['subtotal']);
    }

    public function test_get_items_with_product_filters_deleted_products(): void
    {
        $product = Product::factory()->create(['price' => 2500]);

        $this->cartService->add($product->id);
        $product->delete();

        $allItem = $this->cartService->getItemWithProduct();

        $this->assertCount(0, $allItem);
    }

    public function test_can_add_item_with_size(): void
    {
        $product = Product::factory()->withSizes()->create(['price' => 2500]);

        $this->cartService->add($product->id, 1, 'M');

        $allItem = $this->cartService->getItem();

        $this->assertCount(1, $allItem);
        $this->assertEquals('M', $allItem[0]['size']);
    }

    public function test_same_product_different_sizes_are_separate(): void
    {
        $product = Product::factory()->withSizes()->create(['price' => 2500]);

        $this->cartService->add($product->id, 1, 'S');
        $this->cartService->add($product->id, 1, 'M');

        $allItem = $this->cartService->getItem();

        $this->assertCount(2, $allItem);
        $this->assertEquals(2, $this->cartService->getCount());
    }

    public function test_adding_same_product_same_size_increases_quantity(): void
    {
        $product = Product::factory()->withSizes()->create(['price' => 2500]);

        $this->cartService->add($product->id, 2, 'M');
        $this->cartService->add($product->id, 3, 'M');

        $allItem = $this->cartService->getItem();

        $this->assertCount(1, $allItem);
        $this->assertEquals(5, $this->cartService->getCount());
    }

    public function test_can_update_item_with_size(): void
    {
        $product = Product::factory()->withSizes()->create(['price' => 2500]);

        $this->cartService->add($product->id, 2, 'M');
        $this->cartService->update($product->id, 5, 'M');

        $this->assertEquals(5, $this->cartService->getCount());
    }

    public function test_can_remove_item_with_size(): void
    {
        $product = Product::factory()->withSizes()->create(['price' => 2500]);

        $this->cartService->add($product->id, 2, 'S');
        $this->cartService->add($product->id, 2, 'M');
        $this->cartService->remove($product->id, 'S');

        $allItem = $this->cartService->getItem();

        $this->assertCount(1, $allItem);
        $this->assertEquals('M', $allItem[0]['size']);
    }

    public function test_get_items_with_product_includes_size(): void
    {
        $product = Product::factory()->withSizes()->create([
            'name' => 'Test Product',
            'price' => 2500,
        ]);

        $this->cartService->add($product->id, 2, 'L');

        $allItem = $this->cartService->getItemWithProduct();

        $this->assertCount(1, $allItem);
        $this->assertEquals('L', $allItem[0]['size']);
    }
}
