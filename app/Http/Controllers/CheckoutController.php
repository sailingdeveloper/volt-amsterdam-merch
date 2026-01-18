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

        $itemsWithProduct = $this->cartService->getItemWithProduct();

        // Prepare line items for Stripe.
        $lineItems = $itemsWithProduct->map(function ($item) {
            $name = $item['product']->localized_name;
            if ($item['size']) {
                $name .= ' (' . $item['size'] . ')';
            }

            return [
                'name' => $name,
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
            'locale' => app()->getLocale(),
        ]);

        // Create order items.
        foreach ($itemsWithProduct as $item) {
            $order->item()->create([
                'product_id' => $item['product']->id,
                'size' => $item['size'],
                'quantity' => $item['quantity'],
                'price' => $item['product']->price,
            ]);
        }

        // Create Stripe Checkout session.
        // Note: We append the session_id placeholder manually because Laravel's route() helper URL-encodes it.
        $session = $this->stripeService->createCheckoutSession(
            $lineItems,
            $this->cartService->getFee(),
            route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
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

        // Process payment completion if not already processed by webhook.
        if ($order->status === 'pending') {
            $session = $this->stripeService->getSession($sessionId);
            $this->stripeService->handleCheckoutCompleted($session);
            $order->refresh();
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
