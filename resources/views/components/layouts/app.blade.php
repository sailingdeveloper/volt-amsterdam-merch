@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'url' => null,
    'type' => 'website',
    'product' => null,
    'noindex' => false,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' | Volt Amsterdam Merch' : 'Volt Amsterdam Merch - Official Campaign Store' }}</title>

    {{-- SEO Meta Tags --}}
    <x-seo-meta
        :title="$title"
        :description="$description"
        :image="$image"
        :url="$url"
        :type="$type"
        :product="$product"
        :noindex="$noindex"
    />

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=ubuntu:400,500,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 flex flex-col">
    <header class="bg-volt-purple text-white shadow-lg">
        <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('products.index') }}" class="flex items-center space-x-2">
                        <span class="text-2xl font-bold">Volt</span>
                        <span class="text-volt-yellow">Amsterdam</span>
                    </a>
                </div>

                <div class="flex items-center space-x-6">
                    {{-- Language Switcher --}}
                    <div class="flex items-center space-x-2 text-sm">
                        <a href="{{ route('language.switch', 'en') }}"
                           class="{{ app()->getLocale() === 'en' ? 'font-bold text-volt-yellow' : 'hover:text-volt-yellow' }}">
                            EN
                        </a>
                        <span class="text-white/50">|</span>
                        <a href="{{ route('language.switch', 'nl') }}"
                           class="{{ app()->getLocale() === 'nl' ? 'font-bold text-volt-yellow' : 'hover:text-volt-yellow' }}">
                            NL
                        </a>
                    </div>

                    {{-- Cart --}}
                    <a href="{{ route('cart.index') }}" class="relative flex items-center hover:text-volt-yellow transition-colors" data-cart-counter>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                        </svg>
                        @inject('cartService', 'App\Services\CartService')
                        @if($cartService->getCount() > 0)
                            <span class="cart-badge absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-volt-yellow text-xs font-bold text-volt-purple">
                                {{ $cartService->getCount() }}
                            </span>
                        @endif
                    </a>
                </div>
            </div>
        </nav>
    </header>

    {{-- Toast Notifications --}}
    @if(session('success'))
        <div class="fixed top-20 right-4 z-50 toast-notification">
            <div class="rounded-lg bg-green-600 text-white px-4 py-3 shadow-lg flex items-center space-x-3">
                <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm font-medium">
                    {{ session('success') }}
                    @if(session('cart_link'))
                        <a href="{{ route('cart.index') }}" class="underline hover:no-underline ml-2">{{ __('shop.view_cart') }}</a>
                    @endif
                </p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="fixed top-20 right-4 z-50 toast-notification">
            <div class="rounded-lg bg-red-600 text-white px-4 py-3 shadow-lg flex items-center space-x-3">
                <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm font-medium">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <main class="flex-1 flex flex-col">
        {{ $slot }}
    </main>

    <footer class="mt-auto bg-volt-purple-dark text-white py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <span class="text-xl font-bold">Volt</span>
                    <span class="text-volt-yellow">Amsterdam</span>
                </div>
                <div class="text-sm text-white/70">
                    &copy; {{ date('Y') }} Volt Amsterdam. {{ __('messages.all_rights_reserved') }}
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
