<?php

/**
 * @author Thijs de Maa <mdemaa@bunq.com>
 *
 * @since 20260118 Initial creation.
 */

namespace App\Http\Middleware;

use App\Models\PageView;
use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    /**
     * Bot patterns constants.
     */
    protected const BOT_PATTERNS = [
        'bot',
        'crawl',
        'spider',
        'slurp',
        'googlebot',
        'bingbot',
        'yandex',
        'baidu',
        'duckduck',
        'facebookexternalhit',
        'linkedinbot',
        'twitterbot',
        'applebot',
        'semrush',
        'ahref',
        'mj12bot',
        'dotbot',
        'petalbot',
        'bytespider',
        'gptbot',
        'chatgpt',
        'claude',
        'anthropic',
        'curl',
        'wget',
        'python',
        'java',
        'php',
        'ruby',
        'go-http',
        'httpie',
        'postman',
        'insomnia',
        'lighthouse',
        'pagespeed',
        'headless',
        'phantom',
        'selenium',
        'puppeteer',
        'playwright',
        'whatsapp',
        'google-read-aloud',
        'googleother',
        'adsbot',
        'mediapartners',
        'updown.io',
        'uptimerobot',
        'pingdom',
        'statuscake',
        'site24x7',
        'monitor',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldTrack($request)) {
            $this->trackPageView($request);
        }

        return $response;
    }

    /**
     * AWS IP ranges (common health check sources).
     */
    protected const AWS_IP_PREFIXES = [
        '3.',
        '13.',
        '18.',
        '34.',
        '35.',
        '44.',
        '50.',
        '52.',
        '54.',
        '99.',
    ];

    /**
     * @return bool
     */
    private function shouldTrack(Request $request): bool
    {
        // Only track GET requests.
        if ($request->method() !== 'GET') {
            return false;
        }

        // Skip cloud provider IPs (health checks).
        if ($this->isCloudProviderIp($request->ip())) {
            return false;
        }

        // Skip if no user agent.
        $userAgent = $request->userAgent();
        if (empty($userAgent)) {
            return false;
        }

        // Skip bots.
        if ($this->isBot($userAgent)) {
            return false;
        }

        // Skip AJAX requests.
        if ($request->ajax()) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function isCloudProviderIp(string $ip): bool
    {
        foreach (self::AWS_IP_PREFIXES as $prefix) {
            if (str_starts_with($ip, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    private function isBot(string $userAgent): bool
    {
        $userAgentLower = strtolower($userAgent);

        foreach (self::BOT_PATTERNS as $pattern) {
            if (str_contains($userAgentLower, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return void
     */
    private function trackPageView(Request $request): void
    {
        $ip = $request->ip();
        $geo = $this->resolveGeoLocation($ip);

        PageView::create([
            'path' => '/' . ltrim($request->path(), '/'),
            'session_id' => $request->session()->getId(),
            'referrer' => $this->parseReferrer($request->header('referer')),
            'ip_address' => $ip,
            'user_agent' => $request->userAgent(),
            'country' => $geo['country'] ?? null,
            'city' => $geo['city'] ?? null,
            'product_id' => $this->resolveProductId($request),
        ]);
    }

    /**
     * @return string|null
     */
    private function parseReferrer(?string $referrer): ?string
    {
        if (empty($referrer)) {
            return null;
        }

        // Don't store internal referrers.
        $host = parse_url($referrer, PHP_URL_HOST);
        if ($host === $this->getAppHost()) {
            return null;
        }

        return $referrer;
    }

    /**
     * @return string|null
     */
    private function getAppHost(): ?string
    {
        return parse_url(config('app.url'), PHP_URL_HOST);
    }

    /**
     * @return int|null
     */
    private function resolveProductId(Request $request): ?int
    {
        $route = $request->route();

        if (is_null($route)) {
            return null;
        }

        if ($route->getName() !== 'products.show') {
            return null;
        }

        $slug = $route->parameter('slug');

        if (empty($slug)) {
            return null;
        }

        $product = Product::where('slug', $slug)->first();

        if (is_null($product)) {
            return null;
        }

        return $product->id;
    }

    /**
     * @return array{country: string|null, city: string|null}
     */
    private function resolveGeoLocation(string $ip): array
    {
        // Skip localhost/private IPs.
        if ($this->isPrivateIp($ip)) {
            return ['country' => null, 'city' => null];
        }

        // Cache geo lookups for 24 hours.
        $cacheKey = 'geo_ip_' . md5($ip);

        return Cache::remember($cacheKey, 86400, function () use ($ip) {
            return $this->fetchGeoLocation($ip);
        });
    }

    /**
     * @return bool
     */
    private function isPrivateIp(string $ip): bool
    {
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return true;
        }

        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    /**
     * @return array{country: string|null, city: string|null}
     */
    private function fetchGeoLocation(string $ip): array
    {
        try {
            $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}?fields=status,countryCode,city");

            if ($response->successful() && $response->json('status') === 'success') {
                return [
                    'country' => $response->json('countryCode'),
                    'city' => $response->json('city'),
                ];
            }
        } catch (\Exception $e) {
            // Silently fail — geo is nice-to-have, not critical.
        }

        return ['country' => null, 'city' => null];
    }
}
