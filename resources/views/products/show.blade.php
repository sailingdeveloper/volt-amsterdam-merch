@php
    $productImage = count($product->all_image) > 0 ? Storage::url($product->all_image[0]) : null;
    $productDescription = Str::limit(strip_tags($product->localized_description), 160);
@endphp

<x-layouts.app
    :title="$product->localized_name"
    :description="$productDescription . ' - €' . $product->formatted_price"
    :image="$productImage"
    :url="route('products.show', $product->slug)"
    type="product"
    :product="$product"
>
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
                {{-- Product Images --}}
                <div class="space-y-4">
                    @php
                        $images = $product->all_image;
                    @endphp

                    {{-- Main Image --}}
                    <div class="aspect-square bg-gray-100 rounded-2xl overflow-hidden">
                        @if(count($images) > 0)
                            <img src="{{ Storage::url($images[0]) }}"
                                 alt="{{ $product->localized_name }}"
                                 class="h-full w-full object-cover"
                                 id="main-product-image">
                        @else
                            <div class="h-full w-full flex items-center justify-center bg-gradient-to-br from-volt-purple/10 to-volt-purple/20">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-32 h-32 text-volt-purple/40">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                </svg>
                            </div>
                        @endif
                    </div>

                    {{-- Thumbnail Gallery --}}
                    @if(count($images) > 1)
                        <div class="grid grid-cols-4 gap-3">
                            @foreach($images as $index => $imagePath)
                                <button type="button"
                                        onclick="document.getElementById('main-product-image').src = '{{ Storage::url($imagePath) }}'"
                                        class="aspect-square bg-gray-100 rounded-lg overflow-hidden border-2 hover:border-volt-purple transition-colors {{ $index === 0 ? 'border-volt-purple' : 'border-transparent' }}">
                                    <img src="{{ Storage::url($imagePath) }}"
                                         alt="{{ $product->localized_name }} - {{ $index + 1 }}"
                                         class="h-full w-full object-cover">
                                </button>
                            @endforeach
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

                            <div class="flex flex-wrap gap-4">
                                @if($product->hasSizes())
                                    <div>
                                        <label for="size" class="block text-sm font-medium text-gray-700 mb-2">
                                            {{ __('shop.size') }}
                                        </label>
                                        <select name="size" id="size" required
                                                class="w-full bg-white text-gray-700 font-medium py-2.5 px-4 rounded-lg border border-gray-300 focus:ring-2 focus:ring-volt-purple focus:border-volt-purple cursor-pointer appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%22%20fill%3D%22%236b7280%22%3E%3Cpath%20fill-rule%3D%22evenodd%22%20d%3D%22M5.23%207.21a.75.75%200%20011.06.02L10%2011.168l3.71-3.938a.75.75%200%20111.08%201.04l-4.25%204.5a.75.75%200%2001-1.08%200l-4.25-4.5a.75.75%200%2001.02-1.06z%22%20clip-rule%3D%22evenodd%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.25rem] bg-[right_0.5rem_center] bg-no-repeat pr-10">
                                            <option value="">{{ __('shop.select_size') }}</option>
                                            @foreach($product->ordered_sizes as $size => $stock)
                                                @if($stock > 10)
                                                    <option value="{{ $size }}">{{ $size }}</option>
                                                @elseif($stock > 0)
                                                    <option value="{{ $size }}">{{ $size }} ({{ $stock }})</option>
                                                @else
                                                    <option value="{{ $size }}" disabled>{{ $size }} - {{ __('shop.out_of_stock') }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div>
                                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('shop.quantity') }}
                                    </label>
                                    <select name="quantity" id="quantity"
                                            class="w-20 bg-white text-gray-700 font-medium py-2.5 px-4 rounded-lg border border-gray-300 focus:ring-2 focus:ring-volt-purple focus:border-volt-purple cursor-pointer appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%22%20fill%3D%22%236b7280%22%3E%3Cpath%20fill-rule%3D%22evenodd%22%20d%3D%22M5.23%207.21a.75.75%200%20011.06.02L10%2011.168l3.71-3.938a.75.75%200%20111.08%201.04l-4.25%204.5a.75.75%200%2001-1.08%200l-4.25-4.5a.75.75%200%2001.02-1.06z%22%20clip-rule%3D%22evenodd%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.25rem] bg-[right_0.5rem_center] bg-no-repeat pr-10">
                                        @for($i = 1; $i <= 10; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <button type="submit"
                                    class="w-full sm:w-auto bg-volt-purple hover:bg-volt-purple-dark text-white font-semibold py-3 px-8 rounded-lg transition-colors inline-flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" fill="currentColor" class="w-5 h-5">
                                    <path d="M24 0C10.7 0 0 10.7 0 24S10.7 48 24 48H76.1l60.3 316.5c2.2 11.3 12.1 19.5 23.6 19.5H488c13.3 0 24-10.7 24-24s-10.7-24-24-24H179.9l-9.1-48h317c14.3 0 26.9-9.5 30.8-23.3l54-192C578.3 52.3 563 32 541.8 32H122l-2.4-12.5C117.4 8.2 107.5 0 96 0H24zM176 512c26.5 0 48-21.5 48-48s-21.5-48-48-48s-48 21.5-48 48s21.5 48 48 48zm336-48c0-26.5-21.5-48-48-48s-48 21.5-48 48s21.5 48 48 48s48-21.5 48-48zM252 160c0-11 9-20 20-20h44V96c0-11 9-20 20-20s20 9 20 20v44h44c11 0 20 9 20 20s-9 20-20 20H356v44c0 11-9 20-20 20s-20-9-20-20V180H272c-11 0-20-9-20-20z"/>
                                </svg>
                                {{ __('shop.add_to_cart') }}
                            </button>
                        </form>

                        @if(!$product->hasSizes() && $product->stock <= 10)
                            <p class="mt-4 text-sm text-orange-600 flex items-center">
                                <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                {{ __('shop.x_left_in_stock', ['count' => $product->stock]) }}
                            </p>
                        @else
                            <p class="mt-4 text-sm text-green-600 flex items-center">
                                <svg class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ __('shop.in_stock') }}
                            </p>
                        @endif
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
