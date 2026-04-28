<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Notifications\OrderPlaced;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class OrderService
{
    public function __construct(private CartService $cartService) {}

    public function createFromCart(
        Cart $cart, array $shippingData,
        string $paymentMethod, ?string $stripePaymentIntentId = null
    ): Order {
        $totals = $this->cartService->calculateTotals($cart);

        $order = DB::transaction(function () use ($cart, $shippingData, $paymentMethod, $stripePaymentIntentId, $totals) {
            $order = Order::create([
                'user_id'         => auth()->id(),
                'subtotal'        => $totals['subtotal'],
                'discount_amount' => $totals['discount'],
                'shipping_amount' => $totals['shipping'],
                'tax_amount'      => 0,
                'total'           => $totals['total'],
                'status'          => Order::STATUS_PENDING,
                'payment_status'  => $paymentMethod === 'cod' ? Order::PAYMENT_UNPAID : Order::PAYMENT_PAID,
                'payment_method'             => $paymentMethod,
                'stripe_payment_intent_id'   => $stripePaymentIntentId,
                'coupon_code'                => $totals['coupon']?->code,
                'shipping_name'          => $shippingData['shipping_name'],
                'shipping_email'         => $shippingData['shipping_email'],
                'shipping_phone'         => $shippingData['shipping_phone'] ?? null,
                'shipping_address_line1' => $shippingData['shipping_address_line1'],
                'shipping_address_line2' => $shippingData['shipping_address_line2'] ?? null,
                'shipping_city'          => $shippingData['shipping_city'],
                'shipping_state'         => $shippingData['shipping_state'] ?? null,
                'shipping_postal_code'   => $shippingData['shipping_postal_code'],
                'shipping_country'       => $shippingData['shipping_country'],
                'customer_notes'         => $shippingData['customer_notes'] ?? null,
            ]);

            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;
                OrderItem::create([
                    'order_id'      => $order->id,
                    'product_id'    => $product->id,
                    'product_name'  => $product->name,
                    'product_sku'   => $product->sku,
                    'product_image' => $product->thumbnail,
                    'unit_price'    => $cartItem->unit_price,
                    'quantity'      => $cartItem->quantity,
                    'subtotal'      => $cartItem->line_total,
                ]);
                $product->reduceStock($cartItem->quantity);
                $product->increment('sales_count', $cartItem->quantity);
            }

            $totals['coupon']?->markUsed();
            $cart->items()->delete();
            Session::forget('coupon');
            return $order;
        });

        // Send confirmation email AFTER transaction commits
        try {
            $order->user->notify(new OrderPlaced($order));
        } catch (\Exception $e) {
            logger()->warning('Order confirmation email failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        return $order;
    }

    public function updateStatus(Order $order, string $newStatus): bool
    {
        $prev = $order->status;
        if (! $order->transitionTo($newStatus)) return false;

        try {
            $order->user->notify(new OrderStatusUpdated($order, $prev));
        } catch (\Exception $e) {
            logger()->warning('Status update email failed', ['order_id' => $order->id]);
        }
        return true;
    }

    public function cancelOrder(Order $order): bool
    {
        if (! $order->isCancellable()) return false;

        $prev = $order->status;
        DB::transaction(function () use ($order) {
            $order->transitionTo(Order::STATUS_CANCELLED);
            foreach ($order->items as $item) {
                $item->product?->increment('stock_quantity', $item->quantity);
                $item->product?->update(['in_stock' => true]);
            }
        });

        try {
            $order->user->notify(new OrderStatusUpdated($order, $prev));
        } catch (\Exception $e) {
            logger()->warning('Cancellation email failed', ['order_id' => $order->id]);
        }
        return true;
    }
}