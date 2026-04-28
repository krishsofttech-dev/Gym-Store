<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// ============================================================
// Address
// ============================================================
class Address extends Model
{
    protected $fillable = [
        'user_id', 'label', 'name', 'phone',
        'address_line1', 'address_line2',
        'city', 'state', 'postal_code', 'country',
        'is_default',
    ];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * When setting a new default address, clear the old one first.
     * LESSON: This is a model observer pattern (inline version).
     * Keeps the "only one default" business rule inside the model.
     */
    public function setAsDefault(): void
    {
        // Remove default from all other addresses of this user
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    /**
     * Full formatted address for display
     * $address->full_address  "42 Galle Rd, Colombo 03, Western, 00300, LK"
     */
    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]));
    }
}
