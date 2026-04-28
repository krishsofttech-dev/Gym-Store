<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * LESSON: The Product model is the most important model in the store.
 * Notice:
 *  - SoftDeletes trait  $product->delete() sets deleted_at, doesn't remove row
 *  - JSON cast on 'images'  stored as JSON string, accessed as PHP array
 *  - Multiple scopes for filtering the product catalog
 *  - Price formatting accessors
 *
 * @property int         $id
 * @property string      $name
 * @property string      $slug
 * @property float       $price
 * @property float|null  $compare_price
 * @property int         $stock_quantity
 * @property bool        $in_stock
 * @property bool        $is_featured
 * @property bool        $is_active
 * @property array|null  $images         (auto-decoded from JSON)
 * @property float       $average_rating
 */
class Product extends Model
{
    use SoftDeletes; // LESSON: Adds deleted_at column support

    protected $fillable = [
        'name', 'slug', 'description', 'short_description',
        'price', 'compare_price', 'cost_price',
        'sku', 'stock_quantity', 'track_quantity', 'in_stock',
        'category_id', 'brand',
        'thumbnail', 'images',
        'weight', 'dimensions',
        'meta_title', 'meta_description',
        'is_active', 'is_featured', 'is_new',
        'average_rating', 'reviews_count', 'sales_count',
    ];

    protected function casts(): array
    {
        return [
            // LESSON: 'array' cast automatically JSON-encodes on save
            // and JSON-decodes on read. So $product->images is always a PHP array.
            'images'         => 'array',

            'price'          => 'decimal:2',
            'compare_price'  => 'decimal:2',
            'cost_price'     => 'decimal:2',
            'average_rating' => 'decimal:2',

            'is_active'      => 'boolean',
            'is_featured'    => 'boolean',
            'is_new'         => 'boolean',
            'in_stock'       => 'boolean',
            'track_quantity' => 'boolean',
        ];
    }

    // =========================================================
    // RELATIONSHIPS
    // =========================================================

    /**
     * LESSON: belongsTo = "this model owns the foreign key"
     * products.category_id  references categories.id
     *
     * Usage:
     *   $product->category         the Category object
     *   $product->category->name   "Free Weights"
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    // =========================================================
    // QUERY SCOPES — reusable filters
    // =========================================================

    /**
     * LESSON: Scopes chain perfectly with each other:
     *   Product::active()->featured()->ordered()->get()
     *   Product::active()->inCategory('dumbbells')->paginate(20)
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('in_stock', true);
    }

    public function scopeNewArrivals($query)
    {
        return $query->where('is_new', true);
    }

    public function scopeInCategory($query, string|int $category)
    {
        if (is_string($category)) {
            // Search by slug: Product::inCategory('dumbbells')
            return $query->whereHas('category', fn($q) => $q->where('slug', $category));
        }
        return $query->where('category_id', $category);
    }

    public function scopePriceBetween($query, float $min, float $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    public function scopeSearch($query, string $term)
    {
        /**
         * LESSON: "whereAny" searches across multiple columns.
         * SQLite supports LIKE for basic substring matching.
         * For production, you'd use Laravel Scout + Meilisearch/Algolia.
         */
        return $query->whereAny(
            ['name', 'description', 'brand', 'sku'],
            'LIKE',
            "%{$term}%"
        );
    }

    public function scopeOrdered($query, string $by = 'newest')
    {
        return match($by) {
            'price_asc'   => $query->orderBy('price', 'asc'),
            'price_desc'  => $query->orderBy('price', 'desc'),
            'popular'     => $query->orderBy('sales_count', 'desc'),
            'top_rated'   => $query->orderBy('average_rating', 'desc'),
            'name_asc'    => $query->orderBy('name', 'asc'),
            default       => $query->orderBy('created_at', 'desc'), // newest
        };
    }

    // =========================================================
    // ACCESSORS — computed/formatted properties
    // =========================================================

    /**
     * LESSON: Accessors use the new Laravel 9+ syntax with Attribute::make().
     * They define a "get" function (and optionally "set").
     * The property name comes from the method name in snake_case:
     *   formattedPrice()  $product->formatted_price
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => 'Rs. ' . number_format($this->price, 2)
        );
    }

    protected function formattedComparePrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->compare_price
                ? 'Rs. ' . number_format($this->compare_price, 2)
                : null
        );
    }

    /**
     * Calculate discount percentage
     * Usage: $product->discount_percentage  "18%" or null
     */
    protected function discountPercentage(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->compare_price || $this->compare_price <= $this->price) {
                    return null;
                }
                $pct = (($this->compare_price - $this->price) / $this->compare_price) * 100;
                return round($pct) . '%';
            }
        );
    }

    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->thumbnail
                ? asset('storage/' . $this->thumbnail)
                : asset('images/product-placeholder.jpg')
        );
    }

    /**
     * Get all image URLs as an array
     * Usage: $product->image_urls  ['http://...img1.jpg', 'http://...img2.jpg']
     */
    protected function imageUrls(): Attribute
    {
        return Attribute::make(
            get: function () {
                $images = $this->images ?? [];
                return array_map(
                    fn($img) => asset('storage/' . $img),
                    $images
                );
            }
        );
    }

    // =========================================================
    // HELPER METHODS
    // =========================================================

    public function isOnSale(): bool
    {
        return $this->compare_price && $this->compare_price > $this->price;
    }

    public function isOutOfStock(): bool
    {
        return $this->track_quantity && $this->stock_quantity <= 0;
    }

    public function hasEnoughStock(int $quantity): bool
    {
        if (! $this->track_quantity) return true;
        return $this->stock_quantity >= $quantity;
    }

    /**
     * Reduce stock after a purchase
     * LESSON: We wrap DB operations in a method on the model itself.
     * This keeps the "how stock is reduced" logic in ONE place.
     * Controllers just call: $product->reduceStock(2)
     */
    public function reduceStock(int $quantity): void
    {
        if ($this->track_quantity) {
            $this->decrement('stock_quantity', $quantity);
            if ($this->stock_quantity <= 0) {
                $this->update(['in_stock' => false]);
            }
        }
    }

    /**
     * Update the cached rating stats after a new review
     */
    public function recalculateRating(): void
    {
        $stats = $this->reviews()
            ->where('status', 'approved')
            ->selectRaw('AVG(rating) as avg, COUNT(*) as cnt')
            ->first();

        $this->update([
            'average_rating' => round($stats->avg ?? 0, 2),
            'reviews_count'  => $stats->cnt ?? 0,
        ]);
    }
}