<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(Category $category)
    {
        abort_if(! $category->is_active, 404);

        $products = Product::active()
            ->inStock()
            ->where('category_id', $category->id)
            ->with('category')
            ->ordered(request('sort', 'newest'))
            ->paginate(20)
            ->withQueryString();

        $subcategories = $category->children()->active()->get();

        return view('categories.show', compact('category', 'products', 'subcategories'));
    }
}