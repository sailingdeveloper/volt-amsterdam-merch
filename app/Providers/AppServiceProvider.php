<?php

namespace App\Providers;

use App\Contracts\PaymentServiceInterface;
use App\Services\Payment\MolliePaymentService;
use App\Services\Payment\StripePaymentService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentServiceInterface::class, function ($app) {
            return match (config('services.payment.provider')) {
                'mollie' => $app->make(MolliePaymentService::class),
                default => $app->make(StripePaymentService::class),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
