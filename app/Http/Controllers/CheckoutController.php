<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentServiceInterface;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\StripeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected PaymentServiceInterface $paymentService,
        protected StripeService $stripeService,
        protected OrderService $orderService
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

        // Create the order with customer information and payment provider.
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
            'payment_provider' => $this->paymentService->getProvider(),
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

        // Create payment using the configured provider.
        $paymentResult = $this->paymentService->createPayment($order);

        // Update order with payment ID.
        $updateData = ['payment_id' => $paymentResult->paymentId];

        // Also store in Stripe-specific field for backward compatibility.
        if ($this->paymentService->getProvider() === 'stripe') {
            $updateData['stripe_payment_intent_id'] = $paymentResult->paymentId;
        }

        $order->update($updateData);

        // Mark cart as converted and link to order.
        $this->cartService->markConverted($order);

        // Handle redirect flow (Mollie) vs client-side flow (Stripe).
        if ($paymentResult->requiresRedirect()) {
            return redirect()->away($paymentResult->redirectUrl);
        }

        return view('checkout.payment', [
            'order' => $order,
            'clientSecret' => $paymentResult->clientSecret,
            'customerName' => $validated['name'],
            'stripeKey' => config('services.stripe.key'),
            'returnUrl' => route('checkout.success'),
        ]);
    }

    /**
     * Handle return from payment.
     */
    public function success(Request $request): View|RedirectResponse
    {
        // Mollie payment flow (uses order_id in redirect).
        $mollieOrderId = $request->query('order_id');
        if ($mollieOrderId !== null) {
            return $this->handleMollieSuccess($mollieOrderId);
        }

        // Stripe Payment Intent flow.
        $paymentIntentId = $request->query('payment_intent');
        $redirectStatus = $request->query('redirect_status');

        if ($paymentIntentId !== null) {
            return $this->handleStripeSuccess($paymentIntentId, $redirectStatus);
        }

        // Legacy support for Stripe Checkout Sessions.
        $sessionId = $request->query('session_id');
        if ($sessionId !== null) {
            return $this->handleStripeSessionSuccess($sessionId);
        }

        return redirect()->route('products.index');
    }

    /**
     * Handle Mollie payment success.
     */
    protected function handleMollieSuccess(string $orderId): View|RedirectResponse
    {
        $order = Order::find($orderId);

        if ($order === null || $order->payment_provider !== 'mollie') {
            return redirect()->route('products.index');
        }

        // Check payment status with Mollie if order is still pending.
        if ($order->status === 'pending' && $order->payment_id !== null) {
            $payment = $this->paymentService->getPayment($order->payment_id);

            if ($payment->isPaid()) {
                $this->orderService->markOrderPaid($order);
                $order->refresh();
            }
        }

        if ($order->status !== 'paid') {
            return redirect()->route('checkout.cancel')
                ->with('error', __('shop.payment_failed'));
        }

        return view('checkout.success', compact('order'));
    }

    /**
     * Handle Stripe Payment Intent success.
     */
    protected function handleStripeSuccess(string $paymentIntentId, ?string $redirectStatus): View|RedirectResponse
    {
        $order = Order::where('stripe_payment_intent_id', $paymentIntentId)
            ->orWhere('payment_id', $paymentIntentId)
            ->first();

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
     * Handle legacy Stripe Checkout Session success.
     */
    protected function handleStripeSessionSuccess(string $sessionId): View|RedirectResponse
    {
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

    /**
     * Handle cancelled/failed checkout.
     */
    public function cancel(): View
    {
        return view('checkout.cancel');
    }
}
