<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use App\Models\Order;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title'  => ['nullable', 'string', 'max:100'],
            'body'   => ['nullable', 'string', 'max:2000'],
        ]);

        $exists = Review::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'You have already reviewed this product.');
        }

        $verified = Order::forUser(auth()->id())
            ->paid()
            ->whereHas('items', fn($q) => $q->where('product_id', $product->id))
            ->exists();

        Review::create([
            'user_id'           => auth()->id(),
            'product_id'        => $product->id,
            'rating'            => $request->rating,
            'title'             => $request->title,
            'body'              => $request->body,
            'status'            => Review::STATUS_PENDING,
            'verified_purchase' => $verified,
        ]);

        return back()->with('success', 'Review submitted! It will appear after moderation.');
    }

    public function destroy(Review $review)
    {
        abort_if($review->user_id !== auth()->id(), 403);
        $review->delete();
        return back()->with('success', 'Review deleted.');
    }
}