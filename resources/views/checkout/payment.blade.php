<x-layouts.app
    :title="__('shop.payment')"
    :noindex="true"
>
    <div class="py-12">
        <div class="mx-auto max-w-lg px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('shop.payment') }}</h1>

            {{-- Order Summary --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-gray-600">{{ __('shop.order_number') }}</span>
                        <span class="font-medium text-gray-900">#{{ $order->order_number }}</span>
                    </div>
                    <div class="flex justify-between items-center text-lg font-semibold">
                        <span class="text-gray-900">{{ __('shop.total') }}</span>
                        <span class="text-gray-900">&euro;{{ $order->formatted_total }}</span>
                    </div>
                </div>
            </div>

            {{-- Payment Form --}}
            <div class="bg-white rounded-xl shadow-sm mb-6">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('shop.select_bank') }}</h2>

                    <form id="payment-form">
                        <div id="ideal-bank-element" class="mb-6">
                            {{-- Stripe iDEAL Bank Element will be inserted here --}}
                        </div>

                        <div id="error-message" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
                        </div>

                        <button type="submit"
                                id="submit-button"
                                class="w-full bg-volt-purple hover:bg-volt-purple-dark disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-semibold py-4 px-6 rounded-lg transition-colors flex items-center justify-center space-x-2">
                            <span id="button-text">{{ __('shop.pay_now') }}</span>
                            <svg id="button-spinner" class="hidden animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </form>

                    <p class="mt-4 text-center text-sm text-gray-500">
                        {{ __('shop.secure_payment') }}
                    </p>
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('cart.index') }}" class="text-volt-purple hover:text-volt-purple-dark font-medium">
                    &larr; {{ __('shop.return_to_cart') }}
                </a>
            </div>
        </div>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const stripe = Stripe('{{ $stripeKey }}');
        const elements = stripe.elements({
            locale: '{{ app()->getLocale() }}'
        });

        // Create and mount the iDEAL Bank Element
        const idealBank = elements.create('idealBank', {
            style: {
                base: {
                    padding: '10px 14px',
                    fontSize: '16px',
                    color: '#32325d',
                    fontFamily: 'Ubuntu, system-ui, sans-serif',
                    '::placeholder': {
                        color: '#aab7c4',
                    },
                },
            },
        });
        idealBank.mount('#ideal-bank-element');

        const form = document.getElementById('payment-form');
        const submitButton = document.getElementById('submit-button');
        const buttonText = document.getElementById('button-text');
        const buttonSpinner = document.getElementById('button-spinner');
        const errorMessage = document.getElementById('error-message');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            // Disable button and show spinner
            submitButton.disabled = true;
            buttonText.textContent = '{{ __('shop.processing') }}';
            buttonSpinner.classList.remove('hidden');
            errorMessage.classList.add('hidden');

            const { error } = await stripe.confirmIdealPayment(
                '{{ $clientSecret }}',
                {
                    payment_method: {
                        ideal: idealBank,
                        billing_details: {
                            name: '{{ $customerName }}',
                        },
                    },
                    return_url: '{{ $returnUrl }}',
                }
            );

            if (error) {
                // Show error
                errorMessage.textContent = error.message;
                errorMessage.classList.remove('hidden');

                // Re-enable button
                submitButton.disabled = false;
                buttonText.textContent = '{{ __('shop.pay_now') }}';
                buttonSpinner.classList.add('hidden');
            }
            // If no error, the customer will be redirected to their bank
        });
    </script>
</x-layouts.app>
