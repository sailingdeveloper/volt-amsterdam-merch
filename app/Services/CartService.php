<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected const SESSION_KEY = 'cart';

    /**
     * Get all items in the cart.
     *
     * @return Collection<int, array{product_id: int, quantity: int}>
     */
    public function getItem(): Collection
    {
        return collect(Session::get(self::SESSION_KEY, []));
    }

    /**
     * Add a product to the cart.
     */
    public function add(int $productId, int $quantity = 1): void
    {
        $allItem = $this->getItem()->toArray();
        $found = false;

        foreach ($allItem as &$item) {
            if ($item['product_id'] === $productId) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }

        if ($found === false) {
            $allItem[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
            ];
        }

        Session::put(self::SESSION_KEY, $allItem);
    }

    /**
     * Update the quantity of an item in the cart.
     */
    public function update(int $productId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->remove($productId);
            return;
        }

        $allItem = $this->getItem()->toArray();

        foreach ($allItem as &$item) {
            if ($item['product_id'] === $productId) {
                $item['quantity'] = $quantity;
                break;
            }
        }

        Session::put(self::SESSION_KEY, $allItem);
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(int $productId): void
    {
        $allItem = $this->getItem()->filter(function ($item) use ($productId) {
            return $item['product_id'] !== $productId;
        })->values()->toArray();

        Session::put(self::SESSION_KEY, $allItem);
    }

    /**
     * Clear all items from the cart.
     */
    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * Get the total number of items in the cart.
     */
    public function getCount(): int
    {
        return $this->getItem()->sum('quantity');
    }

    /**
     * Get cart items with product details.
     *
     * @return Collection<int, array{product: Product, quantity: int, subtotal: int}>
     */
    public function getItemWithProduct(): Collection
    {
        $allItem = $this->getItem();

        if ($allItem->isEmpty()) {
            return collect();
        }

        $productIdList = $allItem->pluck('product_id');
        $allProduct = Product::whereIn('id', $productIdList)->get()->keyBy('id');

        return $allItem->map(function ($item) use ($allProduct) {
            $product = $allProduct->get($item['product_id']);

            if ($product === null) {
                return null;
            }

            return [
                'product' => $product,
                'quantity' => $item['quantity'],
                'subtotal' => $product->price * $item['quantity'],
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
        return $this->getItem()->isEmpty();
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
}
