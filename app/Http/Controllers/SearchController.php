<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $term = $request->get('q', '');

        $products = collect();

        if (strlen($term) >= 2) {
            $products = Product::active()
                ->search($term)
                ->with('category')
                ->ordered('newest')
                ->paginate(20)
                ->withQueryString();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'results' => collect($products)->take(6)->map(fn($p) => [
                    'id'        => $p->id,
                    'name'      => $p->name,
                    'price'     => $p->formatted_price,
                    'thumbnail' => $p->thumbnail_url,
                    'url'       => route('products.show', $p),
                ]),
            ]);
        }

        return view('search.index', compact('products', 'term'));
    }
}