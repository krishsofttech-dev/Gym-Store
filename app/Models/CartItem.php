<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * LESSON: CartItem is the "many" side of Cart  CartItems.
 * Each row = one product in the cart with its own quantity and price snapshot.
 *
 * @property int   $cart_id
 * @property int   $product_id
 * @property int   $quantity
 * @property float $unit_price   (price at time of adding to cart)
 * @property array $options      (size, color, etc.)
 */
class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'unit_price',
        'options',
    ];

    protected function casts(): array
    {
        return [
            'quantity'   => 'integer',
            'unit_price' => 'decimal:2',
            'options'    => 'array',
        ];
    }

    // =========================================================
    // RELATIONSHIPS
    // =========================================================

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // =========================================================
    // COMPUTED PROPERTIES
    // =========================================================

    /**
     * Line total = unit price × quantity
     * $item->line_total  5500.00
     */
    public function getLineTotalAttribute(): float
    {
        return $this->unit_price * $this->quantity;
    }

    public function getFormattedLineTotalAttribute(): string
    {
        return 'Rs. ' . number_format($this->line_total, 2);
    }
}