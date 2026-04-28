<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService    $cartService,
        private OrderService   $orderService,
        private PaymentService $paymentService,
    ) {}

    public function index()
    {
        $cart = Cart::where('user_id', auth()->id())
            ->with('items.product')->first();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $totals    = $this->cartService->calculateTotals($cart);
        $addresses = auth()->user()->addresses()->get();

        $subtotal = $totals['subtotal'];
        $discount = $totals['discount'];
        $shipping = $totals['shipping'];
        $total    = $totals['total'];
        $coupon   = $totals['coupon'];

        return view('checkout.index', compact(
            'cart', 'addresses', 'subtotal', 'discount', 'shipping', 'total', 'coupon'
        ));
    }

    public function process(Request $request)
    {
        $data = $request->validate([
            'shipping_name'          => ['required', 'string', 'max:255'],
            'shipping_email'         => ['required', 'email'],
            'shipping_phone'         => ['nullable', 'string', 'max:20'],
            'shipping_address_line1' => ['required', 'string', 'max:255'],
            'shipping_address_line2' => ['nullable', 'string', 'max:255'],
            'shipping_city'          => ['required', 'string', 'max:100'],
            'shipping_state'         => ['nullable', 'string', 'max:100'],
            'shipping_postal_code'   => ['required', 'string', 'max:20'],
            'shipping_country'       => ['required', 'string', 'size:2'],
            'payment_method'         => ['required', 'in:stripe,cod'],
            'stripe_payment_intent'  => ['required_if:payment_method,stripe'],
            'customer_notes'         => ['nullable', 'string', 'max:1000'],
        ]);

        $cart = Cart::where('user_id', auth()->id())
            ->with('items.product')->first();

        if (! $cart || $cart->items->isEmpty()) {
            return back()->with('error', 'Your cart is empty.');
        }

        $order = $this->orderService->createFromCart(
            cart:                  $cart,
            shippingData:          $data,
            paymentMethod:         $data['payment_method'],
            stripePaymentIntentId: $data['stripe_payment_intent'] ?? null,
        );

        if ($data['payment_method'] === 'stripe') {
            $order->transitionTo(Order::STATUS_CONFIRMED);
        }

        return redirect()->route('checkout.success', $order)
            ->with('success', 'Order placed successfully!');
    }

    public function success(Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);
        $order->load('items');
        return view('checkout.success', compact('order'));
    }
}