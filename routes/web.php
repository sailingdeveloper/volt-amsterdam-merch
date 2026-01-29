<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MollieWebhookController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

// Landing page with products.
Route::get('/', [ProductController::class, 'index'])->name('products.index');

// Product detail page.
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

// Cart routes.
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/customer-info', [CartController::class, 'updateCustomerInfo'])->name('cart.customer-info');

// Checkout routes.
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');
Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');

// Language switcher.
Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// Payment webhooks (excluded from CSRF).
Route::post('/webhook/stripe', [StripeWebhookController::class, 'handle'])->name('webhook.stripe');
Route::post('/webhook/mollie', [MollieWebhookController::class, 'handle'])->name('webhook.mollie');
