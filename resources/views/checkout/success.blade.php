<x-layouts.app
    :title="__('shop.order_confirmed')"
    :noindex="true"
>
    <div class="py-16">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 text-center">
            <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-full bg-green-100 mb-6">
                <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ __('shop.order_confirmed') }}</h1>
            <p class="text-lg text-gray-600 mb-4">{{ __('shop.order_confirmed_message') }}</p>
            <p class="text-volt-purple mb-8">{{ __('shop.email_pickup_info') }}</p>

            <div class="bg-white rounded-xl shadow-sm p-6 text-left mb-8">
                <h2 class="font-semibold text-gray-900 mb-4">{{ __('shop.order_details') }}</h2>

                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">{{ __('shop.order_number') }}</dt>
                        <dd class="font-medium text-gray-900">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">{{ __('shop.subtotal') }}</dt>
                        <dd class="text-gray-900">&euro;{{ $order->formatted_subtotal }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">{{ __('shop.processing_fee') }}</dt>
                        <dd class="text-gray-900">&euro;{{ $order->formatted_fee }}</dd>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-3">
                        <dt class="font-semibold text-gray-900">{{ __('shop.total') }}</dt>
                        <dd class="font-semibold text-gray-900">&euro;{{ $order->formatted_total }}</dd>
                    </div>
                </dl>
            </div>

            <a href="{{ route('products.index') }}"
               class="inline-block bg-volt-purple hover:bg-volt-purple-dark text-white font-medium py-3 px-8 rounded-lg transition-colors">
                {{ __('shop.continue_shopping') }}
            </a>
        </div>
    </div>
</x-layouts.app>
