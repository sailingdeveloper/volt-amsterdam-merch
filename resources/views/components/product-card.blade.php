@props(['product'])

<div class="group bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow overflow-hidden">
    <a href="{{ route('products.show', $product->slug) }}" class="block">
        <div class="aspect-square bg-gray-100 relative overflow-hidden">
            @if($product->image)
                <img src="{{ Storage::url($product->image) }}"
                     alt="{{ $product->localized_name }}"
                     class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300">
            @else
                <div class="h-full w-full flex items-center justify-center bg-gradient-to-br from-volt-purple/10 to-volt-purple/20">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 text-volt-purple/40">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                </div>
            @endif

            @if(!$product->isInStock())
                <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                    <span class="bg-red-500 text-white px-4 py-2 rounded-full text-sm font-medium">
                        {{ __('shop.out_of_stock') }}
                    </span>
                </div>
            @endif
        </div>

        <div class="p-4">
            <h3 class="font-semibold text-gray-900 group-hover:text-volt-purple transition-colors">
                {{ $product->localized_name }}
            </h3>
            <p class="mt-1 text-lg font-bold text-volt-purple">
                &euro;{{ $product->formatted_price }}
            </p>
        </div>
    </a>

    <div class="px-4 pb-4">
        @if($product->isOrderable())
            <form action="{{ route('cart.add') }}" method="POST" data-add-to-cart>
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                @if($product->hasSizes())
                    <div class="flex gap-2">
                        <select name="size" required
                                class="w-24 bg-white text-gray-700 font-medium py-2 px-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-volt-purple focus:border-volt-purple cursor-pointer">
                            <option value="">{{ __('shop.size') }}</option>
                            @foreach($product->sizes as $size => $stock)
                                @if($stock > 0)
                                    <option value="{{ $size }}">{{ $size }}</option>
                                @endif
                            @endforeach
                        </select>
                        <button type="submit"
                                class="flex-1 bg-volt-purple hover:bg-volt-purple-dark text-white font-medium py-2 px-4 rounded-lg transition-colors">
                            {{ __('shop.add_to_cart') }}
                        </button>
                    </div>
                @else
                    <button type="submit"
                            class="w-full bg-volt-purple hover:bg-volt-purple-dark text-white font-medium py-2 px-4 rounded-lg transition-colors">
                        {{ __('shop.add_to_cart') }}
                    </button>
                @endif
            </form>
        @elseif($product->orderable === false)
            <a href="{{ route('products.show', $product->slug) }}"
               class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors">
                {{ __('shop.view') }}
            </a>
        @endif
    </div>
</div>
