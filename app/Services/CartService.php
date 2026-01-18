<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected const SESSION_CART_ID_KEY = 'cart_id';

    protected ?Cart $cart = null;

    /**
     * Get or create the cart for the current session.
     */
    public function getCart(): Cart
    {
        if ($this->cart !== null) {
            return $this->cart;
        }

        $cartId = Session::get(self::SESSION_CART_ID_KEY);

        if ($cartId !== null) {
            $this->cart = Cart::where('id', $cartId)
                ->where('status', 'active')
                ->first();

            if ($this->cart !== null) {
                return $this->cart;
            }
        }

        // Create new cart and store ID in session.
        $this->cart = Cart::create(['status' => 'active']);
        Session::put(self::SESSION_CART_ID_KEY, $this->cart->id);

        return $this->cart;
    }

    /**
     * Get all items in the cart.
     *
     * @return Collection<int, array{product_id: int, quantity: int, size: ?string}>
     */
    public function getItem(): Collection
    {
        $cart = $this->getCart();

        return $cart->items->map(function (CartItem $item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'size' => $item->size,
            ];
        });
    }

    /**
     * Add a product to the cart.
     */
    public function add(int $productId, int $quantity = 1, ?string $size = null): void
    {
        $cart = $this->getCart();

        $item = $cart->items()
            ->where('product_id', $productId)
            ->where('size', $size)
            ->first();

        if ($item !== null) {
            $item->increment('quantity', $quantity);
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'size' => $size,
            ]);
        }

        $cart->touch();
    }

    /**
     * Update the quantity of an item in the cart.
     */
    public function update(int $productId, int $quantity, ?string $size = null): void
    {
        if ($quantity <= 0) {
            $this->remove($productId, $size);

            return;
        }

        $cart = $this->getCart();

        $cart->items()
            ->where('product_id', $productId)
            ->where('size', $size)
            ->update(['quantity' => $quantity]);

        $cart->touch();
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(int $productId, ?string $size = null): void
    {
        $cart = $this->getCart();

        $cart->items()
            ->where('product_id', $productId)
            ->where('size', $size)
            ->delete();

        $cart->touch();
    }

    /**
     * Clear all items from the cart.
     */
    public function clear(): void
    {
        $cart = $this->getCart();
        $cart->items()->delete();
        $cart->touch();
    }

    /**
     * Get the total number of items in the cart.
     */
    public function getCount(): int
    {
        return $this->getCart()->items()->sum('quantity');
    }

    /**
     * Get cart items with product details.
     *
     * @return Collection<int, array{product: Product, quantity: int, size: ?string, subtotal: int}>
     */
    public function getItemWithProduct(): Collection
    {
        $cart = $this->getCart();
        $items = $cart->items()->with('product')->get();

        return $items->map(function (CartItem $item) {
            if ($item->product === null) {
                return null;
            }

            return [
                'product' => $item->product,
                'quantity' => $item->quantity,
                'size' => $item->size,
                'subtotal' => $item->product->price * $item->quantity,
            ];
        })->filter()->values();
    }

    /**
     * Get the subtotal in cents (before fees).
     */
    public function getSubtotal(): int
    {
        return $this->getItemWithProduct()->sum('subtotal');
    }

    /**
     * Get the processing fee in cents.
     */
    public function getFee(): int
    {
        if ($this->isEmpty()) {
            return 0;
        }

        return config('stripe.ideal_fee_cents', 32);
    }

    /**
     * Get the total in cents (including fees).
     */
    public function getTotal(): int
    {
        return $this->getSubtotal() + $this->getFee();
    }

    /**
     * Check if the cart is empty.
     */
    public function isEmpty(): bool
    {
        return $this->getCount() === 0;
    }

    /**
     * Get formatted subtotal.
     */
    public function getFormattedSubtotal(): string
    {
        return number_format($this->getSubtotal() / 100, 2, ',', '.');
    }

    /**
     * Get formatted fee.
     */
    public function getFormattedFee(): string
    {
        return number_format($this->getFee() / 100, 2, ',', '.');
    }

    /**
     * Get formatted total.
     */
    public function getFormattedTotal(): string
    {
        return number_format($this->getTotal() / 100, 2, ',', '.');
    }

    /**
     * Mark the cart as converted and link it to the order.
     */
    public function markConverted(Order $order): void
    {
        $cart = $this->getCart();
        $cart->update([
            'status' => 'converted',
            'order_id' => $order->id,
        ]);

        // Clear the cart reference so a new cart is created for future purchases.
        Session::forget(self::SESSION_CART_ID_KEY);
        $this->cart = null;
    }
}
