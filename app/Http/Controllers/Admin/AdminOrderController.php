<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request)
    {
        $query = Order::with('user')->recent();
        if ($request->filled('status')) $query->withStatus($request->status);
        if ($request->filled('search')) $query->where('order_number', 'like', "%{$request->search}%");
        $orders = $query->paginate(20)->withQueryString();
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['status' => ['required', 'string']]);
        if (! $this->orderService->updateStatus($order, $request->status)) {
            return back()->with('error', "Cannot transition from {$order->status} to {$request->status}.");
        }
        return back()->with('success', "Order updated to {$request->status}. Customer notified.");
    }

    public function updateTracking(Request $request, Order $order)
    {
        $request->validate([
            'tracking_number'  => ['required', 'string'],
            'shipping_carrier' => ['required', 'string'],
        ]);
        $order->update($request->only('tracking_number', 'shipping_carrier'));
        $this->orderService->updateStatus($order, Order::STATUS_SHIPPED);
        return back()->with('success', 'Tracking saved and customer notified.');
    }
}