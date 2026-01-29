<x-layouts.app
    :title="__('shop.cart')"
    :noindex="true"
>
    @if($isEmpty)
        <div class="flex-1 flex items-center justify-center px-4">
            <div class="text-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mx-auto h-20 w-20 text-gray-300">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                </svg>
                <h1 class="mt-6 text-2xl font-bold text-gray-900">{{ __('shop.cart_empty') }}</h1>
                <p class="mt-2 text-gray-500 max-w-sm">{{ __('shop.cart_empty_message') }}</p>
                <a href="{{ route('products.index') }}"
                   class="mt-8 inline-block bg-volt-purple hover:bg-volt-purple-dark text-white font-medium py-3 px-8 rounded-lg transition-colors">
                    {{ __('shop.continue_shopping') }}
                </a>
            </div>
        </div>
    @else
    <div class="py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('shop.cart') }}</h1>
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    {{-- Cart Items --}}
                    <ul class="divide-y divide-gray-200">
                        @foreach($items as $item)
                            <li class="p-6">
                                <div class="flex items-start space-x-4">
                                    {{-- Product Image --}}
                                    <div class="h-20 w-20 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100">
                                        @if($item['product']->image)
                                            <img src="{{ Storage::url($item['product']->image) }}"
                                                 alt="{{ $item['product']->localized_name }}"
                                                 class="h-full w-full object-cover">
                                        @else
                                            <div class="h-full w-full flex items-center justify-center bg-gradient-to-br from-volt-purple/10 to-volt-purple/20">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-volt-purple/40">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Product Info --}}
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-medium text-gray-900">
                                            <a href="{{ route('products.show', $item['product']->slug) }}" class="hover:text-volt-purple">
                                                {{ $item['product']->localized_name }}
                                            </a>
                                        </h3>
                                        @if($item['size'])
                                            <p class="text-sm text-gray-500">
                                                {{ __('shop.size') }}: {{ $item['size'] }}
                                            </p>
                                        @endif
                                        <p class="mt-1 text-sm text-gray-500">
                                            &euro;{{ $item['product']->formatted_price }} {{ __('shop.each') }}
                                        </p>

                                        <div class="mt-3 flex items-center space-x-4">
                                            {{-- Quantity Selector --}}
                                            <form action="{{ route('cart.update') }}" method="POST" class="flex items-center space-x-2">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $item['product']->id }}">
                                                <input type="hidden" name="size" value="{{ $item['size'] }}">
                                                <select name="quantity"
                                                        onchange="this.form.submit()"
                                                        class="rounded-md border-gray-300 text-sm focus:border-volt-purple focus:ring-volt-purple">
                                                    @for($i = 1; $i <= 10; $i++)
                                                        <option value="{{ $i }}" {{ $item['quantity'] === $i ? 'selected' : '' }}>
                                                            {{ $i }}
                                                        </option>
                                                    @endfor
                                                </select>
                                            </form>

                                            {{-- Remove Button --}}
                                            <form action="{{ route('cart.remove') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $item['product']->id }}">
                                                <input type="hidden" name="size" value="{{ $item['size'] }}">
                                                <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                                                    {{ __('shop.remove') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    {{-- Item Total --}}
                                    <div class="text-right">
                                        <p class="font-medium text-gray-900">
                                            &euro;{{ number_format($item['subtotal'] / 100, 2, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Order Summary --}}
                    <div class="bg-gray-50 p-6 space-y-3">
                        <div class="flex justify-between text-gray-600">
                            <span>{{ __('shop.subtotal') }}</span>
                            <span>&euro;{{ $subtotal }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>{{ __('shop.processing_fee') }} (iDEAL | Wero)</span>
                            <span>&euro;{{ $fee }}</span>
                        </div>
                        <div class="border-t border-gray-200 pt-3 flex justify-between text-lg font-semibold text-gray-900">
                            <span>{{ __('shop.total') }}</span>
                            <span>&euro;{{ $total }}</span>
                        </div>
                    </div>

                    {{-- Checkout Button --}}
                    <div class="p-6 bg-white border-t border-gray-200">
                        <a href="{{ route('checkout.index') }}"
                           class="w-full bg-volt-purple hover:bg-volt-purple-dark text-white font-semibold py-3 px-6 rounded-lg transition-colors flex items-center justify-center space-x-2">
                            <span>{{ __('shop.continue') }}</span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </a>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <a href="{{ route('products.index') }}" class="text-volt-purple hover:text-volt-purple-dark font-medium">
                        &larr; {{ __('shop.continue_shopping') }}
                    </a>
                </div>

                {{-- Upsell Products --}}
                @if($upsellProducts->isNotEmpty())
                    <div class="mt-12">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('shop.you_might_also_like') }}</h2>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach($upsellProducts as $product)
                                <x-product-card :product="$product" :compact="true" />
                            @endforeach
                        </div>
                    </div>
                @endif
        </div>
    </div>
    @endif
</x-layouts.app>
