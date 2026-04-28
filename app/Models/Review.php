<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * LESSON: Review demonstrates the "approval workflow" pattern.
 * Reviews are not auto-published — they sit in 'pending' until an admin approves.
 * Scopes make it easy to query only approved reviews for the frontend.
 */
class Review extends Model
{
    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id', 'product_id',
        'rating', 'title', 'body', 'images',
        'status', 'verified_purchase', 'helpful_count',
    ];

    protected function casts(): array
    {
        return [
            'rating'             => 'integer',
            'images'             => 'array',
            'verified_purchase'  => 'boolean',
            'helpful_count'      => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Only show approved reviews on the frontend
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function approve(): void
    {
        $this->update(['status' => self::STATUS_APPROVED]);
        $this->product->recalculateRating();
    }

    public function reject(): void
    {
        $this->update(['status' => self::STATUS_REJECTED]);
        $this->product->recalculateRating();
    }

    /**
     * Render rating as filled/empty stars
     * $review->star_display  '★★★★☆'
     */
    public function getStarDisplayAttribute(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }
}