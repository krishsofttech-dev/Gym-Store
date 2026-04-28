<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlisted = auth()->user()
            ->wishlists()
            ->with('product.category')
            ->latest()
            ->paginate(20);

        return view('wishlist.index', compact('wishlisted'));
    }

    public function toggle(Request $request, Product $product)
    {
        /** @var Wishlist|null $wishlist */
        $wishlist = auth()->user()
            ->wishlists()
            ->where('product_id', $product->id)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            $wishlisted = false;
            $message = 'Removed from wishlist.';
        } else {
            auth()->user()->wishlists()->create(['product_id' => $product->id]);
            $wishlisted = true;
            $message = 'Added to wishlist!';
        }

        if ($request->wantsJson()) {
            return response()->json(compact('wishlisted', 'message'));
        }

        return back()->with('success', $message);
    }
}