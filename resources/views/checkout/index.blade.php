<x-layouts.app
    :title="__('shop.checkout')"
    :noindex="true"
>
    <div class="py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('shop.checkout') }}</h1>

            <form action="{{ route('checkout') }}" method="POST" id="checkout-form">
                @csrf

                {{-- Customer Information --}}
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('shop.contact_information') }}</h2>

                        <div class="space-y-4">
                            {{-- Email --}}
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('shop.email') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="email"
                                       id="email"
                                       name="email"
                                       value="{{ $customerInfo['email'] ?? old('email') }}"
                                       required
                                       autofocus
                                       autocomplete="email"
                                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm focus:border-volt-purple focus:ring-volt-purple focus:outline-none @error('email') border-red-500 @enderror"
                                       data-save-field="email">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Phone --}}
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('shop.phone') }}
                                </label>
                                <input type="tel"
                                       id="phone"
                                       name="phone"
                                       value="{{ $customerInfo['phone'] ?? old('phone') }}"
                                       autocomplete="tel"
                                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm focus:border-volt-purple focus:ring-volt-purple focus:outline-none @error('phone') border-red-500 @enderror"
                                       data-save-field="phone">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Name --}}
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('shop.full_name') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       value="{{ $customerInfo['name'] ?? old('name') }}"
                                       required
                                       autocomplete="name"
                                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm focus:border-volt-purple focus:ring-volt-purple focus:outline-none @error('name') border-red-500 @enderror"
                                       data-save-field="name">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Billing Address --}}
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-1">{{ __('shop.billing_address') }}</h2>
                        <p class="text-sm text-gray-500 mb-4">{{ __('shop.pickup_notice') }}</p>

                        <div class="space-y-4">
                            {{-- Address Line 1 --}}
                            <div>
                                <label for="billing_address_line1" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('shop.address') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       id="billing_address_line1"
                                       name="billing_address_line1"
                                       value="{{ old('billing_address_line1') }}"
                                       required
                                       autocomplete="address-line1"
                                       placeholder="{{ __('shop.address_line1_placeholder') }}"
                                       class="w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm focus:border-volt-purple focus:ring-volt-purple focus:outline-none @error('billing_address_line1') border-red-500 @enderror">
                                @error('billing_address_line1')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- City and Postal Code --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="billing_postal_code" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ __('shop.postal_code') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           id="billing_postal_code"
                                           name="billing_postal_code"
                                           value="{{ old('billing_postal_code') }}"
                                           required
                                           autocomplete="postal-code"
                                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm focus:border-volt-purple focus:ring-volt-purple focus:outline-none @error('billing_postal_code') border-red-500 @enderror">
                                    @error('billing_postal_code')
                                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="billing_city" class="block text-sm font-medium text-gray-700 mb-1">
                                        {{ __('shop.city') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           id="billing_city"
                                           name="billing_city"
                                           value="{{ old('billing_city') }}"
                                           required
                                           autocomplete="address-level2"
                                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 shadow-sm focus:border-volt-purple focus:ring-volt-purple focus:outline-none @error('billing_city') border-red-500 @enderror">
                                    @error('billing_city')
                                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Country --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('shop.country') }}
                                </label>
                                <p class="text-gray-900">Nederland</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Order Summary --}}
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('shop.order_summary') }}</h2>

                        <ul class="divide-y divide-gray-200 mb-4">
                            @foreach($items as $item)
                                <li class="py-3 flex justify-between">
                                    <div>
                                        <span class="font-medium text-gray-900">{{ $item['product']->localized_name }}</span>
                                        @if($item['size'])
                                            <span class="text-gray-500">({{ $item['size'] }})</span>
                                        @endif
                                        <span class="text-gray-500">&times; {{ $item['quantity'] }}</span>
                                    </div>
                                    <span class="text-gray-900">&euro;{{ number_format($item['subtotal'] / 100, 2, ',', '.') }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="space-y-2 border-t border-gray-200 pt-4">
                            <div class="flex justify-between text-gray-600">
                                <span>{{ __('shop.subtotal') }}</span>
                                <span>&euro;{{ $subtotal }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>{{ __('shop.processing_fee') }} (iDEAL | Wero)</span>
                                <span>&euro;{{ $fee }}</span>
                            </div>
                            <div class="flex justify-between text-lg font-semibold text-gray-900 pt-2 border-t border-gray-200">
                                <span>{{ __('shop.total') }}</span>
                                <span>&euro;{{ $total }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <button type="submit"
                        class="w-full bg-volt-purple hover:bg-volt-purple-dark text-white font-semibold py-4 px-6 rounded-lg transition-colors flex items-center justify-center space-x-2">
                    <span>{{ __('shop.pay_with_ideal') }}</span>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </button>

                <p class="mt-4 text-center text-sm text-gray-500">
                    {{ __('shop.secure_payment') }}
                </p>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('cart.index') }}" class="text-volt-purple hover:text-volt-purple-dark font-medium">
                    &larr; {{ __('shop.return_to_cart') }}
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const saveFields = document.querySelectorAll('[data-save-field]');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            saveFields.forEach(function(field) {
                field.addEventListener('blur', function() {
                    const fieldName = this.dataset.saveField;
                    const value = this.value.trim();

                    if (value === '') return;

                    fetch('{{ route('cart.customer-info') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            [fieldName]: value
                        })
                    });
                });
            });
        });
    </script>
</x-layouts.app>
