<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * LESSON: The Order model demonstrates STATUS MACHINE pattern.
 * An order flows through statuses: pending  confirmed  processing  shipped  delivered
 * We define all valid statuses as constants — no magic strings scattered in code.
 *
 * @property string $order_number
 * @property string $status
 * @property string $payment_status
 * @property float  $total
 */
class Order extends Model
{
    // =========================================================
    // STATUS CONSTANTS
    // =========================================================
    /**
     * LESSON: Constants prevent typos and enable IDE autocomplete.
     * Instead of: if ($order->status === 'shiped')   ← typo!
     * You write:  if ($order->status === Order::STATUS_SHIPPED)
     */
    const STATUS_PENDING    = 'pending';
    const STATUS_CONFIRMED  = 'confirmed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED    = 'shipped';
    const STATUS_DELIVERED  = 'delivered';
    const STATUS_CANCELLED  = 'cancelled';
    const STATUS_REFUNDED   = 'refunded';

    const PAYMENT_UNPAID   = 'unpaid';
    const PAYMENT_PAID     = 'paid';
    const PAYMENT_REFUNDED = 'refunded';

    // All valid status transitions (what can follow what)
    const VALID_TRANSITIONS = [
        self::STATUS_PENDING    => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
        self::STATUS_CONFIRMED  => [self::STATUS_PROCESSING, self::STATUS_CANCELLED],
        self::STATUS_PROCESSING => [self::STATUS_SHIPPED, self::STATUS_CANCELLED],
        self::STATUS_SHIPPED    => [self::STATUS_DELIVERED],
        self::STATUS_DELIVERED  => [self::STATUS_REFUNDED],
        self::STATUS_CANCELLED  => [],
        self::STATUS_REFUNDED   => [],
    ];

    protected $fillable = [
        'order_number', 'user_id',
        'subtotal', 'discount_amount', 'shipping_amount', 'tax_amount', 'total',
        'status', 'payment_status', 'payment_method',
        'stripe_payment_intent_id',
        'shipping_name', 'shipping_email', 'shipping_phone',
        'shipping_address_line1', 'shipping_address_line2',
        'shipping_city', 'shipping_state', 'shipping_postal_code', 'shipping_country',
        'coupon_code', 'customer_notes', 'admin_notes',
        'tracking_number', 'shipping_carrier',
        'shipped_at', 'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'        => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'tax_amount'      => 'decimal:2',
            'total'           => 'decimal:2',
            'shipped_at'      => 'datetime',
            'delivered_at'    => 'datetime',
        ];
    }

    // =========================================================
    // BOOT — auto-generate order number
    // =========================================================
    /**
     * LESSON: boot() runs when the model class is loaded.
     * "creating" hook fires BEFORE a new record is saved.
     * We use it to auto-generate a human-readable order number.
     * Result: "GYM-2024-00142"
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = 'GYM-' . date('Y') . '-' . str_pad(
                    static::whereYear('created_at', date('Y'))->count() + 1,
                    5, '0', STR_PAD_LEFT
                );
            }
        });
    }

    // =========================================================
    // RELATIONSHIPS
    // =========================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // =========================================================
    // QUERY SCOPES
    // =========================================================

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_PAID);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // =========================================================
    // ACCESSORS
    // =========================================================

    protected function formattedTotal(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => 'Rs. ' . number_format($this->total, 2)
        );
    }

    /**
     * Human-readable status with colour for UI badges
     * $order->status_label  ['label' => 'Shipped', 'color' => 'blue']
     */
    protected function statusLabel(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => match($this->status) {
                self::STATUS_PENDING    => ['label' => 'Pending',    'color' => 'yellow'],
                self::STATUS_CONFIRMED  => ['label' => 'Confirmed',  'color' => 'blue'],
                self::STATUS_PROCESSING => ['label' => 'Processing', 'color' => 'purple'],
                self::STATUS_SHIPPED    => ['label' => 'Shipped',    'color' => 'indigo'],
                self::STATUS_DELIVERED  => ['label' => 'Delivered',  'color' => 'green'],
                self::STATUS_CANCELLED  => ['label' => 'Cancelled',  'color' => 'red'],
                self::STATUS_REFUNDED   => ['label' => 'Refunded',   'color' => 'gray'],
                default                 => ['label' => $this->status, 'color' => 'gray'],
            }
        );
    }

    protected function shippingAddress(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => implode(', ', array_filter([
                $this->shipping_address_line1,
                $this->shipping_address_line2,
                $this->shipping_city,
                $this->shipping_state,
                $this->shipping_postal_code,
                $this->shipping_country,
            ]))
        );
    }

    // =========================================================
    // STATUS MACHINE HELPERS
    // =========================================================

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::VALID_TRANSITIONS[$this->status] ?? []);
    }

    /**
     * LESSON: This is the "State Machine" pattern.
     * Instead of $order->status = 'shipped' (dangerous — no validation),
     * you call $order->transitionTo(Order::STATUS_SHIPPED)
     * which checks if the transition is valid first.
     */
    public function transitionTo(string $newStatus): bool
    {
        if (! $this->canTransitionTo($newStatus)) {
            return false;
        }

        $updates = ['status' => $newStatus];

        if ($newStatus === self::STATUS_SHIPPED) {
            $updates['shipped_at'] = now();
        }
        if ($newStatus === self::STATUS_DELIVERED) {
            $updates['delivered_at'] = now();
        }

        $this->update($updates);
        return true;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
        ]);
    }
}