<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display the landing page with all products.
     */
    public function index(): View
    {
        $products = Product::where('active', true)->get();

        return view('products.index', compact('products'));
    }

    /**
     * Display a single product.
     */
    public function show(string $slug): View
    {
        $product = Product::where('slug', $slug)
            ->where('active', true)
            ->firstOrFail();

        return view('products.show', compact('product'));
    }
}
