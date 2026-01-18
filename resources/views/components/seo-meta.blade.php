@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'url' => null,
    'type' => 'website',
    'product' => null,
    'noindex' => false,
])

@php
    $siteName = 'Volt Amsterdam Merch';
    $defaultDescription = __('seo.site_description');
    // Use SVG as fallback (replace with proper 1200x630 PNG for best social media support)
    $defaultImage = asset('images/og-default.svg');

    // Build the final values - title without suffix for OG (page title already has suffix)
    $metaTitle = $title ? "{$title} | {$siteName}" : $siteName;
    $ogTitle = $title ?: $siteName; // Cleaner title for social sharing
    $metaDescription = $description ?? $defaultDescription;
    $metaImage = $image ?? $defaultImage;
    $metaUrl = $url ?? request()->url();
    $metaType = $product ? 'product' : $type;

    // Clean description (strip HTML, limit length)
    $metaDescription = Str::limit(strip_tags($metaDescription), 160);

    // Ensure image is absolute URL
    if ($metaImage && !Str::startsWith($metaImage, ['http://', 'https://'])) {
        $metaImage = url($metaImage);
    }
@endphp

{{-- Basic SEO Meta Tags --}}
<meta name="description" content="{{ $metaDescription }}">
@if($noindex)
<meta name="robots" content="noindex, nofollow">
@else
<meta name="robots" content="index, follow">
@endif
<link rel="canonical" href="{{ $metaUrl }}">

{{-- OpenGraph Meta Tags (Facebook, WhatsApp, LinkedIn, etc.) --}}
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ $metaUrl }}">
<meta property="og:type" content="{{ $metaType }}">
<meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
@if(app()->getLocale() === 'nl')
<meta property="og:locale:alternate" content="en">
@else
<meta property="og:locale:alternate" content="nl">
@endif

@if($metaImage)
<meta property="og:image" content="{{ $metaImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $ogTitle }}">
@endif

{{-- Twitter Card Meta Tags --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
@if($metaImage)
<meta name="twitter:image" content="{{ $metaImage }}">
<meta name="twitter:image:alt" content="{{ $ogTitle }}">
@endif

{{-- Product-specific OpenGraph tags --}}
@if($product)
<meta property="product:price:amount" content="{{ number_format($product->price / 100, 2, '.', '') }}">
<meta property="product:price:currency" content="EUR">
@if($product->isInStock())
<meta property="product:availability" content="in stock">
@else
<meta property="product:availability" content="out of stock">
@endif
@endif

{{-- JSON-LD Structured Data for Products --}}
@if($product)
@php
    $productImages = [];
    foreach($product->all_image as $img) {
        $productImages[] = Storage::url($img);
    }
    $productJsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->localized_name,
        'description' => Str::limit(strip_tags($product->localized_description), 500),
        'url' => route('products.show', $product->slug),
        'brand' => [
            '@type' => 'Organization',
            'name' => 'Volt Amsterdam',
        ],
        'offers' => [
            '@type' => 'Offer',
            'url' => route('products.show', $product->slug),
            'priceCurrency' => 'EUR',
            'price' => number_format($product->price / 100, 2, '.', ''),
            'availability' => $product->isInStock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'seller' => [
                '@type' => 'Organization',
                'name' => 'Volt Amsterdam',
            ],
        ],
    ];
    if (count($productImages) > 0) {
        $productJsonLd['image'] = $productImages;
    }
@endphp
<script type="application/ld+json">{!! json_encode($productJsonLd, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}</script>
@endif

{{-- JSON-LD Organization Data (for homepage) --}}
@if($type === 'website' && !$product)
@php
    $orgJsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'Volt Amsterdam',
        'url' => config('app.url'),
        'logo' => asset('images/og-default.svg'),
        'description' => $defaultDescription,
    ];
    $siteJsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $siteName,
        'url' => config('app.url'),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($orgJsonLd, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/ld+json">{!! json_encode($siteJsonLd, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}</script>
@endif
