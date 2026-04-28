<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// ============================================================
// OrderItem
// ============================================================
/**
 * LESSON: OrderItem stores a FROZEN SNAPSHOT of the product at purchase time.
 * product_name, unit_price, product_sku are all copied from the product —
 * they never change, even if the product is edited or deleted later.
 */
class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id',
        'product_name', 'product_sku', 'product_image',
        'unit_price', 'subtotal', 'quantity', 'options',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'subtotal'   => 'decimal:2',
            'quantity'   => 'integer',
            'options'    => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * LESSON: nullable() on the product relationship.
     * The product CAN be deleted (soft-deleted), but the order item MUST survive.
     * We check $item->product first before accessing its properties.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
        // withTrashed() = include soft-deleted products in the relationship
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rs. ' . number_format((float) $this->subtotal, 2);
    }
}