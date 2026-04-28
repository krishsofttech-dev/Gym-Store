<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;

/**
 * LESSON: Controllers receive the HTTP request and return a response.
 * They should be THIN — minimal logic, just orchestration.
 * The heavy lifting belongs in:
 *   - Models (data + business rules)
 *   - Service classes (complex operations)
 *   - Blade views (presentation)
 *
 * A controller method should ideally do 3 things:
 *   1. Get data (from models/services)
 *   2. Transform/prepare it if needed
 *   3. Return a view or redirect
 */
class HomeController extends Controller
{
    public function index()
    {
        /**
         * LESSON: compact() is a PHP shorthand.
         * compact('featured', 'newArrivals', 'categories')
         * is identical to:
         * ['featured' => $featured, 'newArrivals' => $newArrivals, ...]
         *
         * These variables become available in the Blade view as:
         * $featured, $newArrivals, $categories
         */
        $featured = Product::active()
            ->featured()
            ->inStock()
            ->with('category')  // eager load — prevent N+1
            ->limit(8)
            ->get();

        $newArrivals = Product::active()
            ->newArrivals()
            ->with('category')
            ->latest()          // order by created_at desc
            ->limit(8)
            ->get();

        $categories = Category::active()
            ->topLevel()
            ->ordered()
            ->withCount(['products' => fn($q) => $q->where('is_active', true)])
            // LESSON: withCount() adds a products_count property to each category
            // without loading all the actual products — very efficient!
            ->get();

        return view('home.index', compact('featured', 'newArrivals', 'categories'));
    }
}