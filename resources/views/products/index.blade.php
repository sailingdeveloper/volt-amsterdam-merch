<x-layouts.app
    :description="__('seo.site_description')"
    type="website"
>
    {{-- Hero Section --}}
    <section class="bg-gradient-to-br from-volt-purple to-volt-purple-dark text-white py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                {{ __('messages.hero_title') }}
            </h1>
            <p class="text-xl text-white/80 max-w-2xl mx-auto">
                {{ __('messages.hero_subtitle') }}
            </p>
        </div>
    </section>

    {{-- Products Grid --}}
    <section class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-8">{{ __('shop.our_products') }}</h2>

            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                @foreach($products as $product)
                    <x-product-card :product="$product" :is-top-seller="$topSellerIds->contains($product->id)" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- About Section --}}
    <section class="py-12 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('messages.about_title') }}</h2>
                <p class="text-gray-600">
                    {{ __('messages.about_text') }}
                </p>
            </div>
        </div>
    </section>
</x-layouts.app>
