<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function index()
    {
        $cart   = $this->cartService->getCartForPage();
        $coupon = $this->cartService->getActiveCoupon();

        return view('cart.index', [
            'cart'   => $cart,
            // FIX: pass the code string, not the Coupon model object
            // The blade uses {{ $coupon }} expecting a plain string like "GYMFIT20"
            'coupon' => $coupon?->code,
        ]);
    }

    public function add(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity'   => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $result = $this->cartService->addItem(
            (int) $request->product_id,
            (int) $request->quantity
        );

        if ($request->wantsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        return $result['success']
            ? back()->with('success', $result['message'])
            : back()->with('error', $result['message']);
    }

    public function update(Request $request, CartItem $item): RedirectResponse|JsonResponse
    {
        $this->authorizeItem($item);
        $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99']]);

        $result = $this->cartService->updateItem($item, (int) $request->quantity);

        if ($request->wantsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        return $result['success']
            ? back()->with('success', 'Cart updated.')
            : back()->with('error', $result['message']);
    }

    public function remove(Request $request, CartItem $item): RedirectResponse|JsonResponse
    {
        $this->authorizeItem($item);
        $result = $this->cartService->removeItem($item);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', 'Item removed.');
    }

    public function clear(): RedirectResponse
    {
        $this->cartService->clearCart();
        return redirect()->route('cart.index')->with('success', 'Cart cleared.');
    }

    private function authorizeItem(CartItem $item): void
    {
        $cart = $this->cartService->getCurrentCart();
        if (! $cart || $item->cart_id !== $cart->id) {
            abort(403, 'This item does not belong to your cart.');
        }
    }
}