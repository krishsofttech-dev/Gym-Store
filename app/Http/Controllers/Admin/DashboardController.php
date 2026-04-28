<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_orders'    => Order::count(),
            'orders_today'    => Order::whereDate('created_at', today())->count(),
            'revenue_total'   => (float) Order::paid()->sum('total'),
            'revenue_today'   => (float) Order::paid()->whereDate('created_at', today())->sum('total'),
            'total_products'  => Product::count(),
            'low_stock'       => Product::where('stock_quantity', '<=', 5)->where('track_quantity', true)->count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'pending_reviews' => Review::pending()->count(),
        ];

        $recentOrders = Order::with('user')
            ->recent()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders'));
    }
}