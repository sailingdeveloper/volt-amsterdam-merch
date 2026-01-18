<x-layouts.app
    :title="__('shop.order_cancelled')"
    :noindex="true"
>
    <div class="py-16">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 text-center">
            <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-full bg-yellow-100 mb-6">
                <svg class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ __('shop.order_cancelled') }}</h1>
            <p class="text-lg text-gray-600 mb-8">{{ __('shop.order_cancelled_message') }}</p>

            <div class="flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                <a href="{{ route('cart.index') }}"
                   class="inline-block bg-volt-purple hover:bg-volt-purple-dark text-white font-medium py-3 px-8 rounded-lg transition-colors">
                    {{ __('shop.return_to_cart') }}
                </a>
                <a href="{{ route('products.index') }}"
                   class="inline-block text-volt-purple hover:text-volt-purple-dark font-medium">
                    {{ __('shop.continue_shopping') }}
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
