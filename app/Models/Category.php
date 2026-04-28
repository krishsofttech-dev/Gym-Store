<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * LESSON: This model demonstrates a SELF-REFERENCING relationship.
 * A category can have a parent category (also a Category).
 * e.g. "Cardio" is parent of "Treadmills" which is parent of "Home Treadmills"
 *
 * @property int         $id
 * @property string      $name
 * @property string      $slug
 * @property string|null $description
 * @property string|null $image
 * @property int         $sort_order
 * @property int|null    $parent_id
 * @property bool        $is_active
 */
class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'sort_order',
        'parent_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // =========================================================
    // RELATIONSHIPS
    // =========================================================

    /**
     * A category has MANY products
     * LESSON: This is the "one" side of a one-to-many relationship.
     * $category->products returns all products in this category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Self-referencing: a category BELONGS TO a parent category
     * LESSON: belongsTo = "I own the foreign key" (parent_id is on THIS table)
     * $category->parent  the parent Category object (or null)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Self-referencing: a category HAS MANY child categories
     * $category->children  collection of sub-categories
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // =========================================================
    // QUERY SCOPES
    // =========================================================

    /**
     * LESSON: "Scopes" are reusable query conditions.
     * Instead of: Category::where('is_active', true)->get()
     * You write:   Category::active()->get()
     *
     * Scope methods start with "scope" then CamelCase.
     * Called without "scope": Category::active()
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTopLevel($query)
    {
        // Top-level = no parent
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // =========================================================
    // ACCESSORS
    // =========================================================

    protected function imageUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => $this->image
                ? asset('storage/' . $this->image)
                : asset('images/category-placeholder.jpg')
        );
    }

    // =========================================================
    // HELPERS
    // =========================================================

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Count active products in this category (including subcategories)
     */
    public function activeProductsCount(): int
    {
        return $this->products()->where('is_active', true)->count();
    }
}