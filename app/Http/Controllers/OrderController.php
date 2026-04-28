<?php namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

/**
 * Customer-facing order history and detail.
 */
class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::forUser(auth()->id())
            ->with('items')
            ->recent()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        // Authorise: only owner can view
        abort_if($order->user_id !== auth()->id(), 403);
        $order->load('items.product');

        return view('orders.show', compact('order'));
    }

    public function cancel(Request $request, Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);

        if (! $order->isCancellable()) {
            return back()->with('error', 'This order can no longer be cancelled.');
        }

        $order->transitionTo(Order::STATUS_CANCELLED);

        // LESSON: Restore stock on cancellation
        foreach ($order->items as $item) {
            $item->product?->increment('stock_quantity', $item->quantity);
        }

        return back()->with('success', 'Order cancelled.');
    }
}