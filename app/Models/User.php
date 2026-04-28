<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * LESSON: The User model extends "Authenticatable" — a special Laravel base
 * class that gives it login/session/password abilities.
 * All other models extend just "Model".
 *
 * @property int    $id
 * @property string $name
 * @property string $email
 * @property string $role        'customer' | 'admin'
 * @property string $phone
 * @property string $avatar
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // =========================================================
    // FILLABLE — mass assignment protection
    // =========================================================
    /**
     * LESSON: $fillable is a security whitelist.
     * Only columns listed here can be set via User::create([...]) or $user->fill([...]).
     * This prevents hackers from injecting "role=admin" into a registration form.
     * Columns NOT listed (like 'role') must be set explicitly: $user->role = 'admin';
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
    ];

    // =========================================================
    // HIDDEN — never included in JSON/array output
    // =========================================================
    /**
     * LESSON: $hidden ensures these fields NEVER leak into API responses
     * or when you do $user->toArray() / $user->toJson().
     * Always hide passwords and tokens!
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // =========================================================
    // CASTS — automatic type conversion
    // =========================================================
    /**
     * LESSON: $casts tells Laravel to automatically convert database
     * values to PHP types. The database stores everything as text,
     * but these rules convert them transparently.
     *
     * 'email_verified_at' => 'datetime'  means when you read $user->email_verified_at
     * you get a Carbon (PHP date) object, not a raw string.
     * You can then do: $user->email_verified_at->format('d M Y')
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',   // auto-hash on assignment!
        ];
    }

    // =========================================================
    // RELATIONSHIPS
    // =========================================================

    /**
     * LESSON: hasMany = "this user has MANY orders"
     * SQL equivalent: SELECT * FROM orders WHERE user_id = {this user's id}
     *
     * Usage:
     *   $user->orders               collection of all orders
     *   $user->orders()->count()    number of orders
     *   $user->orders()->latest()   most recent first
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * hasOne = "this user has ONE cart" (at most)
     */
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    // =========================================================
    // ACCESSORS — computed properties
    // =========================================================

    /**
     * LESSON: An "accessor" is a computed property — it looks like
     * a database column but is calculated on the fly.
     *
     * Usage: $user->avatar_url   returns full URL or placeholder
     * The method name must be camelCase + Attribute suffix.
     */
    protected function avatarUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => $this->avatar
                ? asset('storage/' . $this->avatar)
                : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=e8ff47&color=000'
        );
    }

    // =========================================================
    // HELPER METHODS
    // =========================================================

    /**
     * LESSON: Clean helper methods on the model itself.
     * Instead of sprinkling $user->role === 'admin' everywhere in your code,
     * you write $user->isAdmin() — readable and easy to change later.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Check if this user has a specific product in their wishlist
     */
    public function hasWishlisted(int $productId): bool
    {
        return $this->wishlists()->where('product_id', $productId)->exists();
    }

    /**
     * Get the user's default shipping address
     */
    public function defaultAddress(): ?Address
    {
        return $this->addresses()->where('is_default', true)->first();
    }
}