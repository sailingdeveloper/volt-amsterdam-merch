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
     * Display the checkout page.
     */
    public function index(): View|RedirectResponse
    {
        if ($this->cartService->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', __('shop.cart_empty'));
        }

        $items = $this->cartService->getItemWithProduct();
        $subtotal = $this->cartService->getFormattedSubtotal();
        $fee = $this->cartService->getFormattedFee();
        $total = $this->cartService->getFormattedTotal();
        $customerInfo = $this->cartService->getCustomerInfo();

        return view('checkout.index', compact('items', 'subtotal', 'fee', 'total', 'customerInfo'));
    }

    /**
     * Process checkout form and show payment page.
     */
    public function checkout(Request $request): View|RedirectResponse
    {
        if ($this->cartService->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', __('shop.cart_empty'));
        }

        $validated = $request->validate([
            'email' => 'required|email:rfc,dns|max:255',
            'phone' => 'nullable|string|min:6|max:50',
            'name' => 'required|string|min:3|max:255',
            'billing_address_line1' => 'required|string|min:3|max:255',
            'billing_postal_code' => 'required|string|min:4|max:20',
            'billing_city' => 'required|string|min:2|max:100',
        ]);

        $itemsWithProduct = $this->cartService->getItemWithProduct();

        // Create the order with customer information.
        $order = Order::create([
            'customer_email' => $validated['email'],
            'customer_name' => $validated['name'],
            'customer_phone' => $validated['phone'] ?? null,
            'billing_address_line1' => $validated['billing_address_line1'],
            'billing_postal_code' => $validated['billing_postal_code'],
            'billing_city' => $validated['billing_city'],
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

        // Create Payment Intent.
        $paymentIntent = $this->stripeService->createPaymentIntent(
            $order->total,
            (string) $order->id,
            $validated['email'],
            $validated['name'],
        );

        // Update order with Payment Intent ID.
        $order->update(['stripe_payment_intent_id' => $paymentIntent->id]);

        // Mark cart as converted and link to order.
        $this->cartService->markConverted($order);

        return view('checkout.payment', [
            'order' => $order,
            'clientSecret' => $paymentIntent->client_secret,
            'customerName' => $validated['name'],
            'stripeKey' => config('stripe.key'),
            'returnUrl' => route('checkout.success'),
        ]);
    }

    /**
     * Handle return from payment.
     */
    public function success(Request $request): View|RedirectResponse
    {
        $paymentIntentId = $request->query('payment_intent');
        $redirectStatus = $request->query('redirect_status');

        // Legacy support for Checkout Sessions.
        $sessionId = $request->query('session_id');
        if ($sessionId !== null) {
            $order = Order::where('stripe_session_id', $sessionId)->first();

            if ($order === null) {
                return redirect()->route('products.index');
            }

            if ($order->status === 'pending') {
                $session = $this->stripeService->getSession($sessionId);
                $this->stripeService->handleCheckoutCompleted($session);
                $order->refresh();
            }

            return view('checkout.success', compact('order'));
        }

        // Payment Intent flow.
        if ($paymentIntentId === null) {
            return redirect()->route('products.index');
        }

        $order = Order::where('stripe_payment_intent_id', $paymentIntentId)->first();

        if ($order === null) {
            return redirect()->route('products.index');
        }

        // Check payment status.
        if ($redirectStatus === 'succeeded' && $order->status === 'pending') {
            $paymentIntent = $this->stripeService->getPaymentIntent($paymentIntentId);
            $this->stripeService->handlePaymentIntentSucceeded($paymentIntent);
            $order->refresh();
        }

        if ($order->status !== 'paid') {
            return redirect()->route('checkout.cancel')
                ->with('error', __('shop.payment_failed'));
        }

        return view('checkout.success', compact('order'));
    }

    /**
     * Handle cancelled/failed checkout.
     */
    public function cancel(): View
    {
        return view('checkout.cancel');
    }
}
