<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected StripeService $stripeService
    ) {}

    /**
     * Create a Stripe Checkout session and redirect.
     */
    public function checkout(Request $request): RedirectResponse
    {
        if ($this->cartService->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', __('shop.cart_empty'));
        }

        $allItemWithProduct = $this->cartService->getItemWithProduct();

        // Prepare line items for Stripe.
        $allLineItem = $allItemWithProduct->map(function ($item) {
            return [
                'name' => $item['product']->localized_name,
                'price' => $item['product']->price,
                'quantity' => $item['quantity'],
            ];
        })->toArray();

        // Create the order.
        $order = Order::create([
            'customer_email' => 'pending@example.com',
            'customer_name' => 'Pending',
            'subtotal' => $this->cartService->getSubtotal(),
            'fee' => $this->cartService->getFee(),
            'total' => $this->cartService->getTotal(),
            'status' => 'pending',
        ]);

        // Create order items.
        foreach ($allItemWithProduct as $item) {
            $order->item()->create([
                'product_id' => $item['product']->id,
                'quantity' => $item['quantity'],
                'price' => $item['product']->price,
            ]);
        }

        // Create Stripe Checkout session.
        $session = $this->stripeService->createCheckoutSession(
            $allLineItem,
            $this->cartService->getFee(),
            route('checkout.success', ['session_id' => '{CHECKOUT_SESSION_ID}']),
            route('checkout.cancel'),
        );

        // Update order with Stripe session ID.
        $order->update(['stripe_session_id' => $session->id]);

        return redirect($session->url);
    }

    /**
     * Handle successful checkout.
     */
    public function success(Request $request): View|RedirectResponse
    {
        $sessionId = $request->query('session_id');

        if ($sessionId === null) {
            return redirect()->route('products.index');
        }

        $order = Order::where('stripe_session_id', $sessionId)->first();

        if ($order === null) {
            return redirect()->route('products.index');
        }

        // Clear the cart after successful checkout.
        $this->cartService->clear();

        return view('checkout.success', compact('order'));
    }

    /**
     * Handle cancelled checkout.
     */
    public function cancel(): View
    {
        return view('checkout.cancel');
    }
}
