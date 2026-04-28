<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class CartService
{
    // =========================================================
    // CART RESOLUTION
    // =========================================================

    /**
     * Lightweight cart for navbar/drawer — loads items.product only
     */
    public function getCurrentCart(): ?Cart
    {
        if (auth()->check()) {
            return Cart::where('user_id', auth()->id())
                       ->with('items.product')
                       ->first();
        }

        return Cart::where('session_id', Session::getId())
                   ->with('items.product')
                   ->first();
    }

    /**
     * Full cart for the cart page — loads items.product.category in one query.
     * Use this in CartController::index() ONLY.
     * Never call $cart->load() after this — it resets items to empty.
     */
    public function getCartForPage(): ?Cart
    {
        if (auth()->check()) {
            return Cart::where('user_id', auth()->id())
                       ->with('items.product.category')
                       ->first();
        }

        return Cart::where('session_id', Session::getId())
                   ->with('items.product.category')
                   ->first();
    }

    public function getOrCreateCart(): Cart
    {
        if (auth()->check()) {
            return Cart::getOrCreateForUser(auth()->user());
        }

        return Cart::getOrCreateForSession(Session::getId());
    }

    // =========================================================
    // ADD ITEM
    // =========================================================

    public function addItem(int $productId, int $quantity): array
    {
        $product = Product::find($productId);

        if (! $product || ! $product->is_active) {
            return ['success' => false, 'message' => 'Product not found.'];
        }

        if (! $product->hasEnoughStock($quantity)) {
            return [
                'success' => false,
                'message' => "Sorry, only {$product->stock_quantity} left in stock.",
            ];
        }

        $cart     = $this->getOrCreateCart();
        $existing = CartItem::where('cart_id', $cart->id)
                            ->where('product_id', $product->id)
                            ->first();

        if ($existing) {
            $newQty = $existing->quantity + $quantity;

            if (! $product->hasEnoughStock($newQty)) {
                return [
                    'success' => false,
                    'message' => "Can't add more — only {$product->stock_quantity} available.",
                ];
            }

            $existing->update(['quantity' => $newQty]);
        } else {
            CartItem::create([
                'cart_id'    => $cart->id,
                'product_id' => $product->id,
                'quantity'   => $quantity,
                'unit_price' => $product->price,
            ]);
        }

        $cart->load('items');

        return [
            'success'    => true,
            'message'    => "{$product->name} added to cart!",
            'item_count' => $cart->item_count,
        ];
    }

    // =========================================================
    // UPDATE QUANTITY
    // =========================================================

    public function updateItem(CartItem $item, int $quantity): array
    {
        $product = $item->product;

        if (! $product->hasEnoughStock($quantity)) {
            return [
                'success' => false,
                'message' => "Only {$product->stock_quantity} available.",
            ];
        }

        $item->update(['quantity' => $quantity]);

        $cart = $this->getCurrentCart();
        $cart?->load('items.product');

        return [
            'success'        => true,
            'item_subtotal'  => $item->fresh()->formatted_line_total,
            'cart_subtotal'  => $cart?->formatted_subtotal ?? 'Rs. 0.00',
            'item_count'     => $cart?->item_count ?? 0,
        ];
    }

    // =========================================================
    // REMOVE ITEM
    // =========================================================

    public function removeItem(CartItem $item): array
    {
        $item->delete();

        $cart = $this->getCurrentCart();

        return [
            'success'       => true,
            'item_count'    => $cart?->load('items')->item_count ?? 0,
            'cart_subtotal' => $cart?->formatted_subtotal ?? 'Rs. 0.00',
        ];
    }

    // =========================================================
    // CLEAR
    // =========================================================

    public function clearCart(): void
    {
        $cart = $this->getCurrentCart();
        $cart?->items()->delete();
        Session::forget('coupon');
    }

    // =========================================================
    // COUPON
    // =========================================================

    public function applyCoupon(string $code): array
    {
        $coupon = Coupon::findByCode(strtoupper($code));

        if (! $coupon || ! $coupon->isValid()) {
            return ['success' => false, 'message' => 'Invalid or expired coupon code.'];
        }

        Session::put('coupon', $coupon->code);

        return ['success' => true, 'message' => "Coupon \"{$coupon->code}\" applied!"];
    }

    public function getActiveCoupon(): ?Coupon
    {
        $code = Session::get('coupon');
        if (! $code) return null;

        $coupon = Coupon::findByCode($code);
        return ($coupon && $coupon->isValid()) ? $coupon : null;
    }

    // =========================================================
    // TOTALS
    // =========================================================

    public function calculateTotals(?Cart $cart): array
    {
        if (! $cart || $cart->items->isEmpty()) {
            return [
                'subtotal' => 0,
                'discount' => 0,
                'shipping' => 0,
                'total'    => 0,
                'coupon'   => null,
            ];
        }

        $subtotal = $cart->subtotal;
        $coupon   = $this->getActiveCoupon();
        $discount = $coupon ? $coupon->calculateDiscount($subtotal) : 0;
        $shipping = $this->calculateShipping($subtotal - $discount);
        $total    = $subtotal - $discount + $shipping;

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'shipping' => round($shipping, 2),
            'total'    => round($total, 2),
            'coupon'   => $coupon,
        ];
    }

    // =========================================================
    // MERGE GUEST CART ON LOGIN
    // =========================================================

    public function mergeGuestCartOnLogin(User $user): void
    {
        $sessionId = Session::getId();
        $guestCart = Cart::where('session_id', $sessionId)->with('items')->first();

        if (! $guestCart || $guestCart->items->isEmpty()) {
            return;
        }

        $userCart = Cart::getOrCreateForUser($user);
        $userCart->mergeGuestCart($guestCart);
    }

    // =========================================================
    // CART COUNT (for navbar badge)
    // =========================================================

    public function getCartCount(): int
    {
        $cart = $this->getCurrentCart();
        return $cart ? $cart->item_count : 0;
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    private function calculateShipping(float $subtotal): float
    {
        return $subtotal >= 5000 ? 0.0 : 350.0;
    }
}