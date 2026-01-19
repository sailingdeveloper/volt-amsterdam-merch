<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Display the cart.
     */
    public function index(): View
    {
        $items = $this->cartService->getItemWithProduct();
        $subtotal = $this->cartService->getFormattedSubtotal();
        $fee = $this->cartService->getFormattedFee();
        $total = $this->cartService->getFormattedTotal();
        $isEmpty = $this->cartService->isEmpty();

        return view('cart.index', compact('items', 'subtotal', 'fee', 'total', 'isEmpty'));
    }

    /**
     * Add an item to the cart.
     */
    public function add(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'integer|min:1|max:10',
            'size' => 'nullable|string|max:10',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $size = $validated['size'] ?? null;

        // If product has sizes, size is required.
        if ($product->hasSizes() && $size === null) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('shop.size_required'),
                ], 400);
            }

            return back()->with('error', __('shop.size_required'));
        }

        // Check stock for the specific size.
        $isInStock = $product->hasSizes()
            ? $product->isSizeInStock($size)
            : $product->isInStock();

        if ($isInStock === false) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('shop.out_of_stock'),
                ], 400);
            }

            return back()->with('error', __('shop.out_of_stock'));
        }

        $this->cartService->add(
            $validated['product_id'],
            $validated['quantity'] ?? 1,
            $size
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('shop.added_to_cart'),
                'cart_count' => $this->cartService->getCount(),
                'cart_url' => route('cart.index'),
                'cart_link_text' => __('shop.view_cart'),
            ]);
        }

        return back()->with('success', __('shop.added_to_cart'))
            ->with('cart_link', true);
    }

    /**
     * Update the quantity of an item in the cart.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:0|max:10',
            'size' => 'nullable|string|max:10',
        ]);

        $this->cartService->update(
            $validated['product_id'],
            $validated['quantity'],
            $validated['size'] ?? null
        );

        return back()->with('success', __('shop.cart_updated'));
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'size' => 'nullable|string|max:10',
        ]);

        $this->cartService->remove(
            $validated['product_id'],
            $validated['size'] ?? null
        );

        return back()->with('success', __('shop.item_removed'));
    }

    /**
     * Update customer information on the cart.
     */
    public function updateCustomerInfo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'nullable|email|max:255',
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $this->cartService->updateCustomerInfo($validated);

        return response()->json(['success' => true]);
    }
}
