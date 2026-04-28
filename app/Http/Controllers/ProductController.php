<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

/**
 * LESSON: This controller handles the public product catalog.
 * Two methods:
 *   index()  /products         (listing with filters + pagination)
 *   show()   /products/{slug}  (single product detail page)
 */
class ProductController extends Controller
{
    /**
     * Product listing page — with filtering, sorting, and pagination.
     *
     * URL examples:
     *   /products
     *   /products?category=dumbbells&sort=price_asc&min_price=1000&max_price=20000
     *   /products?search=barbell&page=2
     */
    public function index(Request $request)
    {
        /**
         * LESSON: We build the query step by step, only adding
         * conditions when the request actually contains that filter.
         * This is called a "conditional query builder" pattern.
         */
        $query = Product::active()->with('category');

        // Category filter
        if ($request->filled('category')) {
            $query->inCategory($request->category);
        }

        // Search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Price range filter
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->priceBetween(
                $request->float('min_price', 0),
                $request->float('max_price', 9999999)
            );
        }

        // Brand filter
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        // In stock only
        if ($request->boolean('in_stock')) {
            $query->inStock();
        }

        // Sorting
        $query->ordered($request->get('sort', 'newest'));

        // LESSON: paginate(20) does three things:
        //   1. Adds LIMIT 20 OFFSET N to the SQL query
        //   2. Runs a COUNT(*) query to know total pages
        //   3. Returns a LengthAwarePaginator object
        // In Blade: {{ $products->links() }} renders the page buttons automatically.
        $products = $query->paginate(20)->withQueryString();
        // withQueryString() keeps filters in pagination links:
        // /products?category=dumbbells&page=2  (not just /products?page=2)

        // Sidebar data
        $categories = Category::active()->topLevel()->ordered()->get();
        $brands = Product::active()->distinct()->pluck('brand')->filter()->sort()->values();

        // Price range bounds for the slider
        $priceRange = [
            'min' => (int) Product::active()->min('price'),
            'max' => (int) Product::active()->max('price'),
        ];

        return view('products.index', compact(
            'products', 'categories', 'brands', 'priceRange'
        ));
    }

    /**
     * Single product detail page.
     *
     * LESSON: Route model binding — Laravel automatically finds the product
     * by its slug column and injects it. If not found  404 automatically.
     * No need for: $product = Product::where('slug', $slug)->firstOrFail();
     */
    public function show(Product $product)
    {
        // 404 if product is inactive
        abort_if(! $product->is_active, 404);

        // Load related data — eager loading prevents N+1
        $product->load(['category', 'reviews' => function ($q) {
            $q->approved()->with('user')->latest()->limit(10);
        }]);

        // Related products — same category, exclude current
        $related = Product::active()
            ->inStock()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with('category')
            ->limit(4)
            ->get();

        // Has the logged-in user already reviewed this product?
        $userReview = null;
        $isWishlisted = false;
        if (auth()->check()) {
            $userReview = $product->reviews()
                ->where('user_id', auth()->id())
                ->first();
            $isWishlisted = auth()->user()->hasWishlisted($product->id);
        }

        return view('products.show', compact(
            'product', 'related', 'userReview', 'isWishlisted'
        ));
    }
}