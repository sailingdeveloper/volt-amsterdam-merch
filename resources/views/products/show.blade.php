<x-layouts.app :title="$product->localized_name . ' | Volt Amsterdam'">
    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {{-- Breadcrumb --}}
            <nav class="mb-8">
                <ol class="flex items-center space-x-2 text-sm text-gray-500">
                    <li>
                        <a href="{{ route('products.index') }}" class="hover:text-volt-purple">
                            {{ __('messages.home') }}
                        </a>
                    </li>
                    <li>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </li>
                    <li class="text-gray-900 font-medium">{{ $product->localized_name }}</li>
                </ol>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                {{-- Product Image --}}
                <div class="aspect-square bg-gray-100 rounded-2xl overflow-hidden">
                    @if($product->image)
                        <img src="{{ Storage::url($product->image) }}"
                             alt="{{ $product->localized_name }}"
                             class="h-full w-full object-cover">
                    @else
                        <div class="h-full w-full flex items-center justify-center bg-gradient-to-br from-volt-purple/10 to-volt-purple/20">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-32 h-32 text-volt-purple/40">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Product Info --}}
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">
                        {{ $product->localized_name }}
                    </h1>

                    <p class="text-3xl font-bold text-volt-purple mb-6">
                        &euro;{{ $product->formatted_price }}
                    </p>

                    <div class="prose prose-gray max-w-none mb-8">
                        {!! $product->localized_description !!}
                    </div>

                    @if($product->isOrderable())
                        <form action="{{ route('cart.add') }}" method="POST" class="space-y-4" data-add-to-cart>
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">

                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('shop.quantity') }}
                                </label>
                                <select name="quantity" id="quantity"
                                        class="block w-24 rounded-lg border-gray-300 shadow-sm focus:border-volt-purple focus:ring-volt-purple">
                                    @for($i = 1; $i <= min(10, $product->stock); $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <button type="submit"
                                    class="w-full sm:w-auto bg-volt-purple hover:bg-volt-purple-dark text-white font-semibold py-3 px-8 rounded-lg transition-colors">
                                {{ __('shop.add_to_cart') }}
                            </button>
                        </form>

                        <p class="mt-4 text-sm text-green-600 flex items-center">
                            <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('shop.in_stock') }}
                        </p>
                    @elseif(!$product->orderable)
                        <div class="bg-volt-purple/10 text-volt-purple px-4 py-3 rounded-lg">
                            {{ __('shop.not_orderable_online') }}
                        </div>
                    @else
                        <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg">
                            {{ __('shop.out_of_stock') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
