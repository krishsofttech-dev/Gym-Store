<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_FIXED      = 'fixed';

    protected $fillable = [
        'code', 'type', 'value',
        'minimum_order', 'maximum_discount',
        'usage_limit', 'used_count',
        'expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value'            => 'decimal:2',
            'minimum_order'    => 'decimal:2',
            'maximum_discount' => 'decimal:2',
            'is_active'        => 'boolean',
            'expires_at'       => 'datetime',
        ];
    }

    public function isValid(): bool
    {
        if (! $this->is_active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        return true;
    }

    public function isValidForAmount(float $orderAmount): bool
    {
        return $this->isValid() && $orderAmount >= $this->minimum_order;
    }

    public function calculateDiscount(float $cartTotal): float
    {
        if (! $this->isValidForAmount($cartTotal)) return 0.0;

        if ($this->type === self::TYPE_PERCENTAGE) {
            $discount = $cartTotal * ($this->value / 100);
            if ($this->maximum_discount) {
                $discount = min($discount, $this->maximum_discount);
            }
            return round($discount, 2);
        }

        return min((float)$this->value, $cartTotal);
    }

    public function markUsed(): void
    {
        $this->increment('used_count');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where(fn($q) => $q->whereNull('expires_at')
                                         ->orWhere('expires_at', '>', now()));
    }

    public static function findByCode(string $code): ?self
    {
        return self::where('code', strtoupper($code))->first();
    }
}