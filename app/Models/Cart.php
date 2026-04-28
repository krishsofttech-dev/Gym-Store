<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * LESSON: The Cart model represents a user's active shopping basket.
 * One user  one cart  many cart items.
 *
 * Notice the "getOrCreate" pattern — we never assume a cart exists.
 * We always use Cart::getOrCreateForUser() to safely get or make one.
 */
class Cart extends Model
{
    protected $fillable = ['user_id', 'session_id'];

    // =========================================================
    // RELATIONSHIPS
    // =========================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * LESSON: with('items.product') is called "eager loading".
     * It prevents the N+1 query problem.
     *
     * BAD (N+1): foreach($cart->items as $item) { $item->product->name }
     *   = 1 query for items + N queries for each product
     *
     * GOOD (eager): $cart->load('items.product')
     *   = 1 query for items + 1 query for ALL products
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    // =========================================================
    // COMPUTED PROPERTIES
    // =========================================================

    /**
     * Total number of items (sum of quantities)
     * $cart->item_count  3
     */
    public function getItemCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Cart subtotal before discounts
     * $cart->subtotal  34500.00
     */
    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(fn($item) => $item->unit_price * $item->quantity);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rs. ' . number_format($this->subtotal, 2);
    }

    // =========================================================
    // STATIC HELPERS
    // =========================================================

    /**
     * LESSON: This "factory" static method is a common pattern.
     * It encapsulates the "find or create" logic in one place.
     * Everywhere in your app you just write:
     *   $cart = Cart::getOrCreateForUser(auth()->user());
     */
    public static function getOrCreateForUser(User $user): self
    {
        return self::firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * Get or create a guest cart by session ID
     */
    public static function getOrCreateForSession(string $sessionId): self
    {
        return self::firstOrCreate(['session_id' => $sessionId]);
    }

    /**
     * Merge a guest cart into a user cart (called on login)
     * LESSON: This is called in the Login controller after authentication.
     */
    public function mergeGuestCart(Cart $guestCart): void
    {
        foreach ($guestCart->items as $guestItem) {
            /** @var CartItem|null $existing */
            $existing = $this->items()
                ->where('product_id', $guestItem->product_id)
                ->first();

            if ($existing) {
                // Add quantities together
                $existing->increment('quantity', $guestItem->quantity);
            } else {
                // Move item to user cart
                $guestItem->update(['cart_id' => $this->id]);
            }
        }

        $guestCart->delete();
    }
}